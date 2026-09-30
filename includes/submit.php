<?php
/**
 * Ricezione della richiesta: validazione, allegati, punteggio, salvataggio.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_ajax_pdz_conf_track', 'pdz_conf_track' );
add_action( 'wp_ajax_nopriv_pdz_conf_track', 'pdz_conf_track' );

/**
 * Registra l'avanzamento di chi sta compilando.
 * Riceve solo lo step raggiunto: nessuna risposta, nessun dato personale.
 */
function pdz_conf_track() {
	if ( ! pdz_conf_get( 'track_enabled' ) ) {
		wp_send_json_success();
	}
	if ( ! check_ajax_referer( 'pdz_conf', 'nonce', false ) ) {
		wp_send_json_error( array(), 403 );
	}

	$chiave = isset( $_POST['chiave'] ) ? sanitize_key( wp_unslash( $_POST['chiave'] ) ) : '';
	if ( ! $chiave ) {
		wp_send_json_error( array(), 400 );
	}

	pdz_conf_track_session(
		$chiave,
		array(
			'step_id'  => isset( $_POST['step'] ) ? sanitize_key( wp_unslash( $_POST['step'] ) ) : '',
			'indice'   => isset( $_POST['indice'] ) ? (int) $_POST['indice'] : 0,
			'totale'   => isset( $_POST['totale'] ) ? (int) $_POST['totale'] : 0,
			'percorso' => isset( $_POST['percorso'] ) ? sanitize_text_field( wp_unslash( $_POST['percorso'] ) ) : '',
			'secondi'  => isset( $_POST['secondi'] ) ? (int) $_POST['secondi'] : 0,
		)
	);

	wp_send_json_success();
}

add_action( 'wp_ajax_pdz_conf_submit', 'pdz_conf_submit' );
add_action( 'wp_ajax_nopriv_pdz_conf_submit', 'pdz_conf_submit' );

