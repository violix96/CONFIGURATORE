<?php
/**
 * Archivio richieste: tabella dedicata, niente custom post type.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function pdz_conf_table() {
	global $wpdb;
	return $wpdb->prefix . 'pdz_conf_leads';
}

function pdz_conf_create_table() {
	global $wpdb;

	$table   = pdz_conf_table();
	$charset = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE {$table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		created_at datetime NOT NULL,
		status varchar(20) NOT NULL DEFAULT 'nuovo',
		nome varchar(190) NOT NULL DEFAULT '',
		email varchar(190) NOT NULL DEFAULT '',
		telefono varchar(60) NOT NULL DEFAULT '',
		comune varchar(120) NOT NULL DEFAULT '',
		provincia varchar(5) NOT NULL DEFAULT '',
		regione varchar(60) NOT NULL DEFAULT '',
		cap varchar(10) NOT NULL DEFAULT '',
		percorso varchar(10) NOT NULL DEFAULT '',
		punteggio smallint(6) NOT NULL DEFAULT 0,
		classe varchar(20) NOT NULL DEFAULT '',
		risposte longtext NULL,
		allegati longtext NULL,
		riepilogo longtext NULL,
		note text NULL,
		trashed_at datetime NULL,
		mail_status varchar(20) NOT NULL DEFAULT '',
		mail_error text NULL,
		ip varchar(60) NOT NULL DEFAULT '',
		PRIMARY KEY  (id),
		KEY punteggio (punteggio),
		KEY created_at (created_at),
		KEY status (status),
		KEY trashed_at (trashed_at)
	) {$charset};";

	$sessions = $wpdb->prefix . 'pdz_conf_sessions';

	$sql2 = "CREATE TABLE {$sessions} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		chiave varchar(40) NOT NULL DEFAULT '',
		created_at datetime NOT NULL,
		updated_at datetime NOT NULL,
		step_id varchar(60) NOT NULL DEFAULT '',
		indice smallint(6) NOT NULL DEFAULT 0,
		totale smallint(6) NOT NULL DEFAULT 0,
		percorso varchar(10) NOT NULL DEFAULT '',
		secondi int(11) NOT NULL DEFAULT 0,
		completata tinyint(1) NOT NULL DEFAULT 0,
		lead_id bigint(20) unsigned NOT NULL DEFAULT 0,
		PRIMARY KEY  (id),
		UNIQUE KEY chiave (chiave),
		KEY completata (completata),
		KEY created_at (created_at)
	) {$charset};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
	dbDelta( $sql2 );
}

/**
 * Inserisce una richiesta. Restituisce l'id.
 */
function pdz_conf_insert_lead( $data ) {
	global $wpdb;

	$wpdb->insert(
		pdz_conf_table(),
		array(
			'created_at' => current_time( 'mysql' ),
			'status'     => 'nuovo',
			'nome'       => $data['nome'],
			'email'      => $data['email'],
			'telefono'   => $data['telefono'],
			'comune'     => $data['comune'],
			'provincia'  => $data['provincia'],
			'regione'    => $data['regione'],
			'cap'        => $data['cap'],
			'percorso'   => $data['percorso'],
			'punteggio'  => (int) $data['punteggio'],
			'classe'     => $data['classe'],
			'risposte'   => wp_json_encode( $data['risposte'] ),
			'allegati'   => wp_json_encode( $data['allegati'] ),
			'riepilogo'  => $data['riepilogo'],
			'ip'         => $data['ip'],
		),
		array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s' )
	);

	return (int) $wpdb->insert_id;
}

function pdz_conf_get_lead( $id ) {
	global $wpdb;
	$table = pdz_conf_table();
	return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
}

function pdz_conf_delete_lead( $id ) {
	global $wpdb;
	return $wpdb->delete( pdz_conf_table(), array( 'id' => (int) $id ), array( '%d' ) );
}

function pdz_conf_update_lead( $id, $fields ) {
	global $wpdb;
	$formats = array_fill( 0, count( $fields ), '%s' );
	return $wpdb->update( pdz_conf_table(), $fields, array( 'id' => (int) $id ), $formats, array( '%d' ) );
}

