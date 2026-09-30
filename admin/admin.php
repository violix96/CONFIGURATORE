<?php
/**
 * Menu di amministrazione e pagina impostazioni.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'pdz_conf_admin_menu' );

function pdz_conf_admin_menu() {
	add_menu_page(
		'Configuratore',
		'Configuratore',
		pdz_conf_cap(),
		'pdz-conf-records',
		'pdz_conf_render_records_page',
		'dashicons-clipboard',
		26
	);

	add_submenu_page( 'pdz-conf-records', 'Richieste', 'Richieste', pdz_conf_cap(), 'pdz-conf-records', 'pdz_conf_render_records_page' );
	add_submenu_page( 'pdz-conf-records', 'Domande', 'Domande', pdz_conf_cap( 'domande' ), 'pdz-conf-questions', 'pdz_conf_render_questions_page' );
	add_submenu_page( 'pdz-conf-records', 'Andamento', 'Andamento', pdz_conf_cap(), 'pdz-conf-stats', 'pdz_conf_render_stats_page' );
	add_submenu_page( 'pdz-conf-records', 'Impostazioni', 'Impostazioni', pdz_conf_cap(), 'pdz-conf-settings', 'pdz_conf_render_settings_page' );
}

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( false === strpos( $hook, 'pdz-conf' ) ) {
		return;
	}
	wp_enqueue_style( 'pdz-conf-front', PDZ_CONF_URL . 'assets/css/frontend.css', array(), PDZ_CONF_VERSION );
	wp_enqueue_style( 'pdz-conf-admin', PDZ_CONF_URL . 'assets/css/admin.css', array( 'pdz-conf-front' ), PDZ_CONF_VERSION );
	wp_enqueue_script( 'pdz-conf-admin', PDZ_CONF_URL . 'assets/js/admin.js', array(), PDZ_CONF_VERSION, true );
} );

/* -------------------------------------------------------------------------
 * Helper di rendering dei campi
 * ---------------------------------------------------------------------- */

function pdz_conf_field_row( $label, $name, $value, $type = 'text', $desc = '', $extra = array() ) {
	$id    = 'pdz_' . $name;
	$class = 'pdz-row pdz-row--' . $type;
	?>
	<div class="<?php echo esc_attr( $class ); ?>">
		<div class="pdz-row__label">
			<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
			<?php if ( $desc ) : ?>
				<p class="pdz-desc"><?php echo wp_kses_post( $desc ); ?></p>
			<?php endif; ?>
		</div>
		<div class="pdz-row__field">
			<?php
			switch ( $type ) {
				case 'textarea':
					$rows = isset( $extra['rows'] ) ? (int) $extra['rows'] : 4;
					printf(
						'<textarea class="pdz-input" rows="%d" id="%s" name="pdz[%s]">%s</textarea>',
						$rows,
						esc_attr( $id ),
						esc_attr( $name ),
						esc_textarea( $value )
					);
					break;

				case 'checkbox':
					printf(
						'<label class="pdz-switch"><input type="checkbox" id="%s" name="pdz[%s]" value="1" %s><span class="pdz-switch__track"></span><span class="pdz-switch__text">%s</span></label>',
						esc_attr( $id ),
						esc_attr( $name ),
						checked( 1, (int) $value, false ),
						esc_html( isset( $extra['label'] ) ? $extra['label'] : 'Attivo' )
					);
					break;

				case 'number':
					printf(
						'<span class="pdz-num"><input type="number" class="pdz-input pdz-input--num" id="%s" name="pdz[%s]" value="%s" step="%s">%s</span>',
						esc_attr( $id ),
						esc_attr( $name ),
						esc_attr( $value ),
						esc_attr( isset( $extra['step'] ) ? $extra['step'] : 1 ),
						! empty( $extra['suffix'] ) ? '<em>' . esc_html( $extra['suffix'] ) . '</em>' : ''
					);
					break;

				case 'color':
					printf(
						'<span class="pdz-color"><input type="color" value="%1$s" data-pdz-colorpick aria-label="Scegli il colore"><input type="text" class="pdz-input pdz-input--color" id="%2$s" name="pdz[%3$s]" value="%1$s" placeholder="#e4171c"></span>',
						esc_attr( $value ),
						esc_attr( $id ),
						esc_attr( $name )
					);
					break;

				case 'select':
					echo '<select class="pdz-input" style="max-width:26rem;" id="' . esc_attr( $id ) . '" name="pdz[' . esc_attr( $name ) . ']">';
					foreach ( (array) $extra['options'] as $k => $v ) {
						printf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $value, $k, false ), esc_html( $v ) );
					}
					echo '</select>';
					break;

				case 'url':
					printf(
						'<input type="url" class="pdz-input" id="%s" name="pdz[%s]" value="%s" placeholder="https://">',
						esc_attr( $id ),
						esc_attr( $name ),
						esc_attr( $value )
					);
					break;

				default:
					printf(
						'<input type="text" class="pdz-input" id="%s" name="pdz[%s]" value="%s">',
						esc_attr( $id ),
						esc_attr( $name ),
						esc_attr( $value )
					);
			}
			?>
		</div>
	</div>
	<?php
}

/**
 * Apre una sezione delle impostazioni.
 */
/**
 * Manda le due email di esempio all'indirizzo indicato.
 * Restituisce 'ok', 'parziale', 'errore' oppure 'indirizzo'.
 */
