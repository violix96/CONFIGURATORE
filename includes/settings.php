<?php
/**
 * Impostazioni del plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function pdz_conf_default_settings() {
	$host = wp_parse_url( home_url(), PHP_URL_HOST );

	return array(
		// Email
		'email_to'            => get_option( 'admin_email' ),
		'email_to_elettrica'  => '',
		'email_from'          => '',
		'email_from_name'     => get_bloginfo( 'name' ),
		'email_subject'       => 'Nuova richiesta configuratore - {percorso} - {comune} - {nome}',
		'email_intro'         => 'Nuova richiesta ricevuta dal configuratore.',

		// Email di conferma all'utente
		'user_email_enabled'  => 1,
		'user_email_summary'  => 1,
		'user_email_contacts' => 1,
		'user_email_files'    => 1,
		'user_email_attach'   => 0,
		'user_email_subject'  => 'Abbiamo ricevuto la tua richiesta - Pedrinazzi Srl',
		'user_email_intro'    => 'Grazie {nome}, abbiamo ricevuto la tua richiesta. Qui sotto trovi il riepilogo dei dati che ci hai inviato. Ti ricontatteremo per approfondire i dettagli.',
		'user_email_footer'   => 'Pedrinazzi Srl — questa email è una conferma automatica, puoi rispondere per aggiungere informazioni.',

		// Thank you page
		'thankyou_lombardia'  => '',
		'thankyou_fuori'      => '',
		'thankyou_elettrica'  => '',
		'msg_button_label'    => 'Torna alla home',
		'msg_button_url'      => home_url( '/' ),

		// Privacy
		'privacy_text'        => 'Ho letto e accetto l’informativa sul trattamento dei dati personali.',
		'privacy_url'         => get_privacy_policy_url(),
		'privacy_link_label'  => 'Leggi l’informativa',

		// Upload
		'max_files'           => 8,
		'max_file_size'       => 8,
		'allowed_ext'         => 'jpg, jpeg, png, webp, heic, pdf, doc, docx, xls, xlsx',
		'attach_to_email'     => 1,

		// Antispam
		'track_enabled'       => 1,
		'track_days'          => 180,
		'min_seconds'         => 8,
		'rate_limit'          => 5,

		// Punteggio
		'score_lombardia'     => 20,
		'score_nord'          => 5,
		'score_allegati'      => 10,
		'score_preventivo'    => 10,
		'score_dati_completi' => 10,
		'score_messaggio'     => 5,
		'soglia_caldo'        => 65,
		'soglia_medio'        => 35,
		'label_alta'          => 'Priorità alta',
		'label_media'         => 'Priorità media',
		'label_bassa'         => 'Priorità bassa',

		// Aspetto
		'load_google_font'    => 1,
		'accent'              => '#e4171c',
		'btn_radius'          => 4,
		'max_width'           => 1600,

		// Testi intro
		'intro_kicker'        => 'Configuratore',
		'intro_title'         => 'Valuta il tuo impianto',
		'intro_text'          => "Rispondi ad alcune domande sulla tua abitazione, sull’impianto attuale e sugli obiettivi dell’intervento.\n\nIl percorso ci aiuta a capire se stai valutando una pompa di calore, un progetto più completo di elettrificazione della casa, l’integrazione con fotovoltaico e accumulo o una richiesta tecnica diversa.",
		'intro_note'          => 'Le informazioni inviate non generano un preventivo automatico: permettono a Pedrinazzi Srl di inquadrare il tuo caso e capire quale percorso tecnico può avere senso.',
		'intro_cta'           => 'Inizia la valutazione',
		'intro_cta_note'      => 'Bastano 2–3 minuti. Nessun dato viene pubblicato.',
		'intro_points'        => "Domande su casa e impianto|Partiamo da quello che hai oggi: generatore, terminali, superficie.\nPercorso su misura|Le domande cambiano in base all’obiettivo che scegli.\nRisposta di una persona|Non è un preventivo automatico: la richiesta viene letta da un tecnico.",
		'hero_style'          => 'circuito',
		'tema'                => 'corsia',
		'vivace_bg'           => '#0c0f13',
		'vivace_secondo'      => '#ff9a3d',
		'vivace_radius'       => 28,
		'vivace_velocita'     => 'normale',
		'vivace_aloni'        => 1,
		'vivace_vetro'        => 1,
		'vivace_bottoni'      => 'sfumato',
		'rail_counts'         => 1,

		// Messaggi finali usati quando non e' impostata una thank you page
		'msg_standard'        => "<h3>Grazie, abbiamo ricevuto la tua richiesta.</h3><p>Le informazioni inviate ci aiuteranno a inquadrare il tuo caso e a capire quale percorso tecnico può avere senso. Pedrinazzi Srl ti ricontatterà per approfondire i dettagli.</p>",
		'msg_fuori'           => "<h3>Grazie, abbiamo ricevuto la tua richiesta.</h3><p>Pedrinazzi Srl opera principalmente in Lombardia, ma valuteremo comunque le informazioni inviate per capire se il caso può essere seguito o se è preferibile orientarti verso un altro percorso.</p>",
		'msg_elettrica'       => "<h3>Grazie, abbiamo ricevuto la tua richiesta tecnica.</h3><p>Le informazioni inviate verranno valutate per capire il contesto dell’intervento e la coerenza con le competenze della Divisione Elettrica di Pedrinazzi Srl.</p>",

		'_host'               => $host,
	);
}

/**
 * Tutte le impostazioni, con i valori predefiniti a colmare i buchi.
 */
