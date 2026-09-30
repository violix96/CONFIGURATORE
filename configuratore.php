<?php
/**
 * Plugin Name: Configuratore
 * Description: Configuratore multi-step con percorsi dinamici: domande modificabili dalla bacheca, priorità delle richieste, archivio, email al gestore e all'utente, thank you page differenziate e statistiche di abbandono.
 * Version:     4.1.3
 * Author:      LU3G Agenzia Web
 * Text Domain: configuratore
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PDZ_CONF_VERSION', '4.1.3' );
define( 'PDZ_CONF_FILE', __FILE__ );
define( 'PDZ_CONF_DIR', plugin_dir_path( __FILE__ ) );
define( 'PDZ_CONF_URL', plugin_dir_url( __FILE__ ) );

/**
 * Aggiornamenti automatici da GitHub.
 *
 * Ogni sito controlla periodicamente l'ultima Release del repository e, se
 * la versione è più nuova di quella installata, mostra l'aggiornamento in
 * bacheca come per qualsiasi plugin. Lo zip scaricato è quello allegato
 * alla Release dalla GitHub Action, che ha la cartella con il nome giusto.
 *
 * Sta prima di qualsiasi altro caricamento: il plugin deve potersi
 * aggiornare anche se una dipendenza o una parte del plugin non è a posto.
 *
 * Repository privato: definire il token in wp-config.php, mai qui.
 *     define( 'LU3G_GITHUB_TOKEN', 'github_pat_...' );
 */
if ( file_exists( PDZ_CONF_DIR . 'vendor/plugin-update-checker/plugin-update-checker.php' ) ) {
	require_once PDZ_CONF_DIR . 'vendor/plugin-update-checker/plugin-update-checker.php';

	$pdz_conf_updater = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
		'https://github.com/violix96/CONFIGURATORE/',
		__FILE__,
		'configuratore'
	);

	// Solo lo zip allegato alla Release, non quello generato da GitHub, che
	// ha una cartella con il numero di versione nel nome.
	$pdz_conf_updater->getVcsApi()->enableReleaseAssets( '/configuratore\.zip($|[?&#])/i' );

	if ( defined( 'LU3G_GITHUB_TOKEN' ) && LU3G_GITHUB_TOKEN ) {
		$pdz_conf_updater->setAuthentication( LU3G_GITHUB_TOKEN );
	}
}

require_once PDZ_CONF_DIR . 'includes/capabilities.php';
require_once PDZ_CONF_DIR . 'includes/schema-defaults.php';
require_once PDZ_CONF_DIR . 'includes/schema.php';
require_once PDZ_CONF_DIR . 'includes/settings.php';
require_once PDZ_CONF_DIR . 'includes/db.php';
require_once PDZ_CONF_DIR . 'includes/frontend.php';
require_once PDZ_CONF_DIR . 'includes/submit.php';
require_once PDZ_CONF_DIR . 'includes/mail.php';

if ( is_admin() ) {
	require_once PDZ_CONF_DIR . 'admin/admin.php';
	require_once PDZ_CONF_DIR . 'admin/editor.php';
	require_once PDZ_CONF_DIR . 'admin/records.php';
	require_once PDZ_CONF_DIR . 'admin/stats.php';
}

register_activation_hook( __FILE__, 'pdz_conf_activate' );
register_deactivation_hook( __FILE__, function () {
	wp_clear_scheduled_hook( 'pdz_conf_daily' );
} );

add_action( 'pdz_conf_daily', 'pdz_conf_purge_sessions' );

function pdz_conf_activate() {
	pdz_conf_create_table();
	pdz_conf_grant_admin();

	$uploads = wp_upload_dir();
	pdz_conf_protect_uploads( $uploads['basedir'] . '/configuratore' );

	if ( false === get_option( 'pdz_conf_settings' ) ) {
		add_option( 'pdz_conf_settings', pdz_conf_default_settings() );
	}
	if ( false === get_option( 'pdz_conf_schema' ) ) {
		add_option( 'pdz_conf_schema', pdz_conf_default_schema() );
	}

	if ( ! wp_next_scheduled( 'pdz_conf_daily' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'pdz_conf_daily' );
	}

	update_option( 'pdz_conf_db_version', PDZ_CONF_VERSION );
}

/**
 * Aggiorna la tabella se il plugin viene aggiornato senza riattivazione.
 */
add_action( 'plugins_loaded', function () {
	if ( get_option( 'pdz_conf_db_version' ) !== PDZ_CONF_VERSION ) {
		pdz_conf_create_table();
		pdz_conf_migrate_classes();
		pdz_conf_migrate_caps();
		update_option( 'pdz_conf_db_version', PDZ_CONF_VERSION );
	}
} );

/**
 * Porta le richieste della prima versione alle nuove chiavi di priorita'.
 */
function pdz_conf_migrate_classes() {
	global $wpdb;
	$table = pdz_conf_table();

	foreach ( array( 'Caldo' => 'alta', 'Medio' => 'media', 'Freddo' => 'bassa' ) as $old => $new ) {
		$wpdb->update( $table, array( 'classe' => $new ), array( 'classe' => $old ), array( '%s' ), array( '%s' ) );
	}
}

/**
 * Link "Impostazioni" nella lista plugin.
 */
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $links ) {
	$url = admin_url( 'admin.php?page=pdz-conf-settings' );
	array_unshift( $links, '<a href="' . esc_url( $url ) . '">Impostazioni</a>' );
	return $links;
} );