function pdz_conf_submit() {
	$s = pdz_conf_settings();

	// Se gli allegati superano il limite del server, PHP scarta la richiesta
	// prima di arrivare qui: senza questo controllo l'utente vedrebbe un errore
	// di sessione scaduta invece del motivo vero.
	$length = isset( $_SERVER['CONTENT_LENGTH'] ) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
	if ( empty( $_POST ) && $length > 0 ) {
		wp_send_json_error(
			array(
				'message' => sprintf(
					'I file allegati superano il limite del server (%s complessivi). Rimuovine qualcuno o inviali più tardi via email.',
					size_format( pdz_conf_max_total_upload() )
				),
			),
			413
		);
	}

	if ( ! check_ajax_referer( 'pdz_conf', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => 'Sessione scaduta. Ricarica la pagina e riprova.' ), 403 );
	}

	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';

	// Chi gestisce il configuratore sta collaudando: niente antispam.
	$antispam = ! current_user_can( 'pdz_conf_access' );

	// Honeypot: sempre attivo, non dà falsi positivi.
	if ( ! empty( $_POST['pdz_website'] ) ) {
		wp_send_json_error( array( 'message' => 'Invio non riuscito.' ), 400 );
	}

	if ( $antispam ) {
		// Tempo minimo di compilazione.
		$elapsed = isset( $_POST['elapsed'] ) ? (int) $_POST['elapsed'] : 0;
		if ( $elapsed < (int) $s['min_seconds'] ) {
			wp_send_json_error( array( 'message' => 'Invio non riuscito. Riprova.' ), 400 );
		}

		// Limite invii per IP.
		$key  = 'pdz_conf_rl_' . md5( $ip );
		$hits = (int) get_transient( $key );
		if ( $hits >= (int) $s['rate_limit'] ) {
			wp_send_json_error( array( 'message' => 'Troppe richieste dallo stesso indirizzo. Riprova più tardi.' ), 429 );
		}
		set_transient( $key, $hits + 1, HOUR_IN_SECONDS );
	}

	$raw     = isset( $_POST['answers'] ) ? wp_unslash( $_POST['answers'] ) : '';
	$answers = json_decode( $raw, true );
	if ( ! is_array( $answers ) ) {
		wp_send_json_error( array( 'message' => 'Dati non validi.' ), 400 );
	}

	$clean  = array();
	$errors = array();

	foreach ( pdz_conf_visible_steps( $answers ) as $step ) {
		foreach ( $step['fields'] as $field ) {
			if ( ! pdz_conf_field_visible( $field, $answers ) ) {
				continue;
			}

			$name  = $field['name'];
			$value = isset( $answers[ $name ] ) ? $answers[ $name ] : null;

			if ( is_array( $value ) && 'checkbox' !== $field['type'] ) {
				$value = '';
			}

			switch ( $field['type'] ) {
				case 'radio':
				case 'select':
					$value = is_string( $value ) ? sanitize_text_field( $value ) : '';
					if ( '' !== $value && ! isset( $field['options'][ $value ] ) ) {
						$value = '';
					}
					break;

				case 'checkbox':
					$value = is_array( $value ) ? $value : array();
					$value = array_values(
						array_filter(
							array_map( 'sanitize_text_field', $value ),
							function ( $v ) use ( $field ) {
								return isset( $field['options'][ $v ] );
							}
						)
					);
					break;

				case 'email':
					$value = sanitize_email( (string) $value );
					if ( $value && ! is_email( $value ) ) {
						$value = '';
					}
					break;

				case 'textarea':
					$value = sanitize_textarea_field( (string) $value );
					break;

				case 'consent':
					$value = ! empty( $value ) ? 'si' : '';
					break;

				case 'file':
					continue 2;

				default:
					$value = sanitize_text_field( (string) $value );
			}

			if ( ! empty( $field['required'] ) && ( '' === $value || array() === $value ) ) {
				$errors[] = $name;
			}
			$clean[ $name ] = $value;
		}
	}

	if ( ! empty( $clean['cap'] ) && ! preg_match( '/^[0-9]{5}$/', $clean['cap'] ) ) {
		$errors[] = 'cap';
	}

	if ( $errors ) {
		wp_send_json_error(
			array(
				'message' => 'Alcuni campi obbligatori non sono compilati.',
				'fields'  => array_values( array_unique( $errors ) ),
			),
			422
		);
	}

	$files = pdz_conf_handle_uploads();
	$path  = pdz_conf_resolve_path( $clean );
	$score = pdz_conf_score( $clean, $files, $path );
	$class = pdz_conf_classify( $score );

	$summary = pdz_conf_build_summary( $clean, $files, $path, $score );

	$lead_id = pdz_conf_insert_lead(
		array(
			'nome'      => isset( $clean['nome'] ) ? $clean['nome'] : '',
			'email'     => isset( $clean['email'] ) ? $clean['email'] : '',
			'telefono'  => isset( $clean['telefono'] ) ? $clean['telefono'] : '',
			'comune'    => isset( $clean['comune'] ) ? $clean['comune'] : '',
			'provincia' => isset( $clean['provincia'] ) ? $clean['provincia'] : '',
			'regione'   => pdz_conf_regione( isset( $clean['provincia'] ) ? $clean['provincia'] : '' ),
			'cap'       => isset( $clean['cap'] ) ? $clean['cap'] : '',
			'percorso'  => $path,
			'punteggio' => $score,
			'classe'    => $class,
			'risposte'  => $clean,
			'allegati'  => $files,
			'riepilogo' => $summary,
			'ip'        => $ip,
		)
	);

	$mail_admin = pdz_conf_send_admin_email( $clean, $summary, $score, $path, $files );
	$mail_user  = pdz_conf_send_user_email( $clean, $path, $files );

	pdz_conf_record_mail_result( $lead_id, $mail_admin, $mail_user );

	if ( pdz_conf_get( 'track_enabled' ) && ! empty( $_POST['chiave'] ) ) {
		pdz_conf_complete_session( sanitize_key( wp_unslash( $_POST['chiave'] ) ), $lead_id );
	}

	do_action( 'pdz_conf_after_submit', $clean, $files, $score, $path, $lead_id );

	wp_send_json_success(
		array(
			'redirect' => pdz_conf_thankyou_url( $clean, $path ),
			'message'  => pdz_conf_thankyou_message( $clean, $path ),
		)
	);
}

/**
 * URL della thank you page in base alla provenienza. Stringa vuota = messaggio inline.
 */
function pdz_conf_thankyou_url( $answers, $path ) {
	if ( 'F' === $path ) {
		$url = pdz_conf_get( 'thankyou_elettrica', '' );
		if ( $url ) {
			return esc_url_raw( $url );
		}
	}

	$key = pdz_conf_is_lombardia( $answers ) ? 'thankyou_lombardia' : 'thankyou_fuori';
	$url = pdz_conf_get( $key, '' );

	return $url ? esc_url_raw( $url ) : '';
}

/**
 * Messaggio mostrato quando non e' impostata una thank you page.
 */