function pdz_conf_settings() {
	// L'anteprima nelle impostazioni lavora sui valori del modulo, non su quelli salvati.
	if ( isset( $GLOBALS['pdz_conf_override'] ) && is_array( $GLOBALS['pdz_conf_override'] ) ) {
		return wp_parse_args( $GLOBALS['pdz_conf_override'], pdz_conf_default_settings() );
	}

	$saved = get_option( 'pdz_conf_settings', array() );
	return wp_parse_args( is_array( $saved ) ? $saved : array(), pdz_conf_default_settings() );
}

/**
 * Applica dei valori temporanei, usati solo per generare l'anteprima.
 */
function pdz_conf_set_override( $values ) {
	$GLOBALS['pdz_conf_override'] = $values;
}

/**
 * Una singola impostazione.
 */
function pdz_conf_get( $key, $default = '' ) {
	$s = pdz_conf_settings();
	return isset( $s[ $key ] ) && '' !== $s[ $key ] ? $s[ $key ] : $default;
}

/**
 * Estensioni consentite, come array.
 */
function pdz_conf_allowed_ext() {
	$raw = pdz_conf_get( 'allowed_ext', 'jpg, jpeg, png, pdf' );
	$out = array_filter( array_map( 'trim', explode( ',', strtolower( $raw ) ) ) );
	return $out;
}

/**
 * Destinatari email interna (accetta piu' indirizzi separati da virgola).
 */
function pdz_conf_recipients( $path ) {
	$key = ( 'F' === $path ) ? 'email_to_elettrica' : 'email_to';
	$raw = pdz_conf_get( $key, '' );
	if ( ! $raw ) {
		$raw = pdz_conf_get( 'email_to', get_option( 'admin_email' ) );
	}
	$list = array_filter( array_map( 'trim', explode( ',', $raw ) ), 'is_email' );
	return $list ? $list : array( get_option( 'admin_email' ) );
}

/**
 * Sostituisce i segnaposto {nome}, {comune}, {percorso}, {punteggio}.
 */
function pdz_conf_placeholders( $text, $answers, $path = '', $score = null ) {
	$map = array(
		'{nome}'      => isset( $answers['nome'] ) ? $answers['nome'] : '',
		'{email}'     => isset( $answers['email'] ) ? $answers['email'] : '',
		'{telefono}'  => isset( $answers['telefono'] ) ? $answers['telefono'] : '',
		'{comune}'    => isset( $answers['comune'] ) ? $answers['comune'] : '',
		'{provincia}' => isset( $answers['provincia'] ) ? $answers['provincia'] : '',
		'{percorso}'  => $path ? pdz_conf_path_label( $path ) : '',
		'{punteggio}' => null === $score ? '' : (int) $score,
	);
	return strtr( $text, $map );
}