/**
 * Elenco filtrato per la schermata richieste.
 */
function pdz_conf_query_leads( $args = array() ) {
	global $wpdb;

	$args = wp_parse_args(
		$args,
		array(
			'search'   => '',
			'classe'   => '',
			'percorso' => '',
			'status'   => '',
			'cestino'  => false,
			'orderby'  => 'created_at',
			'order'    => 'DESC',
			'per_page' => 20,
			'paged'    => 1,
		)
	);

	$table = pdz_conf_table();
	$where = array( $args['cestino'] ? 'trashed_at IS NOT NULL' : 'trashed_at IS NULL' );
	$prep  = array();

	if ( $args['search'] ) {
		$like    = '%' . $wpdb->esc_like( $args['search'] ) . '%';
		$where[] = '(nome LIKE %s OR email LIKE %s OR comune LIKE %s OR telefono LIKE %s)';
		array_push( $prep, $like, $like, $like, $like );
	}
	foreach ( array( 'classe', 'percorso', 'status' ) as $col ) {
		if ( $args[ $col ] ) {
			$where[]= "{$col} = %s";
			$prep[] = $args[ $col ];
		}
	}

	$allowed_orderby = array( 'created_at', 'punteggio', 'nome', 'comune', 'classe' );
	$orderby = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'created_at';
	$order   = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';

	$where_sql = implode( ' AND ', $where );
	$offset    = max( 0, ( (int) $args['paged'] - 1 ) * (int) $args['per_page'] );

	$count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
	$total     = $prep ? (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $prep ) ) : (int) $wpdb->get_var( $count_sql );

	$sql  = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
	$prep = array_merge( $prep, array( (int) $args['per_page'], $offset ) );
	$rows = $wpdb->get_results( $wpdb->prepare( $sql, $prep ) );

	return array( 'rows' => $rows, 'total' => $total );
}

/**
 * Conteggi per la barra di stato in alto.
 */