function pdz_conf_send_test_email( $to ) {
	if ( ! $to || ! is_email( $to ) ) {
		return 'indirizzo';
	}

	$answers          = pdz_conf_sample_answers();
	$answers['email'] = $to;
	$path             = pdz_conf_resolve_path( $answers );
	$files            = array();
	$score            = pdz_conf_score( $answers, $files, $path );
	$summary          = pdz_conf_build_summary( $answers, $files, $path, $score, 'admin' );

	$admin = pdz_conf_admin_email_parts( $answers, $summary, $score, $path );
	$user  = pdz_conf_user_email_parts( $answers, $files, $path );

	$headers = pdz_conf_mail_headers();

	$ok_admin = wp_mail( $to, '[PROVA] ' . $admin['subject'], $admin['body'], $headers );
	$ok_user  = wp_mail( $to, '[PROVA] ' . $user['subject'], $user['body'], $headers );

	if ( $ok_admin && $ok_user ) {
		return 'ok';
	}
	if ( $ok_admin || $ok_user ) {
		return 'parziale';
	}
	return 'errore';
}

/**
 * Invio di prova: verifica che l'SMTP consegni davvero, cosa che l'anteprima non dice.
 */
function pdz_conf_test_email_row() {
	$current = wp_get_current_user();
	?>
	<div class="pdz-row">
		<div class="pdz-row__label">
			<label for="pdz_test_to">Invia un’email di prova</label>
			<p class="pdz-desc">Manda a questo indirizzo sia l’email interna sia quella di conferma, con la richiesta di esempio. Serve a verificare la consegna, non l’aspetto: per quello c’è l’anteprima qui sotto.</p>
		</div>
		<div class="pdz-row__field">
			<span class="pdz-num">
				<input type="email" class="pdz-input" id="pdz_test_to" name="pdz_test_to" value="<?php echo esc_attr( $current->user_email ); ?>" style="max-width:22rem;">
				<button type="submit" class="button" name="pdz_action" value="test_email" formnovalidate>Invia la prova</button>
			</span>
		</div>
	</div>
	<?php
}

/**
 * Selettore del tema, con un bozzetto della disposizione.
 */