function pdz_conf_thankyou_message( $answers, $path ) {
	if ( 'F' === $path ) {
		$msg = pdz_conf_get( 'msg_elettrica' );
	} else {
		$key = pdz_conf_is_lombardia( $answers ) ? 'msg_standard' : 'msg_fuori';
		$msg = pdz_conf_get( $key );
	}

	$msg = wp_kses_post( $msg );

	// Pulsante di uscita: senza, l'utente resta bloccato sul messaggio.
	$label = pdz_conf_get( 'msg_button_label', '' );
	$url   = pdz_conf_get( 'msg_button_url', '' );

	if ( $label && $url ) {
		$msg .= sprintf(
			'<p class="pdz-done__cta"><a class="pdz-btn pdz-btn--primary" href="%s">%s</a></p>',
			esc_url( $url ),
			esc_html( $label )
		);
	}

	return $msg;
}

/**
 * Peso complessivo ammesso dal server, con un margine per il resto della richiesta.
 */
function pdz_conf_max_total_upload() {
	$limit = (int) wp_max_upload_size();
	return $limit > 0 ? (int) ( $limit * 0.9 ) : 8 * 1024 * 1024;
}

/**
 * Peso massimo per singolo file: quello impostato, ma mai oltre il limite del server.
 */
function pdz_conf_max_file_size() {
	$wanted = (int) pdz_conf_get( 'max_file_size', 8 ) * 1024 * 1024;
	$server = (int) wp_max_upload_size();

	return ( $server > 0 && $wanted > $server ) ? $server : $wanted;
}

/**
 * Valida e sposta gli allegati. Restituisce [ campo => [ [name, url, file] ] ].
 */
