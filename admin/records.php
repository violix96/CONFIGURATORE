<?php
/**
 * Schermata delle richieste ricevute.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function pdz_conf_statuses() {
	return array(
		'nuovo'    => 'Nuova',
		'lavorato' => 'In lavorazione',
		'chiuso'   => 'Chiusa',
	);
}

function pdz_conf_status_label( $status ) {
	$map = pdz_conf_statuses();
	return isset( $map[ $status ] ) ? $map[ $status ] : $status;
}

function pdz_conf_render_records_page() {
	if ( ! current_user_can( pdz_conf_cap() ) ) {
		return;
	}

	if ( isset( $_GET['lead'] ) ) {
		pdz_conf_render_lead_detail( (int) $_GET['lead'] );
		return;
	}

	$cestino = ( isset( $_GET['vista'] ) && 'cestino' === $_GET['vista'] );

	$args = array(
		'search'   => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
		'classe'   => isset( $_GET['classe'] ) ? sanitize_key( $_GET['classe'] ) : '',
		'percorso' => isset( $_GET['percorso'] ) ? sanitize_text_field( wp_unslash( $_GET['percorso'] ) ) : '',
		'status'   => isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '',
		'orderby'  => isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'created_at',
		'order'    => isset( $_GET['order'] ) ? sanitize_key( $_GET['order'] ) : 'DESC',
		'paged'    => isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1,
		'per_page' => 20,
		'cestino'  => $cestino,
	);

	$result   = pdz_conf_query_leads( $args );
	$counts   = pdz_conf_counts();
	$in_trash = pdz_conf_trash_count();
	$labels   = pdz_conf_path_labels();
	$pages    = (int) ceil( $result['total'] / $args['per_page'] );
	$base     = admin_url( 'admin.php?page=pdz-conf-records' );

	$export = wp_nonce_url(
		add_query_arg(
			array_merge(
				array( 'action' => 'pdz_conf_export' ),
				array_filter( array( 's' => $args['search'], 'classe' => $args['classe'], 'percorso' => $args['percorso'], 'status' => $args['status'] ) )
			),
			admin_url( 'admin-post.php' )
		),
		'pdz_conf_export'
	);

	$keep = array_filter(
		array(
			'page'     => 'pdz-conf-records',
			'vista'    => $cestino ? 'cestino' : '',
			's'        => $args['search'],
			'percorso' => $args['percorso'],
			'status'   => $args['status'],
			'orderby'  => $args['orderby'],
			'order'    => $args['order'],
		)
	);
	?>
	<div class="wrap pdz-admin pdz-requests">
		<div class="pdz-head">
			<div>
				<h1><?php echo $cestino ? 'Cestino' : 'Richieste'; ?></h1>
				<p class="pdz-head__sub">
					<?php echo (int) $result['total']; ?> risultati<?php echo $args['search'] ? ' per «' . esc_html( $args['search'] ) . '»' : ''; ?>
				</p>
			</div>
			<div class="pdz-head__actions">
				<?php if ( ! $cestino && $in_trash ) : ?>
					<a href="<?php echo esc_url( add_query_arg( 'vista', 'cestino', $base ) ); ?>" class="button">Cestino (<?php echo (int) $in_trash; ?>)</a>
				<?php endif; ?>
				<?php if ( $cestino ) : ?>
					<a href="<?php echo esc_url( $base ); ?>" class="button">Torna all’elenco</a>
					<?php if ( $in_trash ) : ?>
						<a class="button pdz-danger-btn"
						   href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=pdz_conf_empty_trash' ), 'pdz_conf_empty_trash' ) ); ?>"
						   data-pdz-confirm="Eliminare definitivamente tutte le richieste nel cestino, allegati compresi?">Svuota cestino</a>
					<?php endif; ?>
				<?php endif; ?>
				<a href="<?php echo esc_url( $export ); ?>" class="button button-primary">Esporta CSV</a>
			</div>
		</div>

		<?php pdz_conf_admin_notice(); ?>

		<?php if ( ! $cestino ) : ?>
			<div class="pdz-cards">
				<div class="pdz-card"><span class="pdz-card__n"><?php echo (int) $counts['totale']; ?></span> richieste</div>
				<div class="pdz-card pdz-card--nuovo"><span class="pdz-card__n"><?php echo (int) $counts['nuovo']; ?></span> da lavorare</div>
				<div class="pdz-card pdz-card--alta"><span class="pdz-card__n"><?php echo (int) $counts['alta']; ?></span> <?php echo esc_html( pdz_conf_class_label( 'alta' ) ); ?></div>
				<div class="pdz-card pdz-card--media"><span class="pdz-card__n"><?php echo (int) $counts['media']; ?></span> <?php echo esc_html( pdz_conf_class_label( 'media' ) ); ?></div>
				<div class="pdz-card pdz-card--bassa"><span class="pdz-card__n"><?php echo (int) $counts['bassa']; ?></span> <?php echo esc_html( pdz_conf_class_label( 'bassa' ) ); ?></div>
			</div>
		<?php endif; ?>

		<div class="pdz-toolbar">
			<div class="pdz-pills">
				<?php
				$classi = array( '' => 'Tutte le priorità', 'alta' => pdz_conf_class_label( 'alta' ), 'media' => pdz_conf_class_label( 'media' ), 'bassa' => pdz_conf_class_label( 'bassa' ) );
				foreach ( $classi as $key => $label ) :
					$url = add_query_arg( array_filter( array_merge( $keep, array( 'classe' => $key ) ) ), admin_url( 'admin.php' ) );
					?>
					<a class="pdz-pill<?php echo $args['classe'] === $key ? ' is-active' : ''; ?>" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
			</div>

			<form method="get" class="pdz-toolbar__form">
				<input type="hidden" name="page" value="pdz-conf-records">
				<?php if ( $cestino ) : ?><input type="hidden" name="vista" value="cestino"><?php endif; ?>
				<input type="hidden" name="classe" value="<?php echo esc_attr( $args['classe'] ); ?>">

				<input type="search" class="pdz-search" name="s" value="<?php echo esc_attr( $args['search'] ); ?>" placeholder="Nome, email, comune…">

				<select name="percorso">
					<option value="">Tutti i percorsi</option>
					<?php foreach ( $labels as $key => $label ) : ?>
						<?php if ( false !== strpos( $key, '+' ) ) { continue; } ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $args['percorso'], $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>

				<select name="status">
					<option value="">Tutti gli stati</option>
					<?php foreach ( pdz_conf_statuses() as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $args['status'], $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>

				<button class="button">Filtra</button>
			</form>
		</div>

		<div class="pdz-sortbar">
			<span>Ordina per</span>
			<?php
			$sorts = array( 'created_at' => 'Data', 'punteggio' => 'Priorità', 'nome' => 'Nome' );
			foreach ( $sorts as $col => $label ) :
				$is  = ( $args['orderby'] === $col );
				$dir = ( $is && 'ASC' === strtoupper( $args['order'] ) ) ? 'DESC' : 'ASC';
				$url = add_query_arg( array_filter( array_merge( $keep, array( 'classe' => $args['classe'], 'orderby' => $col, 'order' => $dir ) ) ), admin_url( 'admin.php' ) );
				?>
				<a class="pdz-sort<?php echo $is ? ' is-active' : ''; ?>" href="<?php echo esc_url( $url ); ?>">
					<?php echo esc_html( $label ); ?>
					<?php if ( $is ) : ?><i aria-hidden="true"><?php echo 'ASC' === strtoupper( $args['order'] ) ? '↑' : '↓'; ?></i><?php endif; ?>
				</a>
			<?php endforeach; ?>
		</div>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="pdz-bulkform" data-pdz-bulk>
			<input type="hidden" name="action" value="pdz_conf_bulk">
			<input type="hidden" name="vista" value="<?php echo $cestino ? 'cestino' : ''; ?>">
			<?php wp_nonce_field( 'pdz_conf_bulk' ); ?>

			<?php if ( $result['rows'] ) : ?>
				<div class="pdz-bulkbar">
					<label class="pdz-bulkbar__all">
						<input type="checkbox" data-pdz-checkall>
						<span>Seleziona tutte le richieste in questa pagina</span>
					</label>

					<div class="pdz-bulkbar__actions" data-pdz-bulkactions hidden>
						<span data-pdz-bulkcount>0 selezionate</span>
						<?php if ( $cestino ) : ?>
							<button class="button" name="operazione" value="ripristina">Ripristina</button>
							<button class="button pdz-danger-btn" name="operazione" value="elimina"
								data-pdz-confirm="Eliminare definitivamente le richieste selezionate e i loro allegati?">Elimina definitivamente</button>
						<?php else : ?>
							<button class="button" name="operazione" value="lavorato">Segna come in lavorazione</button>
							<button class="button" name="operazione" value="chiuso">Segna come chiusa</button>
							<button class="button pdz-danger-btn" name="operazione" value="cestina">Sposta nel cestino</button>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>

		<div class="pdz-list">
			<?php if ( ! $result['rows'] ) : ?>
				<p class="pdz-empty"><?php echo $cestino ? 'Il cestino è vuoto.' : 'Nessuna richiesta trovata con questi filtri.'; ?></p>
			<?php endif; ?>

			<?php foreach ( $result['rows'] as $row ) : ?>
				<?php
				$link    = add_query_arg( 'lead', (int) $row->id, $base );
				$key     = pdz_conf_class_key( $row->classe );
				$trash   = wp_nonce_url( admin_url( 'admin-post.php?action=pdz_conf_trash_lead&lead=' . (int) $row->id ), 'pdz_conf_trash_lead' );
				$restore = wp_nonce_url( admin_url( 'admin-post.php?action=pdz_conf_restore_lead&lead=' . (int) $row->id ), 'pdz_conf_restore_lead' );
				$delete  = wp_nonce_url( admin_url( 'admin-post.php?action=pdz_conf_delete_lead&lead=' . (int) $row->id ), 'pdz_conf_delete_lead' );
				?>
				<article class="pdz-lead pdz-lead--<?php echo esc_attr( $key ); ?><?php echo 'nuovo' === $row->status ? ' is-new' : ''; ?>">
					<label class="pdz-lead__check">
						<input type="checkbox" name="ids[]" value="<?php echo (int) $row->id; ?>" data-pdz-check
							aria-label="Seleziona la richiesta di <?php echo esc_attr( $row->nome ? $row->nome : 'anonimo' ); ?>">
					</label>

					<div class="pdz-lead__main">
						<a class="pdz-lead__name" href="<?php echo esc_url( $link ); ?>">
							<?php echo esc_html( $row->nome ? $row->nome : '(senza nome)' ); ?>
						</a>
						<div class="pdz-lead__meta">
							<span><?php echo esc_html( mysql2date( 'd/m/Y H:i', $row->created_at ) ); ?></span>
							<span><?php echo esc_html( trim( $row->comune . ' (' . $row->provincia . ')' ) ); ?></span>
							<span class="pdz-lead__path"><?php echo esc_html( pdz_conf_path_label( $row->percorso ) ); ?></span>
						</div>
					</div>

					<div class="pdz-lead__contacts">
						<a href="mailto:<?php echo esc_attr( $row->email ); ?>"><?php echo esc_html( $row->email ); ?></a>
						<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $row->telefono ) ); ?>"><?php echo esc_html( $row->telefono ); ?></a>
					</div>

					<div class="pdz-lead__score">
						<span class="pdz-badge pdz-badge--<?php echo esc_attr( $key ); ?>"><?php echo esc_html( pdz_conf_class_label( $key ) ); ?></span>
						<span class="pdz-meter"><span style="width:<?php echo (int) $row->punteggio; ?>%"></span></span>
						<span class="pdz-lead__pts"><?php echo (int) $row->punteggio; ?>/100</span>
					</div>

					<div class="pdz-lead__side">
						<span class="pdz-status pdz-status--<?php echo esc_attr( $row->status ); ?>"><?php echo esc_html( pdz_conf_status_label( $row->status ) ); ?></span>
						<?php if ( ! empty( $row->mail_status ) && 'ok' !== $row->mail_status ) : ?>
							<span class="pdz-status pdz-status--mail" title="<?php echo esc_attr( $row->mail_error ); ?>">
								<?php echo 'parziale' === $row->mail_status ? 'email parziale' : 'email non inviata'; ?>
							</span>
						<?php endif; ?>
						<div class="pdz-lead__actions">
							<?php if ( $cestino ) : ?>
								<a href="<?php echo esc_url( $restore ); ?>">Ripristina</a>
								<a href="<?php echo esc_url( $delete ); ?>" class="pdz-danger" data-pdz-confirm="Eliminare definitivamente questa richiesta e i suoi allegati?">Elimina</a>
							<?php else : ?>
								<a href="<?php echo esc_url( $link ); ?>">Apri</a>
								<a href="<?php echo esc_url( $trash ); ?>" class="pdz-danger">Cestina</a>
							<?php endif; ?>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
		</form>

		<?php if ( $pages > 1 ) : ?>
			<div class="pdz-pagination">
				<?php
				echo paginate_links(
					array(
						'base'      => add_query_arg( 'paged', '%#%' ),
						'format'    => '',
						'current'   => $args['paged'],
						'total'     => $pages,
						'prev_text' => '‹ Precedenti',
						'next_text' => 'Successive ›',
					)
				);
				?>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

function pdz_conf_admin_notice() {
	if ( ! isset( $_GET['msg'] ) ) {
		return;
	}
	if ( 'bulk' === sanitize_key( $_GET['msg'] ) ) {
		$n  = isset( $_GET['n'] ) ? (int) $_GET['n'] : 0;
		$op = isset( $_GET['op'] ) ? sanitize_key( $_GET['op'] ) : '';

		$verbi = array(
			'cestina'    => 'spostate nel cestino',
			'ripristina' => 'ripristinate',
			'elimina'    => 'eliminate definitivamente',
			'lavorato'   => 'segnate come in lavorazione',
			'chiuso'     => 'segnate come chiuse',
		);

		$verbo = isset( $verbi[ $op ] ) ? $verbi[ $op ] : 'aggiornate';
		$testo = $n
			? sprintf( '%d %s %s.', $n, 1 === $n ? 'richiesta' : 'richieste', $verbo )
			: 'Nessuna richiesta selezionata.';

		echo '<div class="notice notice-' . ( $n ? 'success' : 'warning' ) . ' is-dismissible"><p>' . esc_html( $testo ) . '</p></div>';
		return;
	}

	$map = array(
		'trashed'  => 'Richiesta spostata nel cestino.',
		'restored' => 'Richiesta ripristinata.',
		'deleted'  => 'Richiesta eliminata definitivamente.',
		'emptied'  => 'Cestino svuotato.',
		'saved'    => 'Richiesta aggiornata.',
	);
	$code = sanitize_key( $_GET['msg'] );
	if ( isset( $map[ $code ] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $map[ $code ] ) . '</p></div>';
	}
}

function pdz_conf_sort_link( $column, $label, $args ) {
	$order = ( $args['orderby'] === $column && 'ASC' === strtoupper( $args['order'] ) ) ? 'DESC' : 'ASC';
	$url   = add_query_arg( array( 'orderby' => $column, 'order' => $order ) );
	echo '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
}

/* -------------------------------------------------------------------------
 * Dettaglio
 * ---------------------------------------------------------------------- */