/**
 * Illustrazioni disponibili per la schermata di apertura.
 */
function pdz_conf_hero_styles() {
	return array(
		'circuito' => array( 'Circuito con pompa', 'Il fluido esce caldo dalla pompa, cede calore all’utenza e rientra freddo.' ),
		'schema'   => array( 'Schema d’impianto', 'Captazione, macchina ed edificio collegati da un flusso di energia.' ),
		'pompa'    => array( 'Pompa e dosaggio', 'Trasferimento di fluido da una condotta all’altra, con erogazione controllata.' ),
		'onda'     => array( 'Onda termica', 'Linee che scorrono e passano dal freddo al caldo attraversando il sistema.' ),
		'resa'     => array( 'Resa energetica', 'L’anello dell’energia assorbita e quello, molto più lungo, del calore restituito.' ),
		'rete'     => array( 'Impulso in rete', 'Un impulso che percorre la griglia e accende i nodi, uno dopo l’altro.' ),
		'emissione'=> array( 'Emissione', 'La macchina in funzione che diffonde calore nell’ambiente.' ),
		'nessuna'  => array( 'Nessuna illustrazione', 'Il testo di apertura occupa tutta la larghezza.' ),
	);
}

/**
 * Illustrazione scelta, con compatibilita' verso la vecchia spunta.
 */
function pdz_conf_hero_style() {
	$s     = pdz_conf_settings();
	$style = isset( $s['hero_style'] ) ? $s['hero_style'] : 'circuito';

	if ( isset( $s['hero_visual'] ) && ! $s['hero_visual'] && ! isset( $s['hero_style'] ) ) {
		return 'nessuna';
	}

	return array_key_exists( $style, pdz_conf_hero_styles() ) ? $style : 'circuito';
}

/**
 * Temi disponibili per il configuratore.
 */
function pdz_conf_temi() {
	return array(
		'corsia'    => array( 'Corsia laterale', 'Le fasi in colonna a sinistra e le risposte a scheda, su sfondo bianco.' ),
		'pannello'  => array( 'Pannello', 'Il configuratore vive dentro una scheda rialzata, staccata dallo sfondo della pagina.' ),
		'contrasto' => array( 'Contrasto', 'Colonna delle fasi su fondo scuro, domande su fondo chiaro.' ),
		'ampio'     => array( 'Ampio', 'Corsia ridotta alle sole icone, titoli grandi e risposte su quattro colonne.' ),
		'vivace'    => array( 'Vivace', 'Pannello scuro con aloni in movimento, riquadri di vetro e pulsanti luminosi.' ),
	);
}

function pdz_conf_tema() {
	$tema = pdz_conf_get( 'tema', 'corsia' );
	return array_key_exists( $tema, pdz_conf_temi() ) ? $tema : 'corsia';
}

/**
 * Velocità delle animazioni del tema Vivace: moltiplicatore delle durate.
 */
function pdz_conf_velocita() {
	return array(
		'lenta'    => 'Lenta — movimenti appena percettibili',
		'normale'  => 'Normale',
		'veloce'   => 'Veloce — animazioni più marcate',
	);
}

function pdz_conf_fattore_velocita() {
	$map = array( 'lenta' => 1.6, 'normale' => 1, 'veloce' => 0.6 );
	$v   = pdz_conf_get( 'vivace_velocita', 'normale' );
	return isset( $map[ $v ] ) ? $map[ $v ] : 1;
}

/**
 * Aspetto dei pulsanti nel tema Vivace.
 */
function pdz_conf_bottoni() {
	return array(
		'sfumato' => 'Sfumato — la sfumatura si sposta al passaggio del mouse',
		'pieno'   => 'Tinta unita — solo il colore di accento',
	);
}