function pdz_conf_handle_uploads() {
	$s        = pdz_conf_settings();
	$out      = array();
	$count    = 0;
	$max      = (int) $s['max_files'];
	$max_size = pdz_conf_max_file_size();
	$total    = 0;
	$max_tot  = pdz_conf_max_total_upload();
	$allowed  = pdz_conf_allowed_ext();

	if ( empty( $_FILES ) ) {
		return $out;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	add_filter( 'upload_dir', 'pdz_conf_upload_dir' );

	foreach ( $_FILES as $field => $data ) {
		if ( 0 !== strpos( $field, 'file_' ) || ! isset( $data['name'] ) ) {
			continue;
		}

		foreach ( array_keys( (array) $data['name'] ) as $i ) {
			if ( $count >= $max ) {
				break 2;
			}

			$file = array(
				'name'     => is_array( $data['name'] ) ? $data['name'][ $i ] : $data['name'],
				'type'     => is_array( $data['type'] ) ? $data['type'][ $i ] : $data['type'],
				'tmp_name' => is_array( $data['tmp_name'] ) ? $data['tmp_name'][ $i ] : $data['tmp_name'],
				'error'    => is_array( $data['error'] ) ? $data['error'][ $i ] : $data['error'],
				'size'     => is_array( $data['size'] ) ? $data['size'][ $i ] : $data['size'],
			);

			if ( UPLOAD_ERR_OK !== (int) $file['error'] || $file['size'] > $max_size ) {
				continue;
			}
			if ( $total + $file['size'] > $max_tot ) {
				break 2;
			}
			$total += $file['size'];
			$ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
			if ( ! in_array( $ext, $allowed, true ) ) {
				continue;
			}

			// Nome leggibile per l'email, nome su disco non indovinabile.
			$original     = sanitize_file_name( $file['name'] );
			$file['name'] = wp_generate_password( 10, false, false ) . '-' . $original;

			$moved = wp_handle_upload( $file, array( 'test_form' => false ) );
			if ( ! empty( $moved['file'] ) ) {
				$out[ $field ][] = array(
					'name' => $original,
					'url'  => pdz_conf_file_url( $moved['file'] ),
					'file' => $moved['file'],
				);
				$count++;
			}
		}
	}

	remove_filter( 'upload_dir', 'pdz_conf_upload_dir' );

	return $out;
}

function pdz_conf_upload_dir( $dirs ) {
	$sub            = '/configuratore/' . gmdate( 'Y-m' );
	$dirs['subdir'] = $sub;
	$dirs['path']   = $dirs['basedir'] . $sub;
	$dirs['url']    = $dirs['baseurl'] . $sub;

	pdz_conf_protect_uploads( $dirs['basedir'] . '/configuratore' );

	return $dirs;
}

/**
 * Blocca l'accesso diretto alla cartella degli allegati.
 * Su Apache e LiteSpeed basta l'.htaccess; su Nginx serve una regola nel vhost
 * (indicata nel file LEGGIMI): in entrambi i casi i file restano raggiungibili
 * solo attraverso il link firmato generato dal plugin.
 */
function pdz_conf_protect_uploads( $dir ) {
	if ( ! file_exists( $dir ) ) {
		wp_mkdir_p( $dir );
	}

	$htaccess = trailingslashit( $dir ) . '.htaccess';
	if ( ! file_exists( $htaccess ) ) {
		file_put_contents( $htaccess, "Options -Indexes\n<FilesMatch \".*\">\nRequire all denied\n</FilesMatch>\n" );
	}

	$index = trailingslashit( $dir ) . 'index.html';
	if ( ! file_exists( $index ) ) {
		file_put_contents( $index, '' );
	}
}

/**
 * Link firmato a un allegato. Funziona senza login, si può revocare
 * cambiando i sali di WordPress, e non espone il percorso reale del file.
 */
function pdz_conf_file_url( $path ) {
	$uploads = wp_upload_dir();
	$rel     = ltrim( str_replace( $uploads['basedir'], '', $path ), '/' );

	return add_query_arg(
		array(
			'pdz_file' => $rel,
			't'        => pdz_conf_file_token( $rel ),
		),
		home_url( '/' )
	);
}

function pdz_conf_file_token( $rel ) {
	return substr( hash_hmac( 'sha256', $rel, wp_salt( 'pdz_conf_file' ) ), 0, 24 );
}

/**
 * Consegna l'allegato solo se la firma è valida e il file sta nella cartella giusta.
 */
add_action( 'init', 'pdz_conf_serve_file' );

function pdz_conf_serve_file() {
	if ( empty( $_GET['pdz_file'] ) || empty( $_GET['t'] ) ) {
		return;
	}

	// $_GET arriva già decodificato: nessuna decodifica aggiuntiva.
	$rel   = ltrim( str_replace( array( '..', "\0" ), '', (string) wp_unslash( $_GET['pdz_file'] ) ), '/' );
	$token = sanitize_text_field( wp_unslash( $_GET['t'] ) );

	if ( ! hash_equals( pdz_conf_file_token( $rel ), $token ) ) {
		wp_die( 'Link non valido o scaduto.', 'Accesso negato', array( 'response' => 403 ) );
	}

	$uploads = wp_upload_dir();
	$base    = realpath( $uploads['basedir'] . '/configuratore' );
	$path    = realpath( $uploads['basedir'] . '/' . $rel );

	if ( ! $base || ! $path || 0 !== strpos( $path, $base ) || ! is_file( $path ) ) {
		wp_die( 'File non disponibile.', 'Non trovato', array( 'response' => 404 ) );
	}

	$type = wp_check_filetype( $path );

	nocache_headers();
	header( 'Content-Type: ' . ( $type['type'] ? $type['type'] : 'application/octet-stream' ) );
	header( 'Content-Length: ' . filesize( $path ) );
	header( 'Content-Disposition: inline; filename="' . basename( $path ) . '"' );
	header( 'X-Content-Type-Options: nosniff' );
	readfile( $path );
	exit;
}

/* -------------------------------------------------------------------------
 * Punteggio
 * ---------------------------------------------------------------------- */

/**
 * Punteggio 0-100: punti delle singole risposte (impostati nell'editor domande)
 * piu' i bonus di provenienza e qualita' delle informazioni (impostazioni).
 */
function pdz_conf_score( $answers, $files, $path ) {
	$s     = pdz_conf_settings();
	$score = 0;

	// Punti definiti sulle risposte.
	foreach ( pdz_conf_visible_steps( $answers ) as $step ) {
		foreach ( $step['fields'] as $field ) {
			if ( empty( $field['scores'] ) || ! isset( $answers[ $field['name'] ] ) ) {
				continue;
			}
			$value = $answers[ $field['name'] ];
			foreach ( (array) $value as $v ) {
				if ( isset( $field['scores'][ $v ] ) ) {
					$score += (int) $field['scores'][ $v ];
				}
			}
		}
	}

	// Provenienza.
	$regione = pdz_conf_regione( isset( $answers['provincia'] ) ? $answers['provincia'] : '' );
	$nord    = array( 'Piemonte', 'Liguria', 'Veneto', 'Emilia-Romagna', 'Friuli-Venezia Giulia', 'Trentino-Alto Adige', "Valle d'Aosta" );

	if ( 'Lombardia' === $regione ) {
		$score += (int) $s['score_lombardia'];
	} elseif ( in_array( $regione, $nord, true ) ) {
		$score += (int) $s['score_nord'];
	}

	// Qualita' delle informazioni.
	if ( ! empty( $files ) ) {
		$score += (int) $s['score_allegati'];
	}
	if ( ! empty( $files['file_preventivi'] ) ) {
		$score += (int) $s['score_preventivo'];
	}
	if ( pdz_conf_dati_completi( $answers ) ) {
		$score += (int) $s['score_dati_completi'];
	}

	$msg = trim(
		(string) ( isset( $answers['messaggio'] ) ? $answers['messaggio'] : '' ) . ' ' .
		(string) ( isset( $answers['descrizione_richiesta'] ) ? $answers['descrizione_richiesta'] : '' )
	);
	if ( strlen( $msg ) >= 120 ) {
		$score += (int) $s['score_messaggio'];
	}

	$score = max( 0, min( 100, $score ) );

	return (int) apply_filters( 'pdz_conf_score', $score, $answers, $files, $path );
}

/**
 * "Dati tecnici completi": meno del 30% di risposte "non so".
 */
function pdz_conf_dati_completi( $answers ) {
	$tot = 0;
	$ns  = 0;
	foreach ( $answers as $v ) {
		if ( ! is_string( $v ) || '' === $v ) {
			continue;
		}
		$tot++;
		if ( 0 === strpos( $v, 'non_so' ) || 'non_ancora' === $v ) {
			$ns++;
		}
	}
	return $tot >= 8 && ( $ns / max( 1, $tot ) ) < 0.3;
}

/**
 * Classe di priorita': alta | media | bassa. L'etichetta mostrata e' configurabile.
 */
function pdz_conf_classify( $score ) {
	if ( $score >= (int) pdz_conf_get( 'soglia_caldo', 65 ) ) {
		return 'alta';
	}
	if ( $score >= (int) pdz_conf_get( 'soglia_medio', 35 ) ) {
		return 'media';
	}
	return 'bassa';
}

/**
 * Etichetta leggibile della classe di priorita'.
 */
function pdz_conf_class_label( $key ) {
	// Compatibilita' con le richieste salvate dalla prima versione.
	$legacy = array( 'Caldo' => 'alta', 'Medio' => 'media', 'Freddo' => 'bassa' );
	if ( isset( $legacy[ $key ] ) ) {
		$key = $legacy[ $key ];
	}

	$map = array(
		'alta'  => pdz_conf_get( 'label_alta', 'Priorità alta' ),
		'media' => pdz_conf_get( 'label_media', 'Priorità media' ),
		'bassa' => pdz_conf_get( 'label_bassa', 'Priorità bassa' ),
	);

	return isset( $map[ $key ] ) ? $map[ $key ] : $key;
}

/**
 * Chiave normalizzata, usata per le classi CSS.
 */
function pdz_conf_class_key( $key ) {
	$legacy = array( 'Caldo' => 'alta', 'Medio' => 'media', 'Freddo' => 'bassa' );
	return isset( $legacy[ $key ] ) ? $legacy[ $key ] : $key;
}

/* -------------------------------------------------------------------------
 * Esito degli invii
 * ---------------------------------------------------------------------- */

/**
 * Raccoglie l'ultimo errore riportato da wp_mail().
 */
add_action( 'wp_mail_failed', function ( $error ) {
	$GLOBALS['pdz_conf_mail_error'] = is_wp_error( $error ) ? $error->get_error_message() : '';
} );

/**
 * Annota sul record se le email sono partite. La richiesta resta comunque salvata:
 * così un guasto SMTP non fa perdere il contatto.
 */
function pdz_conf_record_mail_result( $lead_id, $admin_ok, $user_ok ) {
	if ( ! $lead_id ) {
		return;
	}

	$status = $admin_ok ? 'ok' : 'errore';
	$error  = '';

	if ( ! $admin_ok ) {
		$error = isset( $GLOBALS['pdz_conf_mail_error'] ) ? $GLOBALS['pdz_conf_mail_error'] : 'Invio non riuscito, causa non riportata dal server.';
	} elseif ( false === $user_ok && ! empty( pdz_conf_get( 'user_email_enabled' ) ) ) {
		$status = 'parziale';
		$error  = 'L’email interna è partita, quella di conferma all’utente no.';
	}

	pdz_conf_update_lead( $lead_id, array( 'mail_status' => $status, 'mail_error' => $error ) );
}