function pdz_conf_render_lead_detail( $id ) {
	$lead = pdz_conf_get_lead( $id );

	if ( ! $lead ) {
		echo '<div class="wrap"><h1>Richiesta non trovata</h1></div>';
		return;
	}

	$allegati  = json_decode( $lead->allegati, true );
	$key       = pdz_conf_class_key( $lead->classe );
	$in_trash  = ! empty( $lead->trashed_at );
	$trash_url = wp_nonce_url( admin_url( 'admin-post.php?action=pdz_conf_trash_lead&lead=' . (int) $lead->id ), 'pdz_conf_trash_lead' );
	$restore   = wp_nonce_url( admin_url( 'admin-post.php?action=pdz_conf_restore_lead&lead=' . (int) $lead->id ), 'pdz_conf_restore_lead' );
	$delete    = wp_nonce_url( admin_url( 'admin-post.php?action=pdz_conf_delete_lead&lead=' . (int) $lead->id ), 'pdz_conf_delete_lead' );
	?>
	<div class="wrap pdz-admin pdz-detail">
		<h1><?php echo esc_html( $lead->nome ? $lead->nome : 'Richiesta #' . $lead->id ); ?></h1>
		<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=pdz-conf-records' ) ); ?>">&larr; Torna alle richieste</a></p>

		<?php pdz_conf_admin_notice(); ?>

		<?php if ( ! empty( $lead->mail_status ) && 'ok' !== $lead->mail_status ) : ?>
			<div class="notice notice-error">
				<p>
					<strong>Le email di questa richiesta non sono partite correttamente.</strong>
					<?php echo $lead->mail_error ? esc_html( $lead->mail_error ) : ''; ?>
					I dati sono comunque salvati qui: ricontatta la persona a mano e verifica la configurazione SMTP.
				</p>
			</div>
		<?php endif; ?>

		<?php if ( $in_trash ) : ?>
			<div class="notice notice-warning">
				<p>Questa richiesta è nel cestino. <a href="<?php echo esc_url( $restore ); ?>">Ripristinala</a> per rimetterla in elenco.</p>
			</div>
		<?php endif; ?>

		<div class="pdz-detail__grid">
			<div class="pdz-detail__main">
				<div class="pdz-box"><?php echo wp_kses_post( $lead->riepilogo ); ?></div>
			</div>

			<div class="pdz-detail__side">
				<div class="pdz-box">
					<h2>Valutazione</h2>
					<p class="pdz-score">
						<span class="pdz-badge pdz-badge--<?php echo esc_attr( $key ); ?>"><?php echo esc_html( pdz_conf_class_label( $key ) ); ?> · <?php echo (int) $lead->punteggio; ?>/100</span>
					</p>
					<ul class="pdz-meta">
						<li><span>Ricevuta</span> <?php echo esc_html( mysql2date( 'd/m/Y H:i', $lead->created_at ) ); ?></li>
						<li><span>Percorso</span> <?php echo esc_html( pdz_conf_path_label( $lead->percorso ) ); ?></li>
						<li><span>Località</span> <?php echo esc_html( $lead->comune . ' (' . $lead->provincia . ') ' . $lead->cap ); ?></li>
						<li><span>Regione</span> <?php echo esc_html( $lead->regione ? $lead->regione : '—' ); ?></li>
						<li><span>Email</span> <a href="mailto:<?php echo esc_attr( $lead->email ); ?>"><?php echo esc_html( $lead->email ); ?></a></li>
						<li><span>Telefono</span> <a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $lead->telefono ) ); ?>"><?php echo esc_html( $lead->telefono ); ?></a></li>
						<?php if ( $lead->ip ) : ?>
							<li><span>Indirizzo IP</span> <code class="pdz-ip" title="Registrato a fini di sicurezza"><?php echo esc_html( $lead->ip ); ?></code></li>
						<?php endif; ?>
					</ul>
				</div>

				<?php if ( $allegati ) : ?>
					<div class="pdz-box">
						<h2>Allegati</h2>
						<ul class="pdz-files">
							<?php foreach ( $allegati as $list ) : ?>
								<?php foreach ( $list as $f ) : ?>
									<li><a href="<?php echo esc_url( $f['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $f['name'] ); ?></a></li>
								<?php endforeach; ?>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<div class="pdz-box">
					<h2>Gestione</h2>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="pdz_conf_update_lead">
						<input type="hidden" name="lead" value="<?php echo (int) $lead->id; ?>">
						<?php wp_nonce_field( 'pdz_conf_update_lead' ); ?>

						<p>
							<label for="pdz_status">Stato</label><br>
							<select name="status" id="pdz_status">
								<?php foreach ( pdz_conf_statuses() as $skey => $label ) : ?>
									<option value="<?php echo esc_attr( $skey ); ?>" <?php selected( $lead->status, $skey ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</p>
						<p>
							<label for="pdz_note">Note interne</label><br>
							<textarea name="note" id="pdz_note" rows="5" class="large-text"><?php echo esc_textarea( (string) $lead->note ); ?></textarea>
						</p>
						<p>
							<button class="button button-primary">Salva</button>
							<?php if ( $in_trash ) : ?>
								<a href="<?php echo esc_url( $delete ); ?>" class="button-link pdz-danger" data-pdz-confirm="Eliminare definitivamente questa richiesta e i suoi allegati?">Elimina definitivamente</a>
							<?php else : ?>
								<a href="<?php echo esc_url( $trash_url ); ?>" class="button-link pdz-danger">Sposta nel cestino</a>
							<?php endif; ?>
						</p>
					</form>
				</div>
			</div>
		</div>
	</div>
	<?php
}