function pdz_conf_counts() {
	global $wpdb;
	$table = pdz_conf_table();
	$out   = array( 'totale' => 0, 'alta' => 0, 'media' => 0, 'bassa' => 0, 'nuovo' => 0 );

	$out['totale'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE trashed_at IS NULL" );
	$out['nuovo']  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status = 'nuovo' AND trashed_at IS NULL" );

	$rows = $wpdb->get_results( "SELECT classe, COUNT(*) AS n FROM {$table} WHERE trashed_at IS NULL GROUP BY classe" );
	foreach ( (array) $rows as $r ) {
		$out[ $r->classe ] = (int) $r->n;
	}

	return $out;
}

/**
 * Sposta una richiesta nel cestino.
 */
function pdz_conf_trash_lead( $id ) {
	global $wpdb;
	return $wpdb->update(
		pdz_conf_table(),
		array( 'trashed_at' => current_time( 'mysql' ) ),
		array( 'id' => (int) $id ),
		array( '%s' ),
		array( '%d' )
	);
}

/**
 * Riporta una richiesta dal cestino all'elenco.
 */
function pdz_conf_restore_lead( $id ) {
	global $wpdb;
	$table = pdz_conf_table();
	return $wpdb->query( $wpdb->prepare( "UPDATE {$table} SET trashed_at = NULL WHERE id = %d", (int) $id ) );
}

/**
 * Svuota il cestino, eliminando anche gli allegati dal disco.
 */
function pdz_conf_empty_trash() {
	global $wpdb;
	$table = pdz_conf_table();

	$rows = $wpdb->get_results( "SELECT id, allegati FROM {$table} WHERE trashed_at IS NOT NULL" );
	foreach ( (array) $rows as $row ) {
		pdz_conf_delete_lead_files( $row->allegati );
	}

	return $wpdb->query( "DELETE FROM {$table} WHERE trashed_at IS NOT NULL" );
}

/**
 * Rimuove dal disco gli allegati di una richiesta.
 */
function pdz_conf_delete_lead_files( $allegati_json ) {
	$allegati = json_decode( (string) $allegati_json, true );
	if ( ! is_array( $allegati ) ) {
		return;
	}
	$uploads = wp_upload_dir();
	foreach ( $allegati as $list ) {
		foreach ( (array) $list as $f ) {
			if ( empty( $f['file'] ) ) {
				continue;
			}
			// Sicurezza: cancella solo dentro la cartella uploads.
			if ( 0 === strpos( $f['file'], $uploads['basedir'] ) && file_exists( $f['file'] ) ) {
				wp_delete_file( $f['file'] );
			}
		}
	}
}

/**
 * Conteggio delle richieste nel cestino.
 */
function pdz_conf_trash_count() {
	global $wpdb;
	$table = pdz_conf_table();
	return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE trashed_at IS NOT NULL" );
}

/* -------------------------------------------------------------------------
 * Andamento delle compilazioni
 * ---------------------------------------------------------------------- */

function pdz_conf_sessions_table() {
	global $wpdb;
	return $wpdb->prefix . 'pdz_conf_sessions';
}

/**
 * Aggiorna (o crea) la traccia di una compilazione in corso.
 * Non salva risposte: solo a che punto è arrivata la persona.
 */
function pdz_conf_track_session( $chiave, $data ) {
	global $wpdb;

	$table = pdz_conf_sessions_table();
	$now   = current_time( 'mysql' );

	$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE chiave = %s", $chiave ) );

	$row = array(
		'updated_at' => $now,
		'step_id'    => $data['step_id'],
		'indice'     => (int) $data['indice'],
		'totale'     => (int) $data['totale'],
		'percorso'   => $data['percorso'],
		'secondi'    => (int) $data['secondi'],
	);

	if ( $exists ) {
		$wpdb->update( $table, $row, array( 'id' => (int) $exists ), array( '%s', '%s', '%d', '%d', '%s', '%d' ), array( '%d' ) );
		return (int) $exists;
	}

	$row['chiave']     = $chiave;
	$row['created_at'] = $now;

	$wpdb->insert( $table, $row, array( '%s', '%s', '%d', '%d', '%s', '%d', '%s', '%s' ) );

	return (int) $wpdb->insert_id;
}

/**
 * Segna una compilazione come arrivata in fondo.
 */
function pdz_conf_complete_session( $chiave, $lead_id ) {
	global $wpdb;

	if ( ! $chiave ) {
		return;
	}

	$table = pdz_conf_sessions_table();
	$wpdb->update(
		$table,
		array( 'completata' => 1, 'lead_id' => (int) $lead_id, 'updated_at' => current_time( 'mysql' ) ),
		array( 'chiave' => $chiave ),
		array( '%d', '%d', '%s' ),
		array( '%s' )
	);
}

/**
 * Numeri per la schermata Andamento, sugli ultimi N giorni.
 */
function pdz_conf_session_stats( $giorni = 30 ) {
	global $wpdb;

	$table = pdz_conf_sessions_table();
	$from  = gmdate( 'Y-m-d H:i:s', current_time( 'timestamp' ) - $giorni * DAY_IN_SECONDS );

	$avviate    = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE created_at >= %s", $from ) );
	$completate = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE completata = 1 AND created_at >= %s", $from ) );

	// Quante compilazioni si fermano a ciascun passaggio.
	$per_indice = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT indice, COUNT(*) AS totali, SUM(completata) AS complete
			 FROM {$table} WHERE created_at >= %s GROUP BY indice ORDER BY indice ASC",
			$from
		)
	);

	// Titolo più ricorrente per ogni passaggio, per dare un nome alle righe.
	$etichette = array();
	$rows      = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT indice, step_id, COUNT(*) AS n FROM {$table}
			 WHERE created_at >= %s AND step_id <> '' GROUP BY indice, step_id ORDER BY n DESC",
			$from
		)
	);
	foreach ( (array) $rows as $r ) {
		if ( ! isset( $etichette[ (int) $r->indice ] ) ) {
			$etichette[ (int) $r->indice ] = $r->step_id;
		}
	}

	// Imbuto: quante arrivano ad almeno il passaggio N e quante si fermano lì.
	$max    = (int) $wpdb->get_var( $wpdb->prepare( "SELECT MAX(indice) FROM {$table} WHERE created_at >= %s", $from ) );
	$ferme  = array();
	$totali = array();
	foreach ( (array) $per_indice as $r ) {
		$ferme[ (int) $r->indice ]  = (int) $r->totali - (int) $r->complete;
		$totali[ (int) $r->indice ] = (int) $r->totali;
	}

	$funnel = array();
	for ( $i = 1; $i <= $max; $i++ ) {
		$raggiunte = 0;
		foreach ( $totali as $indice => $n ) {
			if ( $indice >= $i ) {
				$raggiunte += $n;
			}
		}
		$funnel[] = array(
			'indice'    => $i,
			'step_id'   => isset( $etichette[ $i ] ) ? $etichette[ $i ] : '',
			'raggiunte' => $raggiunte,
			'ferme'     => isset( $ferme[ $i ] ) ? $ferme[ $i ] : 0,
		);
	}

	// Andamento nel tempo: per giorno sui periodi brevi, per mese oltre i tre mesi,
	// altrimenti con un anno di dati il grafico diventa illeggibile.
	$per_mese = ( $giorni > 92 );

	if ( $per_mese ) {
		$per_giorno = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT MIN(DATE(created_at)) AS giorno, COUNT(*) AS n, SUM(completata) AS ok
				 FROM {$table} WHERE created_at >= %s
				 GROUP BY YEAR(created_at), MONTH(created_at) ORDER BY giorno ASC",
				$from
			)
		);
	} else {
		$per_giorno = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DATE(created_at) AS giorno, COUNT(*) AS n, SUM(completata) AS ok
				 FROM {$table} WHERE created_at >= %s GROUP BY DATE(created_at) ORDER BY giorno ASC",
				$from
			)
		);
	}

	return array(
		'avviate'    => $avviate,
		'completate' => $completate,
		'tempo'      => (int) $wpdb->get_var( $wpdb->prepare( "SELECT AVG(secondi) FROM {$table} WHERE completata = 1 AND created_at >= %s", $from ) ),
		'tempo_ko'   => (int) $wpdb->get_var( $wpdb->prepare( "SELECT AVG(secondi) FROM {$table} WHERE completata = 0 AND created_at >= %s", $from ) ),
		'funnel'     => $funnel,
		'giorni'     => $per_giorno,
		'per_mese'   => $per_mese,
		'percorsi'   => $wpdb->get_results(
			$wpdb->prepare(
				"SELECT percorso, COUNT(*) AS n, SUM(completata) AS ok FROM {$table}
				 WHERE created_at >= %s AND percorso <> '' GROUP BY percorso ORDER BY n DESC",
				$from
			)
		),
	);
}

/**
 * Ultime compilazioni registrate, completate e non.
 */
function pdz_conf_recent_sessions( $limit = 40, $solo_abbandoni = false ) {
	global $wpdb;

	$table = pdz_conf_sessions_table();
	$where = $solo_abbandoni ? 'WHERE completata = 0' : '';

	return $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM {$table} {$where} ORDER BY updated_at DESC LIMIT %d", $limit )
	);
}

/**
 * Cancella le tracce più vecchie della soglia impostata.
 */
function pdz_conf_purge_sessions() {
	global $wpdb;

	$giorni = (int) pdz_conf_get( 'track_days', 180 );
	if ( $giorni < 1 ) {
		return;
	}

	$table = pdz_conf_sessions_table();
	$from  = gmdate( 'Y-m-d H:i:s', current_time( 'timestamp' ) - $giorni * DAY_IN_SECONDS );

	$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE created_at < %s", $from ) );
}

/**
 * Azzera tutte le tracce di andamento. Non tocca le richieste ricevute.
 */
function pdz_conf_reset_sessions() {
	global $wpdb;
	$table = pdz_conf_sessions_table();
	return $wpdb->query( "TRUNCATE TABLE {$table}" );
}
