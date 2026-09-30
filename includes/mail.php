<?php
/**
 * Email: riepilogo al gestore del sito e conferma all'utente.
 * Tutto passa da wp_mail(), quindi eventuali plugin SMTP funzionano senza configurazioni qui.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tabella riepilogativa delle risposte, raggruppate per step.
 *
 * @param string $context 'admin' oppure 'user'.
 */
function pdz_conf_build_summary( $answers, $files, $path, $score = null, $context = 'admin' ) {
	$accent = sanitize_hex_color( pdz_conf_get( 'accent', '#e4171c' ) );
	$s      = pdz_conf_settings();
	$html   = '';

	// Nell'email all'utente si decide cosa includere dalle impostazioni.
	$user          = ( 'user' === $context );
	$show_answers  = ! $user || ! empty( $s['user_email_summary'] );
	$show_contacts = ! $user || ! empty( $s['user_email_contacts'] );
	$show_files    = ! $user || ! empty( $s['user_email_files'] );

	if ( 'admin' === $context ) {
		$html .= '<table style="border-collapse:collapse;width:100%;margin:0 0 24px;">'
			. '<tr>'
			. '<td style="padding:12px 16px;background:#f4f6f8;border-left:3px solid ' . esc_attr( $accent ) . ';font-size:14px;">'
			. 'Percorso: <strong>' . esc_html( pdz_conf_path_label( $path ) ) . '</strong><br>'
			. 'Punteggio: <strong>' . (int) $score . '/100</strong> — <strong>' . esc_html( pdz_conf_class_label( pdz_conf_classify( $score ) ) ) . '</strong>'
			. '</td></tr></table>';
	}

	foreach ( pdz_conf_visible_steps( $answers ) as $step ) {
		$is_contact = ( 'contatti' === pdz_conf_step_phase( $step ) );

		if ( $is_contact && ! $show_contacts ) {
			continue;
		}
		if ( ! $is_contact && ! $show_answers ) {
			continue;
		}

		$rows = '';

		foreach ( $step['fields'] as $field ) {
			if ( 'file' === $field['type'] || ! pdz_conf_field_visible( $field, $answers ) ) {
				continue;
			}
			$name = $field['name'];
			if ( ! isset( $answers[ $name ] ) || '' === $answers[ $name ] || array() === $answers[ $name ] ) {
				continue;
			}
			$rows .= '<tr>'
				. '<td style="padding:6px 16px 6px 0;color:#5c6771;font-size:14px;vertical-align:top;width:45%;">' . esc_html( $field['label'] ) . '</td>'
				. '<td style="padding:6px 0;font-size:14px;color:#14181c;"><strong>' . esc_html( pdz_conf_value_label( $field, $answers[ $name ] ) ) . '</strong></td>'
				. '</tr>';
		}

		if ( $rows ) {
			$html .= '<h3 style="margin:24px 0 8px;font-size:15px;color:#14181c;border-bottom:1px solid #dfe4e8;padding-bottom:6px;">'
				. esc_html( $step['title'] ) . '</h3>'
				. '<table style="border-collapse:collapse;width:100%;">' . $rows . '</table>';
		}
	}

	if ( $files && $show_files ) {
		$html .= '<h3 style="margin:24px 0 8px;font-size:15px;border-bottom:1px solid #dfe4e8;padding-bottom:6px;">Allegati</h3><ul style="font-size:14px;padding-left:18px;margin:0;">';
		foreach ( $files as $list ) {
			foreach ( $list as $f ) {
				$html .= 'admin' === $context
					? '<li><a href="' . esc_url( $f['url'] ) . '" style="color:' . esc_attr( $accent ) . ';">' . esc_html( $f['name'] ) . '</a></li>'
					: '<li>' . esc_html( $f['name'] ) . '</li>';
			}
		}
		$html .= '</ul>';
	}

	return $html;
}

/**
 * Scheletro HTML comune alle due email.
 */
function pdz_conf_email_template( $title, $intro, $body, $footer = '' ) {
	$accent = sanitize_hex_color( pdz_conf_get( 'accent', '#e4171c' ) );

	$html  = '<div style="background:#f4f6f8;padding:24px 0;">';
	$html .= '<div style="max-width:640px;margin:0 auto;background:#ffffff;padding:32px;font-family:Inter,Helvetica,Arial,sans-serif;color:#14181c;line-height:1.55;">';
	$html .= '<div style="border-top:3px solid ' . esc_attr( $accent ) . ';padding-top:20px;">';
	$html .= '<h2 style="margin:0 0 12px;font-size:20px;font-weight:600;">' . esc_html( $title ) . '</h2>';

	if ( $intro ) {
		foreach ( preg_split( '/\n\s*\n/', $intro ) as $par ) {
			$par = trim( $par );
			if ( $par ) {
				$html .= '<p style="margin:0 0 12px;font-size:14px;color:#5c6771;">' . esc_html( $par ) . '</p>';
			}
		}
	}

	$html .= $body;

	if ( $footer ) {
		$html .= '<p style="margin:28px 0 0;padding-top:16px;border-top:1px solid #dfe4e8;font-size:12px;color:#5c6771;">' . esc_html( $footer ) . '</p>';
	}

	$html .= '</div></div></div>';

	return $html;
}

/**
 * Intestazioni comuni. Il mittente resta vuoto se non impostato,
 * cosi' vale quello configurato nel plugin SMTP.
 */