/* -------------------------------------------------------------------------
 * Azioni
 * ---------------------------------------------------------------------- */

add_action( 'admin_post_pdz_conf_update_lead', function () {
	if ( ! current_user_can( pdz_conf_cap() ) ) {
		wp_die( 'Non autorizzato.' );
	}
	check_admin_referer( 'pdz_conf_update_lead' );

	$id     = isset( $_POST['lead'] ) ? (int) $_POST['lead'] : 0;
	$status = isset( $_POST['status'] ) ? sanitize_key( $_POST['status'] ) : 'nuovo';
	$status = array_key_exists( $status, pdz_conf_statuses() ) ? $status : 'nuovo';
	$note   = isset( $_POST['note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['note'] ) ) : '';

	pdz_conf_update_lead( $id, array( 'status' => $status, 'note' => $note ) );

	wp_safe_redirect( admin_url( 'admin.php?page=pdz-conf-records&lead=' . $id . '&msg=saved' ) );
	exit;
} );

add_action( 'admin_post_pdz_conf_trash_lead', function () {
	if ( ! current_user_can( pdz_conf_cap() ) ) {
		wp_die( 'Non autorizzato.' );
	}
	check_admin_referer( 'pdz_conf_trash_lead' );

	pdz_conf_trash_lead( isset( $_GET['lead'] ) ? (int) $_GET['lead'] : 0 );

	wp_safe_redirect( admin_url( 'admin.php?page=pdz-conf-records&msg=trashed' ) );
	exit;
} );

add_action( 'admin_post_pdz_conf_restore_lead', function () {
	if ( ! current_user_can( pdz_conf_cap() ) ) {
		wp_die( 'Non autorizzato.' );
	}
	check_admin_referer( 'pdz_conf_restore_lead' );

	pdz_conf_restore_lead( isset( $_GET['lead'] ) ? (int) $_GET['lead'] : 0 );

	wp_safe_redirect( admin_url( 'admin.php?page=pdz-conf-records&msg=restored' ) );
	exit;
} );

add_action( 'admin_post_pdz_conf_delete_lead', function () {
	if ( ! current_user_can( pdz_conf_cap() ) ) {
		wp_die( 'Non autorizzato.' );
	}
	check_admin_referer( 'pdz_conf_delete_lead' );

	$id   = isset( $_GET['lead'] ) ? (int) $_GET['lead'] : 0;
	$lead = pdz_conf_get_lead( $id );

	if ( $lead ) {
		pdz_conf_delete_lead_files( $lead->allegati );
		pdz_conf_delete_lead( $id );
	}

	wp_safe_redirect( admin_url( 'admin.php?page=pdz-conf-records&vista=cestino&msg=deleted' ) );
	exit;
} );

add_action( 'admin_post_pdz_conf_empty_trash', function () {
	if ( ! current_user_can( pdz_conf_cap() ) ) {
		wp_die( 'Non autorizzato.' );
	}
	check_admin_referer( 'pdz_conf_empty_trash' );

	pdz_conf_empty_trash();

	wp_safe_redirect( admin_url( 'admin.php?page=pdz-conf-records&msg=emptied' ) );
	exit;
} );

add_action( 'admin_post_pdz_conf_bulk', function () {
	if ( ! current_user_can( pdz_conf_cap() ) ) {
		wp_die( 'Non autorizzato.' );
	}
	check_admin_referer( 'pdz_conf_bulk' );

	$ids        = isset( $_POST['ids'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['ids'] ) ) : array();
	$operazione = isset( $_POST['operazione'] ) ? sanitize_key( wp_unslash( $_POST['operazione'] ) ) : '';
	$cestino    = ! empty( $_POST['vista'] );
	$fatte      = 0;

	foreach ( array_filter( $ids ) as $id ) {
		switch ( $operazione ) {
			case 'cestina':
				pdz_conf_trash_lead( $id );
				$fatte++;
				break;

			case 'ripristina':
				pdz_conf_restore_lead( $id );
				$fatte++;
				break;

			case 'elimina':
				$lead = pdz_conf_get_lead( $id );
				if ( $lead ) {
					pdz_conf_delete_lead_files( $lead->allegati );
					pdz_conf_delete_lead( $id );
					$fatte++;
				}
				break;

			case 'lavorato':
			case 'chiuso':
				pdz_conf_update_lead( $id, array( 'status' => $operazione ) );
				$fatte++;
				break;
		}
	}

	wp_safe_redirect(
		add_query_arg(
			array_filter(
				array(
					'page'  => 'pdz-conf-records',
					'vista' => $cestino ? 'cestino' : '',
					'msg'   => 'bulk',
					'n'     => $fatte,
					'op'    => $operazione,
				)
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
} );

/* -------------------------------------------------------------------------
 * Esportazione CSV
 * ---------------------------------------------------------------------- */

add_action( 'admin_post_pdz_conf_export', function () {
	if ( ! current_user_can( pdz_conf_cap() ) ) {
		wp_die( 'Non autorizzato.' );
	}
	check_admin_referer( 'pdz_conf_export' );

	$result = pdz_conf_query_leads(
		array(
			'search'   => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
			'classe'   => isset( $_GET['classe'] ) ? sanitize_key( $_GET['classe'] ) : '',
			'percorso' => isset( $_GET['percorso'] ) ? sanitize_text_field( wp_unslash( $_GET['percorso'] ) ) : '',
			'status'   => isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '',
			'per_page' => 5000,
			'paged'    => 1,
		)
	);

	$fields = pdz_conf_all_fields();

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=richieste-configuratore-' . gmdate( 'Y-m-d' ) . '.csv' );

	$out = fopen( 'php://output', 'w' );
	fprintf( $out, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) ); // BOM per Excel

	$header = array( 'ID', 'Data', 'Stato', 'Nome', 'Email', 'Telefono', 'Comune', 'Provincia', 'Regione', 'CAP', 'Percorso', 'Punteggio', 'Priorità' );
	foreach ( $fields as $field ) {
		$header[] = $field['label'];
	}
	fputcsv( $out, $header );

	foreach ( $result['rows'] as $row ) {
		$answers = json_decode( $row->risposte, true );
		$answers = is_array( $answers ) ? $answers : array();

		$line = array(
			$row->id,
			mysql2date( 'd/m/Y H:i', $row->created_at ),
			pdz_conf_status_label( $row->status ),
			$row->nome,
			$row->email,
			$row->telefono,
			$row->comune,
			$row->provincia,
			$row->regione,
			$row->cap,
			pdz_conf_path_label( $row->percorso ),
			$row->punteggio,
			pdz_conf_class_label( $row->classe ),
		);

		foreach ( $fields as $name => $field ) {
			$line[] = isset( $answers[ $name ] ) ? pdz_conf_value_label( $field, $answers[ $name ] ) : '';
		}

		fputcsv( $out, $line );
	}

	fclose( $out );
	exit;
} );
