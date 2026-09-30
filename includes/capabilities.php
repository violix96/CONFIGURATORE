<?php
/**
 * Permessi: chi può vedere le richieste e chi può cambiare la configurazione.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Il permesso del plugin. La sezione Domande resta ai soli amministratori.
 */
function pdz_conf_caps() {
	return array(
		'pdz_conf_access' => array(
			'Accesso al configuratore',
			'Richieste, cestino, esportazione, andamento e impostazioni. La sezione Domande resta riservata agli amministratori.',
		),
	);
}

/**
 * Permessi non più usati, rimossi in fase di aggiornamento.
 */
function pdz_conf_caps_obsolete() {
	return array( 'pdz_conf_leads', 'pdz_conf_settings' );
}

/**
 * Slug del ruolo dedicato.
 */
function pdz_conf_role_slug() {
	return 'pdz_conf_gestore';
}

/**
 * Chi amministra il sito ha sempre entrambi i permessi.
 */
function pdz_conf_grant_admin() {
	$admin = get_role( 'administrator' );
	if ( ! $admin ) {
		return;
	}
	foreach ( array_keys( pdz_conf_caps() ) as $cap ) {
		$admin->add_cap( $cap );
	}
}

/**
 * Crea il ruolo "Gestore configuratore": entra in bacheca e vede solo il plugin.
 */
function pdz_conf_create_role() {
	if ( get_role( pdz_conf_role_slug() ) ) {
		return false;
	}

	add_role(
		pdz_conf_role_slug(),
		'Gestore configuratore',
		array(
			'read'            => true,
			'pdz_conf_access' => true,
		)
	);

	return true;
}

function pdz_conf_delete_role() {
	if ( ! get_role( pdz_conf_role_slug() ) ) {
		return false;
	}
	remove_role( pdz_conf_role_slug() );
	return true;
}

/**
 * Ruoli a cui si possono assegnare i permessi (l'amministratore è escluso: li ha sempre).
 */
function pdz_conf_assignable_roles() {
	$out = array();

	foreach ( wp_roles()->roles as $slug => $role ) {
		if ( 'administrator' === $slug ) {
			continue;
		}
		$out[ $slug ] = translate_user_role( $role['name'] );
	}

	return $out;
}

/**
 * Applica la tabella dei permessi arrivata dalle impostazioni.
 *
 * @param array $matrice [ ruolo => [ cap => 1 ] ]
 */
function pdz_conf_sync_roles( $matrice ) {
	foreach ( array_keys( pdz_conf_assignable_roles() ) as $slug ) {
		$role = get_role( $slug );
		if ( ! $role ) {
			continue;
		}

		foreach ( array_keys( pdz_conf_caps() ) as $cap ) {
			$vuole = ! empty( $matrice[ $slug ][ $cap ] );

			if ( $vuole ) {
				$role->add_cap( $cap );
			} else {
				$role->remove_cap( $cap );
			}
		}
	}

	pdz_conf_grant_admin();
}

/**
 * Permesso richiesto da una schermata.
 * 'domande' è l'unica riservata a chi amministra il sito.
 */
function pdz_conf_cap( $ambito = 'access' ) {
	return ( 'domande' === $ambito ) ? 'manage_options' : 'pdz_conf_access';
}

/**
 * Porta i ruoli dai vecchi due permessi a quello unico.
 */
function pdz_conf_migrate_caps() {
	foreach ( wp_roles()->roles as $slug => $dati ) {
		$role = get_role( $slug );
		if ( ! $role ) {
			continue;
		}

		$aveva = false;
		foreach ( pdz_conf_caps_obsolete() as $vecchio ) {
			if ( $role->has_cap( $vecchio ) ) {
				$aveva = true;
			}
			$role->remove_cap( $vecchio );
		}

		if ( $aveva && 'administrator' !== $slug ) {
			$role->add_cap( 'pdz_conf_access' );
		}
	}

	pdz_conf_grant_admin();
}
