<?php
/**
 * Editor delle domande: tutto lo schema è modificabile dalla bacheca.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function pdz_conf_field_types() {
	return array(
		'radio'    => 'Scelta singola',
		'checkbox' => 'Scelta multipla',
		'select'   => 'Menu a tendina',
		'text'     => 'Testo breve',
		'textarea' => 'Testo lungo',
		'email'    => 'Email',
		'tel'      => 'Telefono',
		'file'     => 'Caricamento file',
		'consent'  => 'Consenso privacy',
	);
}

function pdz_conf_phases() {
	return array(
		'intro'    => 'Comune iniziale — mostrato a tutti, prima dei percorsi',
		'percorso' => 'Percorso — mostrato solo nei percorsi selezionati',
		'finale'   => 'Comune finale — dopo il percorso',
		'contatti' => 'Contatti — sempre ultimo',
	);
}

/* -------------------------------------------------------------------------
 * Pagina
 * ---------------------------------------------------------------------- */

function pdz_conf_render_questions_page() {
	if ( ! current_user_can( pdz_conf_cap( 'domande' ) ) ) {
		return;
	}

	$step_id = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : '';

	if ( $step_id ) {
		pdz_conf_render_step_editor( $step_id );
		return;
	}

	$schema = pdz_conf_schema();
	$labels = pdz_conf_path_labels();
	$phases = array(
		'intro'    => array( 'Comuni iniziali', 'Mostrati a tutti prima della diramazione.' ),
		'percorso' => array( 'Percorsi', 'Ogni step compare solo nei percorsi indicati.' ),
		'finale'   => array( 'Comuni finali', 'Dopo il percorso, prima dei contatti.' ),
		'contatti' => array( 'Contatti', 'Sempre l’ultimo step.' ),
	);

	$grouped = array( 'intro' => array(), 'percorso' => array(), 'finale' => array(), 'contatti' => array() );
	$domande = 0;
	foreach ( $schema as $step ) {
		$grouped[ pdz_conf_step_phase( $step ) ][] = $step;
		$domande += count( $step['fields'] );
	}
	?>
	<div class="wrap pdz-admin pdz-questions">
		<div class="pdz-head">
			<div>
				<h1>Domande</h1>
				<p class="pdz-head__sub">
					<?php echo (int) count( $schema ); ?> step · <?php echo (int) $domande; ?> domande ·
					trascina le schede per cambiare l’ordine di compilazione
				</p>
			</div>
			<div class="pdz-head__actions">
				<input type="search" class="pdz-search" placeholder="Cerca uno step…" data-pdz-search aria-label="Cerca uno step">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=pdz-conf-questions&step=nuovo' ) ); ?>" class="button button-primary">Aggiungi step</a>
			</div>
		</div>

		<?php if ( isset( $_GET['msg'] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( pdz_conf_editor_notice( sanitize_key( $_GET['msg'] ) ) ); ?></p></div>
		<?php endif; ?>

		<div class="pdz-savebar" data-pdz-savebar hidden>
			<span data-pdz-savebar-text>Ordine modificato</span>
			<button type="button" class="button button-primary" data-pdz-save-order>Salva ordine</button>
		</div>

		<div class="pdz-board" data-pdz-board
			data-nonce="<?php echo esc_attr( wp_create_nonce( 'pdz_conf_reorder' ) ); ?>"
			data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">

			<?php foreach ( $phases as $phase => $meta ) : ?>
				<section class="pdz-group" data-pdz-group="<?php echo esc_attr( $phase ); ?>">
					<header class="pdz-group__head">
						<h2><?php echo esc_html( $meta[0] ); ?></h2>
						<p><?php echo esc_html( $meta[1] ); ?></p>
					</header>

					<div class="pdz-group__list" data-pdz-sortable>
						<?php foreach ( $grouped[ $phase ] as $step ) : ?>
							<?php
							$edit   = admin_url( 'admin.php?page=pdz-conf-questions&step=' . rawurlencode( $step['id'] ) );
							$delete = wp_nonce_url( admin_url( 'admin-post.php?action=pdz_conf_delete_step&step=' . rawurlencode( $step['id'] ) ), 'pdz_conf_delete_step' );
							$req    = 0;
							$tipi   = array();
							foreach ( $step['fields'] as $f ) {
								if ( ! empty( $f['required'] ) ) {
									$req++;
								}
								$tipi[ $f['type'] ] = true;
							}
							?>
							<article class="pdz-stepcard" draggable="true" data-pdz-card data-id="<?php echo esc_attr( $step['id'] ); ?>"
								data-search="<?php echo esc_attr( strtolower( $step['title'] . ' ' . $step['id'] ) ); ?>">

								<span class="pdz-stepcard__grip" aria-hidden="true"></span>

								<div class="pdz-stepcard__body">
									<a class="pdz-stepcard__title" href="<?php echo esc_url( $edit ); ?>"><?php echo esc_html( $step['title'] ); ?></a>
									<div class="pdz-stepcard__meta">
										<code><?php echo esc_html( $step['id'] ); ?></code>
										<span><?php echo (int) count( $step['fields'] ); ?> domande</span>
										<?php if ( $req ) : ?><span><?php echo (int) $req; ?> obbligatorie</span><?php endif; ?>
									</div>

									<?php if ( 'percorso' === $phase || 'finale' === $phase ) : ?>
										<div class="pdz-chips">
											<?php foreach ( $step['paths'] as $pth ) : ?>
												<?php if ( '*' === $pth ) { continue; } ?>
												<span class="pdz-chip pdz-chip--<?php echo esc_attr( strtolower( $pth ) ); ?>" title="<?php echo esc_attr( isset( $labels[ $pth ] ) ? $labels[ $pth ] : '' ); ?>">
													<?php echo esc_html( $pth ); ?>
												</span>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>
								</div>

								<div class="pdz-stepcard__actions">
									<a href="<?php echo esc_url( $edit ); ?>" class="button button-small">Modifica</a>
									<a href="<?php echo esc_url( $delete ); ?>" class="pdz-danger"
									   data-pdz-confirm="Eliminare lo step «<?php echo esc_attr( $step['title'] ); ?>» e le sue domande?">Elimina</a>
								</div>
							</article>
						<?php endforeach; ?>

						<?php if ( ! $grouped[ $phase ] ) : ?>
							<p class="pdz-empty">Nessuno step in questa fase.</p>
						<?php endif; ?>
					</div>
				</section>
			<?php endforeach; ?>
		</div>

		<div class="pdz-box pdz-reset">
			<h2>Ripristino</h2>
			<p>Riporta tutte le domande alla configurazione di partenza. Le richieste già ricevute non vengono toccate.</p>
			<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=pdz_conf_reset_schema' ), 'pdz_conf_reset' ) ); ?>"
			   class="button" data-pdz-confirm="Ripristinare le domande predefinite? Le modifiche fatte andranno perse.">Ripristina domande predefinite</a>
		</div>
	</div>
	<?php
}

/**
 * Salvataggio dell'ordine via trascinamento.
 */
add_action( 'wp_ajax_pdz_conf_reorder_steps', function () {
	if ( ! current_user_can( pdz_conf_cap( 'domande' ) ) ) {
		wp_send_json_error( array( 'message' => 'Non autorizzato.' ), 403 );
	}
	check_ajax_referer( 'pdz_conf_reorder', 'nonce' );

	$order = isset( $_POST['order'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['order'] ) ) : array();
	if ( ! $order ) {
		wp_send_json_error( array( 'message' => 'Ordine non valido.' ), 400 );
	}

	$schema = pdz_conf_schema();
	$by_id  = array();
	foreach ( $schema as $step ) {
		$by_id[ $step['id'] ] = $step;
	}

	$new = array();
	foreach ( $order as $id ) {
		if ( isset( $by_id[ $id ] ) ) {
			$new[] = $by_id[ $id ];
			unset( $by_id[ $id ] );
		}
	}
	// Eventuali step non presenti nell'elenco ricevuto restano in coda.
	foreach ( $by_id as $step ) {
		$new[] = $step;
	}

	pdz_conf_save_schema( $new );

	wp_send_json_success( array( 'message' => 'Ordine salvato.' ) );
} );

function pdz_conf_editor_notice( $code ) {
	$map = array(
		'saved'   => 'Step salvato.',
		'deleted' => 'Step eliminato.',
		'reset'   => 'Domande riportate alla configurazione predefinita.',
		'moved'   => 'Ordine aggiornato.',
	);
	return isset( $map[ $code ] ) ? $map[ $code ] : '';
}

/* -------------------------------------------------------------------------
 * Editor del singolo step
 * ---------------------------------------------------------------------- */

function pdz_conf_render_step_editor( $step_id ) {
	$is_new = ( 'nuovo' === $step_id );
	$step   = $is_new
		? array( 'id' => '', 'title' => '', 'intro' => '', 'phase' => 'percorso', 'paths' => array( 'A' ), 'fields' => array() )
		: pdz_conf_get_step( $step_id );

	if ( ! $step ) {
		echo '<div class="wrap"><h1>Step non trovato</h1></div>';
		return;
	}

	$phase  = pdz_conf_step_phase( $step );
	$labels = pdz_conf_path_labels();
	?>
	<div class="wrap pdz-admin pdz-editor-wrap">
		<div class="pdz-head">
			<div>
				<h1><?php echo $is_new ? 'Nuovo step' : esc_html( $step['title'] ); ?></h1>
				<p class="pdz-head__sub"><a href="<?php echo esc_url( admin_url( 'admin.php?page=pdz-conf-questions' ) ); ?>">&larr; Tutte le domande</a></p>
			</div>
		</div>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="pdz-editor">
			<input type="hidden" name="action" value="pdz_conf_save_step">
			<input type="hidden" name="original_id" value="<?php echo esc_attr( $is_new ? '' : $step['id'] ); ?>">
			<?php wp_nonce_field( 'pdz_conf_save_step' ); ?>

			<div class="pdz-editor__grid">
				<div class="pdz-editor__main">
					<div class="pdz-panel">
						<h2>Domande di questo step</h2>
						<p class="pdz-panel__hint">Trascina le schede per cambiare l’ordine con cui compaiono nella schermata.</p>

						<div class="pdz-fields" data-pdz-fields>
							<?php foreach ( $step['fields'] as $i => $field ) : ?>
								<?php pdz_conf_render_field_block( $field, $i ); ?>
							<?php endforeach; ?>
						</div>

						<button type="button" class="button pdz-addfield" data-pdz-add-field>+ Aggiungi domanda</button>
					</div>
				</div>

				<aside class="pdz-editor__side">
					<div class="pdz-panel pdz-panel--sticky">
						<h2>Impostazioni dello step</h2>

						<p>
							<label for="pdz_title">Titolo</label>
							<input type="text" class="widefat" id="pdz_title" name="title" value="<?php echo esc_attr( $step['title'] ); ?>" required>
							<span class="pdz-desc">La domanda principale, mostrata in grande.</span>
						</p>

						<p>
							<label for="pdz_intro">Testo esplicativo</label>
							<textarea class="widefat" rows="3" id="pdz_intro" name="intro"><?php echo esc_textarea( isset( $step['intro'] ) ? $step['intro'] : '' ); ?></textarea>
						</p>

						<p>
							<label for="pdz_phase">Fase</label>
							<select id="pdz_phase" name="phase" class="widefat" data-pdz-phase>
								<?php foreach ( pdz_conf_phases() as $key => $label ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $phase, $key ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</p>

						<div data-pdz-paths<?php echo in_array( $phase, array( 'intro', 'contatti' ), true ) ? ' style="display:none;"' : ''; ?>>
							<label>Percorsi</label>
							<div class="pdz-pathpicker">
								<?php foreach ( array( 'A', 'B', 'C', 'D', 'E', 'F', 'G' ) as $p ) : ?>
									<label class="pdz-pathpicker__item">
										<input type="checkbox" name="paths[]" value="<?php echo esc_attr( $p ); ?>" <?php checked( in_array( $p, $step['paths'], true ) ); ?>>
										<span class="pdz-pathpicker__key"><?php echo esc_html( $p ); ?></span>
										<span class="pdz-pathpicker__label"><?php echo esc_html( $labels[ $p ] ); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
						</div>

						<div class="pdz-panel__foot">
							<button class="button button-primary button-large">Salva step</button>
						</div>
					</div>
				</aside>
			</div>
		</form>

		<script type="text/template" id="pdz-field-template">
			<?php pdz_conf_render_field_block( null, '__i__' ); ?>
		</script>
	</div>
	<?php
}

/**
 * Scheda di modifica di una singola domanda.
 */
function pdz_conf_render_field_block( $field, $index ) {
	$is_new = ! is_array( $field );

	$field = wp_parse_args(
		is_array( $field ) ? $field : array(),
		array(
			'name'        => '',
			'type'        => 'radio',
			'label'       => '',
			'options'     => array(),
			'scores'      => array(),
			'required'    => false,
			'hide_label'  => false,
			'hint'        => '',
			'placeholder' => '',
			'show_if'     => null,
		)
	);

	$lines = array();
	foreach ( (array) $field['options'] as $key => $label ) {
		$score   = isset( $field['scores'][ $key ] ) ? (int) $field['scores'][ $key ] : '';
		$lines[] = $key . '|' . $label . ( '' === $score ? '' : '|' . $score );
	}
	$options_text = implode( "\n", $lines );

	$cond_field = ! empty( $field['show_if']['field'] ) ? $field['show_if']['field'] : '';
	$cond_in    = ! empty( $field['show_if']['in'] ) ? implode( ', ', (array) $field['show_if']['in'] ) : '';
	$types      = pdz_conf_field_types();
	$n          = 'fields[' . $index . ']';
	?>
	<article class="pdz-q<?php echo $is_new ? ' is-open' : ''; ?>" data-pdz-field draggable="true">
		<header class="pdz-q__head" data-pdz-toggle>
			<span class="pdz-q__grip" aria-hidden="true"></span>
			<span class="pdz-q__title" data-pdz-qtitle><?php echo esc_html( $field['label'] ? $field['label'] : 'Nuova domanda' ); ?></span>
			<span class="pdz-q__type" data-pdz-qtype><?php echo esc_html( isset( $types[ $field['type'] ] ) ? $types[ $field['type'] ] : $field['type'] ); ?></span>
			<?php if ( ! empty( $field['required'] ) ) : ?><span class="pdz-q__req">obbligatoria</span><?php endif; ?>
			<?php if ( $cond_field ) : ?><span class="pdz-q__cond" title="Domanda condizionale">condizionale</span><?php endif; ?>
			<button type="button" class="pdz-q__caret" aria-label="Apri o chiudi"></button>
		</header>

		<div class="pdz-q__body">
			<div class="pdz-q__row">
				<p class="pdz-q__col">
					<label>Etichetta</label>
					<input type="text" class="widefat" name="<?php echo esc_attr( $n ); ?>[label]" value="<?php echo esc_attr( $field['label'] ); ?>" data-pdz-label>
				</p>
				<p class="pdz-q__col pdz-q__col--small">
					<label>Tipo</label>
					<select class="widefat" name="<?php echo esc_attr( $n ); ?>[type]" data-pdz-type>
						<?php foreach ( $types as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $field['type'], $key ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
				<p class="pdz-q__col pdz-q__col--small">
					<label>Nome tecnico</label>
					<input type="text" class="widefat code" name="<?php echo esc_attr( $n ); ?>[name]" value="<?php echo esc_attr( $field['name'] ); ?>" placeholder="automatico">
				</p>
			</div>

			<p data-pdz-options>
				<label>Risposte</label>
				<textarea class="widefat code" rows="7" name="<?php echo esc_attr( $n ); ?>[options]" placeholder="chiave|Etichetta mostrata|punti"><?php echo esc_textarea( $options_text ); ?></textarea>
				<span class="pdz-desc">Una per riga: <code>chiave|Etichetta|punti</code>. I punti sono facoltativi e alimentano la priorità della richiesta. Cambiare una chiave scollega le risposte già ricevute.</span>
			</p>

			<div class="pdz-q__row">
				<p class="pdz-q__col">
					<label>Testo di aiuto</label>
					<input type="text" class="widefat" name="<?php echo esc_attr( $n ); ?>[hint]" value="<?php echo esc_attr( $field['hint'] ); ?>">
				</p>
				<p class="pdz-q__col">
					<label>Placeholder</label>
					<input type="text" class="widefat" name="<?php echo esc_attr( $n ); ?>[placeholder]" value="<?php echo esc_attr( $field['placeholder'] ); ?>">
				</p>
			</div>

			<div class="pdz-q__cond-box">
				<strong>Mostra questa domanda solo se…</strong>
				<div class="pdz-q__row">
					<p class="pdz-q__col">
						<label>il campo</label>
						<input type="text" class="widefat code" name="<?php echo esc_attr( $n ); ?>[cond_field]" value="<?php echo esc_attr( $cond_field ); ?>" placeholder="nome tecnico">
					</p>
					<p class="pdz-q__col">
						<label>vale una di queste risposte</label>
						<input type="text" class="widefat code" name="<?php echo esc_attr( $n ); ?>[cond_in]" value="<?php echo esc_attr( $cond_in ); ?>" placeholder="chiave1, chiave2">
					</p>
				</div>
			</div>

			<div class="pdz-q__foot">
				<label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[required]" value="1" <?php checked( ! empty( $field['required'] ) ); ?> data-pdz-required> Obbligatoria</label>
				<label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[hide_label]" value="1" <?php checked( ! empty( $field['hide_label'] ) ); ?>> Nascondi l’etichetta</label>
				<button type="button" class="pdz-danger pdz-q__remove" data-pdz-remove-field>Rimuovi domanda</button>
			</div>
		</div>
	</article>
	<?php
}

/* -------------------------------------------------------------------------
 * Salvataggio
 * ---------------------------------------------------------------------- */

add_action( 'admin_post_pdz_conf_save_step', 'pdz_conf_save_step' );

function pdz_conf_save_step() {
	if ( ! current_user_can( pdz_conf_cap( 'domande' ) ) ) {
		wp_die( 'Non autorizzato.' );
	}
	check_admin_referer( 'pdz_conf_save_step' );

	$original = isset( $_POST['original_id'] ) ? sanitize_key( wp_unslash( $_POST['original_id'] ) ) : '';
	$title    = sanitize_text_field( wp_unslash( $_POST['title'] ) );
	$intro    = sanitize_textarea_field( wp_unslash( $_POST['intro'] ) );
	$phase    = isset( $_POST['phase'] ) ? sanitize_key( $_POST['phase'] ) : 'percorso';
	$phase    = array_key_exists( $phase, pdz_conf_phases() ) ? $phase : 'percorso';

	if ( in_array( $phase, array( 'intro', 'contatti' ), true ) ) {
		$paths = array( '*' );
	} else {
		$paths = isset( $_POST['paths'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['paths'] ) ) : array();
		$paths = array_values( array_intersect( $paths, array( 'A', 'B', 'C', 'D', 'E', 'F', 'G' ) ) );
		if ( ! $paths ) {
			$paths = array( 'A' );
		}
	}

	$fields = array();
	$raw    = isset( $_POST['fields'] ) ? (array) wp_unslash( $_POST['fields'] ) : array();

	foreach ( $raw as $item ) {
		$label = isset( $item['label'] ) ? sanitize_text_field( $item['label'] ) : '';
		$name  = isset( $item['name'] ) ? sanitize_key( $item['name'] ) : '';

		if ( ! $label && ! $name ) {
			continue;
		}
		if ( ! $name ) {
			$name = substr( sanitize_key( str_replace( ' ', '_', remove_accents( $label ) ) ), 0, 40 );
		}

		$type = isset( $item['type'] ) ? sanitize_key( $item['type'] ) : 'radio';
		$type = array_key_exists( $type, pdz_conf_field_types() ) ? $type : 'radio';

		list( $options, $scores ) = pdz_conf_parse_options( isset( $item['options'] ) ? $item['options'] : '' );

		$show_if    = null;
		$cond_field = isset( $item['cond_field'] ) ? sanitize_key( $item['cond_field'] ) : '';
		$cond_in    = isset( $item['cond_in'] ) ? sanitize_text_field( $item['cond_in'] ) : '';
		if ( $cond_field && $cond_in ) {
			$show_if = array(
				'field' => $cond_field,
				'in'    => array_values( array_filter( array_map( 'trim', explode( ',', $cond_in ) ) ) ),
			);
		}

		$fields[] = array(
			'name'        => $name,
			'type'        => $type,
			'label'       => $label,
			'options'     => $options,
			'scores'      => $scores,
			'required'    => ! empty( $item['required'] ),
			'hide_label'  => ! empty( $item['hide_label'] ),
			'hint'        => isset( $item['hint'] ) ? sanitize_text_field( $item['hint'] ) : '',
			'placeholder' => isset( $item['placeholder'] ) ? sanitize_text_field( $item['placeholder'] ) : '',
			'show_if'     => $show_if,
		);
	}

	$schema = pdz_conf_schema();
	$id     = $original ? $original : pdz_conf_unique_step_id( $title, $schema );

	$step = array(
		'id'     => $id,
		'phase'  => $phase,
		'paths'  => $paths,
		'title'  => $title,
		'intro'  => $intro,
		'fields' => $fields,
	);

	$found = false;
	foreach ( $schema as $i => $existing ) {
		if ( $existing['id'] === $id ) {
			$schema[ $i ] = $step;
			$found        = true;
			break;
		}
	}
	if ( ! $found ) {
		$schema[] = $step;
	}

	pdz_conf_save_schema( $schema );

	wp_safe_redirect( admin_url( 'admin.php?page=pdz-conf-questions&msg=saved' ) );
	exit;
}

/**
 * Converte il testo delle opzioni in due array: etichette e punteggi.
 */
function pdz_conf_parse_options( $text ) {
	$options = array();
	$scores  = array();

	foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}

		$parts = array_map( 'trim', explode( '|', $line ) );

		if ( count( $parts ) === 1 ) {
			$label = $parts[0];
			$key   = substr( sanitize_key( str_replace( ' ', '_', remove_accents( $label ) ) ), 0, 40 );
		} else {
			$key   = sanitize_key( $parts[0] );
			$label = $parts[1];
		}

		if ( ! $key || '' === $label ) {
			continue;
		}

		$options[ $key ] = sanitize_text_field( $label );

		if ( isset( $parts[2] ) && '' !== $parts[2] ) {
			$scores[ $key ] = (int) $parts[2];
		}
	}

	return array( $options, $scores );
}

function pdz_conf_unique_step_id( $title, $schema ) {
	$base = substr( sanitize_key( str_replace( ' ', '_', remove_accents( $title ) ) ), 0, 30 );
	$base = $base ? $base : 'step';
	$ids  = wp_list_pluck( $schema, 'id' );

	$id = $base;
	$n  = 2;
	while ( in_array( $id, $ids, true ) ) {
		$id = $base . '_' . $n;
		$n++;
	}
	return $id;
}

/* -------------------------------------------------------------------------
 * Ordine, eliminazione, ripristino
 * ---------------------------------------------------------------------- */

add_action( 'admin_post_pdz_conf_move_step', function () {
	if ( ! current_user_can( pdz_conf_cap( 'domande' ) ) ) {
		wp_die( 'Non autorizzato.' );
	}
	check_admin_referer( 'pdz_conf_move' );

	$id     = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : '';
	$dir    = ( isset( $_GET['dir'] ) && 'up' === $_GET['dir'] ) ? -1 : 1;
	$schema = pdz_conf_schema();

	foreach ( $schema as $i => $step ) {
		if ( $step['id'] !== $id ) {
			continue;
		}
		$j = $i + $dir;
		if ( $j >= 0 && $j < count( $schema ) ) {
			$tmp          = $schema[ $j ];
			$schema[ $j ] = $schema[ $i ];
			$schema[ $i ] = $tmp;
			pdz_conf_save_schema( $schema );
		}
		break;
	}

	wp_safe_redirect( admin_url( 'admin.php?page=pdz-conf-questions&msg=moved' ) );
	exit;
} );

add_action( 'admin_post_pdz_conf_delete_step', function () {
	if ( ! current_user_can( pdz_conf_cap( 'domande' ) ) ) {
		wp_die( 'Non autorizzato.' );
	}
	check_admin_referer( 'pdz_conf_delete_step' );

	$id     = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : '';
	$schema = array_values(
		array_filter(
			pdz_conf_schema(),
			function ( $step ) use ( $id ) {
				return $step['id'] !== $id;
			}
		)
	);

	pdz_conf_save_schema( $schema );

	wp_safe_redirect( admin_url( 'admin.php?page=pdz-conf-questions&msg=deleted' ) );
	exit;
} );

add_action( 'admin_post_pdz_conf_reset_schema', function () {
	if ( ! current_user_can( pdz_conf_cap( 'domande' ) ) ) {
		wp_die( 'Non autorizzato.' );
	}
	check_admin_referer( 'pdz_conf_reset' );

	pdz_conf_save_schema( pdz_conf_default_schema() );

	wp_safe_redirect( admin_url( 'admin.php?page=pdz-conf-questions&msg=reset' ) );
	exit;
} );