function pdz_conf_mail_headers( $reply_to = '', $reply_name = '' ) {
	$headers = array( 'Content-Type: text/html; charset=UTF-8' );

	$from = pdz_conf_get( 'email_from', '' );
	if ( $from && is_email( $from ) ) {
		$headers[] = sprintf( 'From: %s <%s>', pdz_conf_get( 'email_from_name', get_bloginfo( 'name' ) ), $from );
	}
	if ( $reply_to && is_email( $reply_to ) ) {
		$headers[] = sprintf( 'Reply-To: %s <%s>', $reply_name, $reply_to );
	}

	return $headers;
}

/**
 * Email al gestore del sito.
 */
/**
 * Oggetto e corpo dell'email interna. Usato sia dall'invio sia dall'anteprima.
 */
function pdz_conf_admin_email_parts( $answers, $summary, $score, $path ) {
	$s = pdz_conf_settings();

	$subject = pdz_conf_placeholders( $s['email_subject'], $answers, $path, $score );
	$intro   = pdz_conf_placeholders( $s['email_intro'], $answers, $path, $score );

	return array(
		'subject' => $subject,
		'body'    => pdz_conf_email_template( $subject, $intro, $summary ),
	);
}

/**
 * Oggetto e corpo dell'email di conferma all'utente.
 */
function pdz_conf_user_email_parts( $answers, $files, $path ) {
	$s = pdz_conf_settings();

	$subject = pdz_conf_placeholders( $s['user_email_subject'], $answers, $path );
	$intro   = pdz_conf_placeholders( $s['user_email_intro'], $answers, $path );
	$footer  = pdz_conf_placeholders( $s['user_email_footer'], $answers, $path );
	$summary = pdz_conf_build_summary( $answers, $files, $path, null, 'user' );

	return array(
		'subject' => $subject,
		'body'    => pdz_conf_email_template( $subject, $intro, $summary, $footer ),
	);
}

/**
 * Richiesta di esempio, con le etichette delle domande attualmente in uso.
 * Serve solo all'anteprima nelle impostazioni.
 */
function pdz_conf_sample_answers() {
	$answers = array(
		'obiettivo'          => 'pompa_calore',
		'comune'             => 'Pavia',
		'provincia'          => 'PV',
		'cap'                => '27100',
		'tipologia_immobile' => 'villa',
		'stato_immobile'     => 'abitato',
		'nome'               => 'Mario Rossi',
		'email'              => 'mario.rossi@example.com',
		'telefono'           => '333 1234567',
		'preferenza_contatto'=> 'telefono',
		'fascia_oraria'      => 'pomeriggio',
		'messaggio'          => 'Vorrei capire se la mia casa è adatta a una pompa di calore senza rifare i termosifoni.',
		'privacy'            => 'si',
	);

	// Completa il percorso A con la prima opzione disponibile di ogni domanda.
	foreach ( pdz_conf_visible_steps( $answers ) as $step ) {
		foreach ( $step['fields'] as $field ) {
			if ( isset( $answers[ $field['name'] ] ) || empty( $field['options'] ) ) {
				continue;
			}
			$keys = array_keys( $field['options'] );
			$answers[ $field['name'] ] = ( 'checkbox' === $field['type'] ) ? array( $keys[0] ) : $keys[0];
		}
	}

	return $answers;
}

function pdz_conf_send_admin_email( $answers, $summary, $score, $path, $files ) {
	$s     = pdz_conf_settings();
	$to    = pdz_conf_recipients( $path );
	$parts = pdz_conf_admin_email_parts( $answers, $summary, $score, $path );

	$subject = $parts['subject'];
	$body    = $parts['body'];

	$attachments = array();
	if ( ! empty( $s['attach_to_email'] ) ) {
		foreach ( $files as $list ) {
			foreach ( $list as $f ) {
				if ( file_exists( $f['file'] ) && filesize( $f['file'] ) < 4 * 1024 * 1024 ) {
					$attachments[] = $f['file'];
				}
			}
		}
	}

	$headers = pdz_conf_mail_headers(
		isset( $answers['email'] ) ? $answers['email'] : '',
		isset( $answers['nome'] ) ? $answers['nome'] : ''
	);

	return wp_mail( $to, $subject, $body, $headers, $attachments );
}

/**
 * Email di conferma all'utente, con lo stesso riepilogo ma senza punteggio.
 */
function pdz_conf_send_user_email( $answers, $path, $files = array() ) {
	$s = pdz_conf_settings();

	if ( empty( $s['user_email_enabled'] ) || empty( $answers['email'] ) || ! is_email( $answers['email'] ) ) {
		return false;
	}

	$parts   = pdz_conf_user_email_parts( $answers, $files, $path );
	$subject = $parts['subject'];
	$body    = $parts['body'];
	$reply   = pdz_conf_recipients( $path );
	$headers = pdz_conf_mail_headers( is_array( $reply ) ? reset( $reply ) : '', get_bloginfo( 'name' ) );

	$attachments = array();
	if ( ! empty( $s['user_email_attach'] ) ) {
		foreach ( $files as $list ) {
			foreach ( $list as $f ) {
				if ( file_exists( $f['file'] ) && filesize( $f['file'] ) < 4 * 1024 * 1024 ) {
					$attachments[] = $f['file'];
				}
			}
		}
	}

	return wp_mail( $answers['email'], $subject, $body, $headers, $attachments );
}
