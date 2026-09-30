<?php
/**
 * Schema in uso e logica dei percorsi.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Domande attualmente in uso (opzione se presente, altrimenti predefinite).
 */
function pdz_conf_schema() {
	$saved = get_option( 'pdz_conf_schema' );
	$schema = ( is_array( $saved ) && $saved ) ? $saved : pdz_conf_default_schema();
	return apply_filters( 'pdz_conf_schema', $schema );
}

function pdz_conf_save_schema( $schema ) {
	return update_option( 'pdz_conf_schema', $schema );
}

/**
 * Un singolo step per id.
 */
function pdz_conf_get_step( $id ) {
	foreach ( pdz_conf_schema() as $step ) {
		if ( $step['id'] === $id ) {
			return $step;
		}
	}
	return null;
}

/**
 * Fase dello step: intro | percorso | finale | contatti.
 */
function pdz_conf_step_phase( $step ) {
	if ( ! empty( $step['phase'] ) ) {
		return $step['phase'];
	}
	return in_array( '*', $step['paths'], true ) ? 'intro' : 'percorso';
}

/**
 * Percorso risultante dalle risposte.
 */
function pdz_conf_resolve_path( $answers ) {
	$map  = pdz_conf_path_map();
	$obj  = isset( $answers['obiettivo'] ) ? $answers['obiettivo'] : '';
	$path = isset( $map[ $obj ] ) ? $map[ $obj ] : 'G';

	// G3: se l'utente chiede il percorso dettagliato, si aggancia ad A o B.
	if ( 'G' === $path && isset( $answers['passaggio_g'] ) && 'dettagliato' === $answers['passaggio_g'] ) {
		$sit = isset( $answers['situazione_g'] ) ? $answers['situazione_g'] : '';
		$pri = isset( $answers['priorita_g'] ) ? $answers['priorita_g'] : '';
		if ( in_array( $sit, array( 'gas', 'piu_soluzioni' ), true ) || 'elettrica' === $pri ) {
			return 'G+B';
		}
		return 'G+A';
	}

	return $path;
}

function pdz_conf_active_paths( $path ) {
	if ( 'G+A' === $path ) {
		return array( 'G', 'A' );
	}
	if ( 'G+B' === $path ) {
		return array( 'G', 'B' );
	}
	return array( $path );
}

/**
 * Un campo e' visibile date le risposte correnti?
 */
function pdz_conf_field_visible( $field, $answers ) {
	if ( empty( $field['show_if'] ) || empty( $field['show_if']['field'] ) ) {
		return true;
	}
	$rule = $field['show_if'];
	$val  = isset( $answers[ $rule['field'] ] ) ? $answers[ $rule['field'] ] : null;
	$in   = isset( $rule['in'] ) ? (array) $rule['in'] : array();

	if ( is_array( $val ) ) {
		return (bool) array_intersect( $val, $in );
	}
	return in_array( $val, $in, true );
}

/**
 * Step visibili, nell'ordine di compilazione:
 * comuni iniziali -> percorso (G prima di A/B se agganciato) -> finali -> contatti.
 */
function pdz_conf_visible_steps( $answers ) {
	$active   = pdz_conf_active_paths( pdz_conf_resolve_path( $answers ) );
	$intro    = array();
	$percorso = array();
	$finale   = array();
	$contatti = array();

	foreach ( pdz_conf_schema() as $step ) {
		$phase = pdz_conf_step_phase( $step );

		if ( 'intro' === $phase ) {
			$intro[] = $step;
			continue;
		}
		if ( 'contatti' === $phase ) {
			$contatti[] = $step;
			continue;
		}
		if ( ! array_intersect( $step['paths'], $active ) ) {
			continue;
		}
		if ( 'finale' === $phase ) {
			$finale[] = $step;
			continue;
		}
		$percorso[] = $step;
	}

	$ordinati = array();
	foreach ( $active as $letter ) {
		foreach ( $percorso as $step ) {
			if ( in_array( $letter, $step['paths'], true ) ) {
				$ordinati[] = $step;
			}
		}
	}

	return array_merge( $intro, $ordinati, $finale, $contatti );
}

/**
 * Regione a partire dalla sigla di provincia.
 */
function pdz_conf_regione( $provincia ) {
	$prov = pdz_conf_province();
	return isset( $prov[ $provincia ] ) ? $prov[ $provincia ] : '';
}

function pdz_conf_is_lombardia( $answers ) {
	return 'Lombardia' === pdz_conf_regione( isset( $answers['provincia'] ) ? $answers['provincia'] : '' );
}

/**
 * Da valore tecnico a etichetta leggibile.
 */
function pdz_conf_value_label( $field, $value ) {
	if ( is_array( $value ) ) {
		$out = array();
		foreach ( $value as $v ) {
			$out[] = isset( $field['options'][ $v ] ) ? $field['options'][ $v ] : $v;
		}
		return implode( ', ', $out );
	}
	if ( 'consent' === $field['type'] ) {
		return 'Accettata';
	}
	return isset( $field['options'][ $value ] ) ? $field['options'][ $value ] : $value;
}

/**
 * Tutti i campi dello schema, utile per i menu a tendina dell'editor.
 */
function pdz_conf_all_fields() {
	$out = array();
	foreach ( pdz_conf_schema() as $step ) {
		foreach ( $step['fields'] as $field ) {
			$out[ $field['name'] ] = $field;
		}
	}
	return $out;
}