function pdz_conf_tema_picker( $current ) {
	$wire = array(
		'corsia'    => '<rect x="4" y="6" width="24" height="5" rx="2" class="a"/><rect x="4" y="15" width="24" height="5" rx="2"/><rect x="4" y="24" width="24" height="5" rx="2"/><rect x="36" y="4" width="60" height="3" rx="1.5" class="a"/><rect x="36" y="13" width="28" height="12" rx="2"/><rect x="68" y="13" width="28" height="12" rx="2"/><rect x="36" y="29" width="28" height="12" rx="2"/><rect x="68" y="29" width="28" height="12" rx="2"/>',
		'pannello'  => '<rect x="3" y="3" width="94" height="54" rx="5" class="b"/><rect x="9" y="11" width="20" height="5" rx="2" class="a"/><rect x="9" y="20" width="20" height="5" rx="2"/><rect x="9" y="29" width="20" height="5" rx="2"/><rect x="37" y="11" width="26" height="12" rx="2"/><rect x="66" y="11" width="26" height="12" rx="2"/><rect x="37" y="27" width="26" height="12" rx="2"/><rect x="66" y="27" width="26" height="12" rx="2"/>',
		'contrasto' => '<rect x="3" y="3" width="30" height="54" rx="4" class="c"/><rect x="9" y="12" width="18" height="4" rx="2" class="a"/><rect x="9" y="21" width="18" height="4" rx="2" class="d"/><rect x="9" y="30" width="18" height="4" rx="2" class="d"/><rect x="40" y="10" width="52" height="4" rx="2"/><rect x="40" y="20" width="24" height="12" rx="2"/><rect x="68" y="20" width="24" height="12" rx="2"/><rect x="40" y="36" width="24" height="12" rx="2"/><rect x="68" y="36" width="24" height="12" rx="2"/>',
		'vivace'    => '<rect x="2" y="2" width="96" height="56" rx="8" class="e"/><circle cx="82" cy="12" r="14" class="g"/><circle cx="16" cy="50" r="12" class="h"/><rect x="9" y="12" width="18" height="5" rx="2.5" class="a"/><rect x="9" y="22" width="18" height="5" rx="2.5" class="d"/><rect x="9" y="32" width="18" height="5" rx="2.5" class="d"/><rect x="35" y="12" width="26" height="14" rx="5" class="f"/><rect x="65" y="12" width="26" height="14" rx="5" class="f"/><rect x="35" y="30" width="26" height="14" rx="5" class="f"/><rect x="65" y="30" width="26" height="14" rx="5" class="f"/>',
		'ampio'     => '<circle cx="9" cy="12" r="4" class="a"/><circle cx="9" cy="26" r="4"/><circle cx="9" cy="40" r="4"/><rect x="22" y="7" width="60" height="6" rx="3" class="a"/><rect x="22" y="20" width="17" height="12" rx="2"/><rect x="43" y="20" width="17" height="12" rx="2"/><rect x="64" y="20" width="17" height="12" rx="2"/><rect x="85" y="20" width="12" height="12" rx="2"/><rect x="22" y="36" width="17" height="12" rx="2"/><rect x="43" y="36" width="17" height="12" rx="2"/><rect x="64" y="36" width="17" height="12" rx="2"/>',
	);
	?>
	<div class="pdz-row pdz-row--picker">
		<div class="pdz-row__label">
			<label>Tema del configuratore</label>
			<p class="pdz-desc">Cambia la disposizione delle domande e delle fasi. I contenuti e la logica restano identici.</p>
		</div>
		<div class="pdz-row__field">
			<div class="pdz-picker pdz-picker--temi">
				<?php foreach ( pdz_conf_temi() as $key => $meta ) : ?>
					<label class="pdz-picker__item<?php echo $current === $key ? ' is-active' : ''; ?>">
						<input type="radio" name="pdz[tema]" value="<?php echo esc_attr( $key ); ?>" <?php checked( $current, $key ); ?>>
						<span class="pdz-wire">
							<svg viewBox="0 0 100 60" aria-hidden="true"><?php echo $wire[ $key ]; // bozzetto statico ?></svg>
						</span>
						<span class="pdz-picker__title"><?php echo esc_html( $meta[0] ); ?></span>
						<span class="pdz-picker__desc"><?php echo esc_html( $meta[1] ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Tabella dei permessi per ruolo e gestione del ruolo dedicato.
 */
function pdz_conf_accessi_rows() {
	$ruoli = pdz_conf_assignable_roles();
	$caps  = pdz_conf_caps();
	$slug  = pdz_conf_role_slug();
	?>
	<div class="pdz-row pdz-row--picker">
		<div class="pdz-row__label">
			<label>Chi può entrare</label>
			<p class="pdz-desc"><?php echo esc_html( $caps['pdz_conf_access'][1] ); ?></p>
		</div>
		<div class="pdz-row__field">
			<table class="pdz-table pdz-table--ruoli">
				<thead>
					<tr>
						<th>Ruolo</th>
						<th>Accesso al configuratore</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><strong>Amministratore</strong><span class="pdz-muted">unico ruolo che vede anche le Domande</span></td>
						<td><span class="pdz-muted">sempre attivo</span></td>
					</tr>
					<?php foreach ( $ruoli as $rslug => $nome ) : ?>
						<?php $role = get_role( $rslug ); ?>
						<tr>
							<td>
								<strong><?php echo esc_html( $nome ); ?></strong>
								<?php if ( $rslug === $slug ) : ?>
									<span class="pdz-muted">creato dal plugin</span>
								<?php endif; ?>
							</td>
							<td>
								<label class="pdz-switch">
									<input type="checkbox" name="pdz_roles[<?php echo esc_attr( $rslug ); ?>][pdz_conf_access]" value="1"
										<?php checked( $role && $role->has_cap( 'pdz_conf_access' ) ); ?>>
									<span class="pdz-switch__track"></span>
								</label>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>

	<div class="pdz-row">
		<div class="pdz-row__label">
			<label>Ruolo dedicato</label>
			<p class="pdz-desc">Un ruolo che entra in bacheca e vede solo il configuratore, senza articoli, pagine, plugin o utenti. È il modo più pulito per dare un accesso a chi deve seguire le richieste.</p>
		</div>
		<div class="pdz-row__field">
			<?php if ( get_role( pdz_conf_role_slug() ) ) : ?>
				<p><strong>«Gestore configuratore» è attivo.</strong> Lo assegni a una persona da Utenti → Modifica → Ruolo.</p>
				<button type="submit" class="button" name="pdz_action" value="elimina_ruolo"
					data-pdz-confirm="Eliminare il ruolo? Gli utenti che ce l’hanno resteranno senza permessi.">Elimina il ruolo</button>
			<?php else : ?>
				<button type="submit" class="button" name="pdz_action" value="crea_ruolo">Crea il ruolo «Gestore configuratore»</button>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * Regolazioni del tema Vivace. Compaiono solo quando quel tema è selezionato.
 */
function pdz_conf_vivace_rows( $s ) {
	echo '<div class="pdz-solotema" data-pdz-solotema="vivace">';

	pdz_conf_field_row( 'Colore di fondo', 'vivace_bg', $s['vivace_bg'], 'color', 'Il fondo del pannello. Più è scuro, più il vetro e gli aloni si notano.' );
	pdz_conf_field_row( 'Secondo colore', 'vivace_secondo', $s['vivace_secondo'], 'color', 'Si sfuma con il colore di accento su pulsanti, barra di avanzamento e aloni.' );
	pdz_conf_field_row( 'Arrotondamento del pannello', 'vivace_radius', $s['vivace_radius'], 'number', '0 per angoli vivi.', array( 'suffix' => 'px' ) );
	pdz_conf_field_row( 'Pulsanti', 'vivace_bottoni', $s['vivace_bottoni'], 'select', '', array( 'options' => pdz_conf_bottoni() ) );
	pdz_conf_field_row( 'Velocità delle animazioni', 'vivace_velocita', $s['vivace_velocita'], 'select', '', array( 'options' => pdz_conf_velocita() ) );
	pdz_conf_field_row( 'Aloni sul fondo', 'vivace_aloni', $s['vivace_aloni'], 'checkbox', 'Le due macchie di colore che si muovono lentamente dietro al contenuto.', array( 'label' => 'Mostra gli aloni' ) );
	pdz_conf_field_row( 'Effetto vetro', 'vivace_vetro', $s['vivace_vetro'], 'checkbox', 'Sfoca ciò che sta dietro alle risposte. Su dispositivi lenti conviene toglierlo.', array( 'label' => 'Sfoca lo sfondo dei riquadri' ) );

	echo '</div>';
}

/**
 * Riquadro con l'anteprima dell'email, aggiornata mentre si scrive.
 */
function pdz_conf_email_preview( $type ) {
	?>
	<div class="pdz-preview" data-pdz-preview="<?php echo esc_attr( $type ); ?>"
		data-nonce="<?php echo esc_attr( wp_create_nonce( 'pdz_conf_preview' ) ); ?>"
		data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">

		<div class="pdz-preview__head">
			<strong>Anteprima<?php echo 'utente' === $type ? ' dell’email all’utente' : ' dell’email interna'; ?></strong>
			<span class="pdz-preview__state" data-pdz-preview-state></span>
		</div>

		<div class="pdz-preview__meta">
			<span><em>A</em> <span data-pdz-preview-to>—</span></span>
			<span><em>Da</em> <span data-pdz-preview-from>—</span></span>
			<span><em>Oggetto</em> <span data-pdz-preview-subject>—</span></span>
		</div>

		<iframe class="pdz-preview__frame" title="Anteprima email" data-pdz-preview-frame></iframe>

		<p class="pdz-desc">Generata con una richiesta di esempio (villa a Pavia, percorso pompa di calore) usando lo stesso codice delle email vere. Si aggiorna mentre modifichi i campi qui sopra.</p>
	</div>
	<?php
}

function pdz_conf_hero_picker( $current ) {
	?>
	<div class="pdz-row pdz-row--picker">
		<div class="pdz-row__label">
			<label>Illustrazione di apertura</label>
			<p class="pdz-desc">Compare a fianco del testo. Le anteprime qui sotto sono animate come in pagina.</p>
		</div>
		<div class="pdz-row__field">
			<div class="pdz-picker">
				<?php foreach ( pdz_conf_hero_styles() as $key => $meta ) : ?>
					<label class="pdz-picker__item<?php echo $current === $key ? ' is-active' : ''; ?>">
						<input type="radio" name="pdz[hero_style]" value="<?php echo esc_attr( $key ); ?>" <?php checked( $current, $key ); ?>>
						<span class="pdz-picker__preview pdz-conf">
							<?php
							if ( 'nessuna' === $key ) {
								echo '<span class="pdz-picker__none">—</span>';
							} else {
								echo pdz_conf_hero_svg( $key );
							}
							?>
						</span>
						<span class="pdz-picker__title"><?php echo esc_html( $meta[0] ); ?></span>
						<span class="pdz-picker__desc"><?php echo esc_html( $meta[1] ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<?php
}

function pdz_conf_section_open( $id, $title, $intro = '' ) {
	printf(
		'<section class="pdz-set" id="pdz-panel-%1$s" data-pdz-panel="%1$s"><header class="pdz-set__head"><h2>%2$s</h2>%3$s</header><div class="pdz-set__body">',
		esc_attr( $id ),
		esc_html( $title ),
		$intro ? '<p>' . wp_kses_post( $intro ) . '</p>' : ''
	);
}

function pdz_conf_section_close() {
	echo '</div></section>';
}

/* -------------------------------------------------------------------------
 * Pagina impostazioni
 * ---------------------------------------------------------------------- */

function pdz_conf_render_settings_page() {
	if ( ! current_user_can( pdz_conf_cap() ) ) {
		return;
	}

	$s = pdz_conf_settings();

	$tabs = array(
		'email'    => 'Email',
		'utente'   => 'Conferma all’utente',
		'grazie'   => 'Thank you page',
		'privacy'  => 'Privacy',
		'allegati' => 'Allegati',
		'priorita' => 'Priorità',
		'testi'    => 'Testi di apertura',
		'aspetto'  => 'Aspetto',
	);

	if ( current_user_can( 'manage_options' ) ) {
		$tabs['accessi'] = 'Accessi';
	}
	?>
	<div class="wrap pdz-admin pdz-settings">
		<div class="pdz-head">
			<div>
				<h1>Impostazioni configuratore</h1>
				<p class="pdz-head__sub">
					Inserisci il configuratore in pagina con <code>[configuratore]</code>.
					Le email partono con <code>wp_mail()</code>: un eventuale plugin SMTP le instrada da solo.
				</p>
			</div>
		</div>

		<?php if ( isset( $_GET['saved'] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p>Impostazioni salvate.</p></div>
		<?php endif; ?>

		<?php if ( isset( $_GET['test'] ) ) : ?>
			<?php
			$esiti = array(
				'ok'        => array( 'success', 'Email di prova inviate. Se non arrivano entro qualche minuto, controlla la posta indesiderata e la configurazione del plugin SMTP.' ),
				'parziale'  => array( 'warning', 'Solo una delle due email è partita. Controlla il registro del plugin SMTP.' ),
				'errore'    => array( 'error', 'Invio non riuscito: il server ha rifiutato le email. Serve un plugin SMTP configurato, oppure il mittente non è autorizzato per il dominio.' ),
				'indirizzo' => array( 'error', 'Indirizzo non valido.' ),
			);
			$code  = sanitize_key( $_GET['test'] );
			$esito = isset( $esiti[ $code ] ) ? $esiti[ $code ] : null;
			?>
			<?php if ( $esito ) : ?>
				<div class="notice notice-<?php echo esc_attr( $esito[0] ); ?> is-dismissible"><p><?php echo esc_html( $esito[1] ); ?></p></div>
			<?php endif; ?>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="pdz-settings__form">
			<input type="hidden" name="action" value="pdz_conf_save_settings">
			<input type="hidden" name="tab" value="" data-pdz-tab-field>
			<?php wp_nonce_field( 'pdz_conf_settings' ); ?>

			<div class="pdz-settings__grid">
				<nav class="pdz-tabs" data-pdz-tabs aria-label="Sezioni delle impostazioni">
					<?php $first = true; ?>
					<?php foreach ( $tabs as $key => $label ) : ?>
						<button type="button" class="pdz-tab<?php echo $first ? ' is-active' : ''; ?>" data-pdz-tab="<?php echo esc_attr( $key ); ?>">
							<?php echo esc_html( $label ); ?>
						</button>
						<?php $first = false; ?>
					<?php endforeach; ?>
				</nav>

				<div class="pdz-settings__panels">

					<?php pdz_conf_section_open( 'email', 'Email al gestore del sito', 'Il riepilogo completo della richiesta, con punteggio e allegati.' ); ?>
						<?php
						pdz_conf_field_row( 'Destinatari', 'email_to', $s['email_to'], 'text', 'Uno o più indirizzi separati da virgola.' );
						pdz_conf_field_row( 'Destinatari Divisione Elettrica', 'email_to_elettrica', $s['email_to_elettrica'], 'text', 'Solo per le richieste di quel percorso. Se vuoto valgono i destinatari sopra.' );
						pdz_conf_field_row( 'Oggetto', 'email_subject', $s['email_subject'], 'text', 'Segnaposto: <code>{percorso}</code> <code>{comune}</code> <code>{nome}</code> <code>{provincia}</code> <code>{punteggio}</code>.' );
						pdz_conf_field_row( 'Testo introduttivo', 'email_intro', $s['email_intro'], 'textarea' );
						pdz_conf_field_row( 'Allegati nell’email', 'attach_to_email', $s['attach_to_email'], 'checkbox', 'Sotto i 4 MB. Restano comunque scaricabili dai link nel riepilogo.', array( 'label' => 'Allega i documenti caricati' ) );
						pdz_conf_field_row( 'Indirizzo mittente', 'email_from', $s['email_from'], 'text', 'Lascia vuoto per usare il mittente del plugin SMTP: è la scelta più sicura.' );
						pdz_conf_field_row( 'Nome mittente', 'email_from_name', $s['email_from_name'], 'text' );
						pdz_conf_test_email_row();
						pdz_conf_email_preview( 'gestore' );
						?>
					<?php pdz_conf_section_close(); ?>

					<?php pdz_conf_section_open( 'utente', 'Email di conferma all’utente', 'Lo stesso riepilogo, senza punteggio interno né link agli allegati.' ); ?>
						<?php
						pdz_conf_field_row( 'Invio', 'user_email_enabled', $s['user_email_enabled'], 'checkbox', '', array( 'label' => 'Invia l’email di conferma' ) );
						pdz_conf_field_row( 'Oggetto', 'user_email_subject', $s['user_email_subject'], 'text' );
						pdz_conf_field_row( 'Testo introduttivo', 'user_email_intro', $s['user_email_intro'], 'textarea' );
						pdz_conf_field_row( 'Testo in chiusura', 'user_email_footer', $s['user_email_footer'], 'textarea', '', array( 'rows' => 2 ) );
						pdz_conf_field_row( 'Riepilogo delle risposte', 'user_email_summary', $s['user_email_summary'], 'checkbox', 'Le risposte tecniche che ha dato durante il percorso.', array( 'label' => 'Includi le risposte' ) );
						pdz_conf_field_row( 'Dati di contatto', 'user_email_contacts', $s['user_email_contacts'], 'checkbox', 'Nome, email, telefono, preferenza e fascia oraria indicati nell’ultimo step.', array( 'label' => 'Includi i dati di contatto' ) );
						pdz_conf_field_row( 'Elenco degli allegati', 'user_email_files', $s['user_email_files'], 'checkbox', 'Solo i nomi dei file: i link restano riservati all’email interna.', array( 'label' => 'Includi l’elenco dei file' ) );
						pdz_conf_field_row( 'File allegati all’email', 'user_email_attach', $s['user_email_attach'], 'checkbox', 'Rimanda all’utente i documenti che ha caricato. Sotto i 4 MB.', array( 'label' => 'Allega i file all’email' ) );
						pdz_conf_email_preview( 'utente' );
						?>
					<?php pdz_conf_section_close(); ?>

					<?php pdz_conf_section_open( 'grazie', 'Thank you page', 'La provenienza si ricava dalla provincia indicata nello step 2. Se un campo resta vuoto non c’è redirect e viene mostrato il messaggio corrispondente.' ); ?>
						<?php
						pdz_conf_field_row( 'Utente in Lombardia', 'thankyou_lombardia', $s['thankyou_lombardia'], 'url' );
						pdz_conf_field_row( 'Utente fuori Lombardia', 'thankyou_fuori', $s['thankyou_fuori'], 'url' );
						pdz_conf_field_row( 'Richiesta Divisione Elettrica', 'thankyou_elettrica', $s['thankyou_elettrica'], 'url', 'Facoltativa: se vuota vale la regola Lombardia / fuori Lombardia.' );
						pdz_conf_field_row( 'Messaggio Lombardia', 'msg_standard', $s['msg_standard'], 'textarea', 'Mostrato solo quando non c’è redirect. HTML consentito.' );
						pdz_conf_field_row( 'Messaggio fuori Lombardia', 'msg_fuori', $s['msg_fuori'], 'textarea' );
						pdz_conf_field_row( 'Messaggio Divisione Elettrica', 'msg_elettrica', $s['msg_elettrica'], 'textarea' );
						pdz_conf_field_row( 'Pulsante sotto al messaggio', 'msg_button_label', $s['msg_button_label'], 'text', 'Dà una via d’uscita a chi ha finito. Lascia vuoto per non mostrarlo.' );
						pdz_conf_field_row( 'Indirizzo del pulsante', 'msg_button_url', $s['msg_button_url'], 'url' );
						?>
					<?php pdz_conf_section_close(); ?>

					<?php pdz_conf_section_open( 'privacy', 'Privacy', 'La casella di accettazione è obbligatoria per inviare la richiesta.' ); ?>
						<?php
						pdz_conf_field_row( 'Testo della checkbox', 'privacy_text', $s['privacy_text'], 'textarea', '', array( 'rows' => 2 ) );
						pdz_conf_field_row( 'Link all’informativa', 'privacy_url', $s['privacy_url'], 'url', 'Può puntare a una pagina esterna. Si apre in una nuova scheda.' );
						pdz_conf_field_row( 'Testo del link', 'privacy_link_label', $s['privacy_link_label'], 'text' );
						?>
					<?php pdz_conf_section_close(); ?>

					<?php pdz_conf_section_open( 'allegati', 'Allegati', 'I file finiscono in <code>/uploads/configuratore/</code> con nome randomizzato.' ); ?>
						<?php
						pdz_conf_field_row( 'Numero massimo di file', 'max_files', $s['max_files'], 'number' );
						pdz_conf_field_row( 'Peso massimo per file', 'max_file_size', $s['max_file_size'], 'number', '', array( 'suffix' => 'MB' ) );
						pdz_conf_field_row( 'Estensioni ammesse', 'allowed_ext', $s['allowed_ext'], 'text', 'Separate da virgola.' );
						?>
					<?php pdz_conf_section_close(); ?>

					<?php pdz_conf_section_open( 'priorita', 'Priorità delle richieste', 'I punti delle singole risposte si impostano nella schermata <a href="' . esc_url( admin_url( 'admin.php?page=pdz-conf-questions' ) ) . '">Domande</a>. Qui ci sono i bonus di contesto e le soglie. Il totale è comunque limitato a 100.' ); ?>
						<?php
						pdz_conf_field_row( 'Immobile in Lombardia', 'score_lombardia', $s['score_lombardia'], 'number', '', array( 'suffix' => 'punti' ) );
						pdz_conf_field_row( 'Resto del Nord Italia', 'score_nord', $s['score_nord'], 'number', '', array( 'suffix' => 'punti' ) );
						pdz_conf_field_row( 'Ha caricato allegati', 'score_allegati', $s['score_allegati'], 'number', '', array( 'suffix' => 'punti' ) );
						pdz_conf_field_row( 'Ha caricato un preventivo', 'score_preventivo', $s['score_preventivo'], 'number', '', array( 'suffix' => 'punti' ) );
						pdz_conf_field_row( 'Dati tecnici completi', 'score_dati_completi', $s['score_dati_completi'], 'number', 'Quando meno del 30% delle risposte è «Non so».', array( 'suffix' => 'punti' ) );
						pdz_conf_field_row( 'Messaggio dettagliato', 'score_messaggio', $s['score_messaggio'], 'number', 'Da 120 caratteri in su.', array( 'suffix' => 'punti' ) );
						pdz_conf_field_row( 'Soglia priorità alta', 'soglia_caldo', $s['soglia_caldo'], 'number' );
						pdz_conf_field_row( 'Soglia priorità media', 'soglia_medio', $s['soglia_medio'], 'number', 'Sotto questa soglia la priorità è bassa.' );
						pdz_conf_field_row( 'Etichetta priorità alta', 'label_alta', $s['label_alta'], 'text', 'Come viene chiamata nelle schermate, nel CSV e nell’email interna.' );
						pdz_conf_field_row( 'Etichetta priorità media', 'label_media', $s['label_media'], 'text' );
						pdz_conf_field_row( 'Etichetta priorità bassa', 'label_bassa', $s['label_bassa'], 'text' );
						?>
					<?php pdz_conf_section_close(); ?>

					<?php pdz_conf_section_open( 'testi', 'Testi di apertura', 'La prima schermata che vede l’utente.' ); ?>
						<?php
						pdz_conf_field_row( 'Sopratitolo', 'intro_kicker', $s['intro_kicker'], 'text' );
						pdz_conf_field_row( 'Titolo', 'intro_title', $s['intro_title'], 'text' );
						pdz_conf_field_row( 'Testo', 'intro_text', $s['intro_text'], 'textarea', 'Una riga vuota separa i paragrafi.', array( 'rows' => 5 ) );
						pdz_conf_field_row( 'Nota evidenziata', 'intro_note', $s['intro_note'], 'textarea', '', array( 'rows' => 2 ) );
						pdz_conf_field_row( 'Testo del pulsante', 'intro_cta', $s['intro_cta'], 'text' );
						pdz_conf_field_row( 'Riga sotto il pulsante', 'intro_cta_note', $s['intro_cta_note'], 'text' );
						pdz_conf_field_row( 'Punti di apertura', 'intro_points', $s['intro_points'], 'textarea', 'Uno per riga, formato <code>Titolo|Descrizione</code>. Massimo tre, vuoto per non mostrarli.', array( 'rows' => 4 ) );
						?>
					<?php pdz_conf_section_close(); ?>

					<?php pdz_conf_section_open( 'aspetto', 'Aspetto e antispam', '' ); ?>
						<?php
						pdz_conf_field_row( 'Colore di accento', 'accent', $s['accent'], 'color' );
						pdz_conf_field_row( 'Arrotondamento dei pulsanti', 'btn_radius', $s['btn_radius'], 'number', '0 per angoli vivi, 999 per pulsanti a pillola.', array( 'suffix' => 'px' ) );
						pdz_conf_field_row( 'Larghezza massima', 'max_width', $s['max_width'], 'number', 'Il contenitore della pagina deve essere a larghezza piena.', array( 'suffix' => 'px' ) );
						pdz_conf_tema_picker( pdz_conf_tema() );
						pdz_conf_vivace_rows( $s );
						pdz_conf_field_row( 'Conteggio delle domande', 'rail_counts', $s['rail_counts'], 'checkbox', 'La riga «3 domande» sotto il nome di ogni fase. Toglierla rende la colonna più asciutta.', array( 'label' => 'Mostra quante domande contiene ogni fase' ) );
						pdz_conf_hero_picker( pdz_conf_hero_style() );
						pdz_conf_field_row( 'Font Inter', 'load_google_font', $s['load_google_font'], 'checkbox', 'Disattiva se Inter è già caricato dal tema.', array( 'label' => 'Carica Inter da Google Fonts' ) );
						pdz_conf_field_row( 'Tracciamento degli abbandoni', 'track_enabled', $s['track_enabled'], 'checkbox', 'Registra a che passaggio si ferma chi non invia, per la schermata Andamento. Non salva risposte, contatti o indirizzi IP.', array( 'label' => 'Registra l’avanzamento' ) );
						pdz_conf_field_row( 'Conservazione delle tracce', 'track_days', $s['track_days'], 'number', 'Oltre questa soglia le tracce vengono cancellate ogni notte.', array( 'suffix' => 'giorni' ) );
						pdz_conf_field_row( 'Tempo minimo di compilazione', 'min_seconds', $s['min_seconds'], 'number', 'Invii più rapidi vengono scartati come spam.', array( 'suffix' => 'secondi' ) );
						pdz_conf_field_row( 'Invii massimi per IP', 'rate_limit', $s['rate_limit'], 'number', 'Superato il limite compare «Troppe richieste dallo stesso indirizzo». Chi è collegato come amministratore non viene mai bloccato, così puoi collaudare liberamente.', array( 'suffix' => 'all’ora' ) );
						?>
					<?php pdz_conf_section_close(); ?>

					<?php if ( current_user_can( 'manage_options' ) ) : ?>
						<?php pdz_conf_section_open( 'accessi', 'Chi può usare il configuratore', 'Serve per dare accesso al plugin senza consegnare le chiavi di tutto il sito. La sezione Domande resta visibile ai soli amministratori, così la struttura del percorso non si tocca per sbaglio.' ); ?>
							<?php pdz_conf_accessi_rows(); ?>
						<?php pdz_conf_section_close(); ?>
					<?php endif; ?>

				</div>
			</div>

			<div class="pdz-stickysave">
				<span>Le modifiche non sono ancora salvate.</span>
				<button class="button button-primary button-large">Salva impostazioni</button>
			</div>
		</form>
	</div>
	<?php
}

/**
 * Anteprima dell'email, generata con lo stesso codice che compone quelle vere.
 */
add_action( 'wp_ajax_pdz_conf_preview_email', function () {
	if ( ! current_user_can( pdz_conf_cap() ) ) {
		wp_send_json_error( array( 'message' => 'Non autorizzato.' ), 403 );
	}
	check_ajax_referer( 'pdz_conf_preview', 'nonce' );

	$in   = isset( $_POST['pdz'] ) ? (array) wp_unslash( $_POST['pdz'] ) : array();
	$type = ( isset( $_POST['type'] ) && 'utente' === $_POST['type'] ) ? 'utente' : 'gestore';

	// I valori del modulo valgono solo per questa richiesta.
	pdz_conf_set_override( pdz_conf_sanitize_settings( $in ) );

	$answers = pdz_conf_sample_answers();
	$path    = pdz_conf_resolve_path( $answers );
	$files   = array(
		'file_allegati' => array(
			array( 'name' => 'foto-caldaia.jpg', 'url' => '#', 'file' => '' ),
			array( 'name' => 'bolletta-2026.pdf', 'url' => '#', 'file' => '' ),
		),
	);
	$score = pdz_conf_score( $answers, $files, $path );

	if ( 'utente' === $type ) {
		$parts = pdz_conf_user_email_parts( $answers, $files, $path );
		$to    = isset( $answers['email'] ) ? $answers['email'] : '';
	} else {
		$summary = pdz_conf_build_summary( $answers, $files, $path, $score, 'admin' );
		$parts   = pdz_conf_admin_email_parts( $answers, $summary, $score, $path );
		$to      = implode( ', ', pdz_conf_recipients( $path ) );
	}

	$from = pdz_conf_get( 'email_from', '' );

	wp_send_json_success(
		array(
			'subject' => $parts['subject'],
			'body'    => $parts['body'],
			'to'      => $to,
			'from'    => $from ? $from : 'mittente del plugin SMTP',
		)
	);
} );

/**
 * Ripulisce i valori del modulo impostazioni. Usata dal salvataggio e dall'anteprima.
 */
function pdz_conf_sanitize_settings( $in ) {
	$defaults = pdz_conf_default_settings();
	$out      = pdz_conf_settings();

	$html_fields  = array( 'msg_standard', 'msg_fuori', 'msg_elettrica' );
	$url_fields   = array( 'thankyou_lombardia', 'thankyou_fuori', 'thankyou_elettrica', 'privacy_url', 'msg_button_url' );
	$int_fields   = array( 'max_files', 'max_file_size', 'min_seconds', 'rate_limit', 'track_days', 'score_lombardia', 'score_nord', 'score_allegati', 'score_preventivo', 'score_dati_completi', 'score_messaggio', 'soglia_caldo', 'soglia_medio', 'max_width', 'btn_radius', 'vivace_radius' );
	$check_fields = array( 'user_email_enabled', 'user_email_summary', 'user_email_contacts', 'user_email_files', 'user_email_attach', 'attach_to_email', 'load_google_font', 'track_enabled', 'rail_counts', 'vivace_aloni', 'vivace_vetro' );
	$area_fields  = array( 'email_intro', 'user_email_intro', 'user_email_footer', 'privacy_text', 'intro_text', 'intro_note', 'intro_points' );

	foreach ( $defaults as $key => $default ) {
		if ( '_host' === $key ) {
			continue;
		}

		if ( in_array( $key, $check_fields, true ) ) {
			$out[ $key ] = empty( $in[ $key ] ) ? 0 : 1;
			continue;
		}
		if ( ! isset( $in[ $key ] ) ) {
			continue;
		}

		if ( in_array( $key, $html_fields, true ) ) {
			$out[ $key ] = wp_kses_post( $in[ $key ] );
		} elseif ( in_array( $key, $url_fields, true ) ) {
			$out[ $key ] = esc_url_raw( trim( $in[ $key ] ) );
		} elseif ( in_array( $key, $int_fields, true ) ) {
			$out[ $key ] = (int) $in[ $key ];
		} elseif ( in_array( $key, $area_fields, true ) ) {
			$out[ $key ] = sanitize_textarea_field( $in[ $key ] );
		} elseif ( 'tema' === $key ) {
			$val         = sanitize_key( $in[ $key ] );
			$out[ $key ] = array_key_exists( $val, pdz_conf_temi() ) ? $val : 'corsia';
		} elseif ( 'hero_style' === $key ) {
			$val         = sanitize_key( $in[ $key ] );
			$out[ $key ] = array_key_exists( $val, pdz_conf_hero_styles() ) ? $val : 'circuito';
		} elseif ( 'vivace_bottoni' === $key ) {
			$val         = sanitize_key( $in[ $key ] );
			$out[ $key ] = array_key_exists( $val, pdz_conf_bottoni() ) ? $val : 'sfumato';
		} elseif ( 'vivace_velocita' === $key ) {
			$val         = sanitize_key( $in[ $key ] );
			$out[ $key ] = array_key_exists( $val, pdz_conf_velocita() ) ? $val : 'normale';
		} elseif ( in_array( $key, array( 'accent', 'vivace_bg', 'vivace_secondo' ), true ) ) {
			$hex         = sanitize_hex_color( trim( $in[ $key ] ) );
			$out[ $key ] = $hex ? $hex : $defaults[ $key ];
		} else {
			$out[ $key ] = sanitize_text_field( $in[ $key ] );
		}
	}

	return $out;
}

add_action( 'admin_post_pdz_conf_save_settings', 'pdz_conf_save_settings' );

function pdz_conf_save_settings() {
	if ( ! current_user_can( pdz_conf_cap() ) ) {
		wp_die( 'Non autorizzato.' );
	}
	check_admin_referer( 'pdz_conf_settings' );

	$in  = isset( $_POST['pdz'] ) ? (array) wp_unslash( $_POST['pdz'] ) : array();
	$out = pdz_conf_sanitize_settings( $in );

	update_option( 'pdz_conf_settings', $out );

	// Permessi e ruolo dedicato: solo chi amministra il sito.
	if ( current_user_can( 'manage_options' ) ) {
		if ( isset( $_POST['pdz_roles'] ) ) {
			pdz_conf_sync_roles( (array) wp_unslash( $_POST['pdz_roles'] ) );
		} elseif ( isset( $_POST['tab'] ) && 'accessi' === $_POST['tab'] ) {
			pdz_conf_sync_roles( array() );
		}

		if ( isset( $_POST['pdz_action'] ) ) {
			if ( 'crea_ruolo' === $_POST['pdz_action'] ) {
				pdz_conf_create_role();
			} elseif ( 'elimina_ruolo' === $_POST['pdz_action'] ) {
				pdz_conf_delete_role();
			}
		}
	}

	// Il pulsante di prova salva e poi invia, così la prova usa i valori appena scritti.
	if ( isset( $_POST['pdz_action'] ) && 'test_email' === $_POST['pdz_action'] ) {
		$to     = isset( $_POST['pdz_test_to'] ) ? sanitize_email( wp_unslash( $_POST['pdz_test_to'] ) ) : '';
		$esito  = pdz_conf_send_test_email( $to );
		$tab    = isset( $_POST['tab'] ) ? sanitize_key( $_POST['tab'] ) : 'email';

		wp_safe_redirect(
			add_query_arg(
				array( 'page' => 'pdz-conf-settings', 'test' => $esito ),
				admin_url( 'admin.php' )
			) . '#' . ( $tab ? $tab : 'email' )
		);
		exit;
	}

	$tab = isset( $_POST['tab'] ) ? sanitize_key( $_POST['tab'] ) : '';
	wp_safe_redirect( admin_url( 'admin.php?page=pdz-conf-settings&saved=1' ) . ( $tab ? '#' . $tab : '' ) );
	exit;
}
