<?php
/**
 * Schermata Andamento: quante compilazioni iniziano, dove e quando si fermano.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function pdz_conf_render_stats_page() {
	if ( ! current_user_can( pdz_conf_cap() ) ) {
		return;
	}

	$giorni = isset( $_GET['giorni'] ) ? max( 1, (int) $_GET['giorni'] ) : 30;
	$solo   = ( isset( $_GET['solo'] ) && 'abbandoni' === $_GET['solo'] );

	$stats    = pdz_conf_session_stats( $giorni );
	$sessioni = pdz_conf_recent_sessions( 40, $solo );

	$avviate    = (int) $stats['avviate'];
	$completate = (int) $stats['completate'];
	$tasso      = $avviate ? round( $completate / $avviate * 100 ) : 0;

	$titoli = array();
	foreach ( pdz_conf_schema() as $step ) {
		$titoli[ $step['id'] ] = $step['title'];
	}

	$base = admin_url( 'admin.php?page=pdz-conf-stats' );

	// Il giorno con più abbandoni e il passaggio peggiore, per la riga di sintesi.
	$peggiore = null;
	foreach ( (array) $stats['funnel'] as $riga ) {
		if ( ! $peggiore || $riga['ferme'] > $peggiore['ferme'] ) {
			$peggiore = $riga;
		}
	}

	$max_giorno = 0;
	foreach ( (array) $stats['giorni'] as $g ) {
		$max_giorno = max( $max_giorno, (int) $g->n );
	}
	?>
	<div class="wrap pdz-admin pdz-stats">
		<div class="pdz-head">
			<div>
				<h1>Andamento</h1>
				<p class="pdz-head__sub">Quante persone iniziano il configuratore, dove e quando si fermano.</p>
			</div>
			<div class="pdz-head__actions">
				<?php foreach ( array( 7 => '7 giorni', 30 => '30 giorni', 90 => '90 giorni', 365 => '12 mesi' ) as $g => $label ) : ?>
					<a class="button<?php echo $giorni === $g ? ' button-primary' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'giorni', $g, $base ) ); ?>"><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
			</div>
		</div>

		<?php if ( isset( $_GET['msg'] ) && 'reset' === sanitize_key( $_GET['msg'] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p>Statistiche azzerate.</p></div>
		<?php endif; ?>

		<?php if ( ! pdz_conf_get( 'track_enabled' ) ) : ?>
			<div class="notice notice-warning"><p>Il tracciamento è disattivato in Impostazioni → Aspetto e antispam: questi numeri non si aggiornano.</p></div>
		<?php endif; ?>

		<div class="pdz-cards">
			<div class="pdz-card"><span class="pdz-card__n"><?php echo (int) $avviate; ?></span> compilazioni iniziate</div>
			<div class="pdz-card pdz-card--alta"><span class="pdz-card__n"><?php echo (int) $completate; ?></span> arrivate all&rsquo;invio</div>
			<div class="pdz-card pdz-card--nuovo"><span class="pdz-card__n"><?php echo (int) $tasso; ?>%</span> tasso di completamento</div>
			<div class="pdz-card"><span class="pdz-card__n"><?php echo $stats['tempo'] ? esc_html( pdz_conf_durata( $stats['tempo'] ) ) : '&mdash;'; ?></span> per completare</div>
			<div class="pdz-card"><span class="pdz-card__n"><?php echo $stats['tempo_ko'] ? esc_html( pdz_conf_durata( $stats['tempo_ko'] ) ) : '&mdash;'; ?></span> prima di abbandonare</div>
		</div>

		<?php if ( $peggiore && $peggiore['ferme'] > 0 ) : ?>
			<p class="pdz-insight">
				Il punto critico è il <strong>passaggio <?php echo (int) $peggiore['indice']; ?></strong><?php
				if ( $peggiore['step_id'] && isset( $titoli[ $peggiore['step_id'] ] ) ) {
					echo ', &ldquo;' . esc_html( $titoli[ $peggiore['step_id'] ] ) . '&rdquo;';
				}
				?>: lì si ferma <?php echo (int) $peggiore['ferme']; ?> <?php echo 1 === (int) $peggiore['ferme'] ? 'compilazione' : 'compilazioni'; ?>
				su <?php echo (int) $avviate; ?>.
			</p>
		<?php endif; ?>

		<div class="pdz-panel">
			<h2>Quando</h2>
			<p class="pdz-panel__hint">Compilazioni iniziate <?php echo empty( $stats['per_mese'] ) ? 'giorno per giorno' : 'mese per mese'; ?>; la parte piena è quella arrivata all&rsquo;invio.</p>

			<?php if ( ! $stats['giorni'] ) : ?>
				<p class="pdz-empty">Nessun dato in questo periodo.</p>
			<?php else : ?>
				<div class="pdz-days">
					<?php foreach ( $stats['giorni'] as $g ) : ?>
						<?php
						$n  = (int) $g->n;
						$ok = (int) $g->ok;
						$h  = $max_giorno ? max( 4, round( $n / $max_giorno * 100 ) ) : 4;
						$ho = $n ? round( $ok / $n * 100 ) : 0;
						?>
						<div class="pdz-day" title="<?php echo esc_attr( mysql2date( empty( $stats['per_mese'] ) ? 'd/m/Y' : 'F Y', $g->giorno ) . ' — ' . $n . ' iniziate, ' . $ok . ' inviate' ); ?>">
							<div class="pdz-day__bar" style="height:<?php echo (int) $h; ?>%">
								<span style="height:<?php echo (int) $ho; ?>%"></span>
							</div>
							<div class="pdz-day__label"><?php echo esc_html( mysql2date( empty( $stats['per_mese'] ) ? 'd/m' : 'M', $g->giorno ) ); ?></div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="pdz-panel" style="margin-top:20px;">
			<h2>Dove si fermano</h2>
			<p class="pdz-panel__hint">Per ogni passaggio: quante compilazioni ci arrivano e quante si interrompono proprio lì.</p>

			<?php if ( ! $stats['funnel'] ) : ?>
				<p class="pdz-empty">Ancora nessuna compilazione registrata.</p>
			<?php else : ?>
				<div class="pdz-funnel">
					<?php foreach ( $stats['funnel'] as $riga ) : ?>
						<?php
						$titolo = ( $riga['step_id'] && isset( $titoli[ $riga['step_id'] ] ) ) ? $titoli[ $riga['step_id'] ] : 'Passaggio ' . $riga['indice'];
						$quota  = $avviate ? round( $riga['raggiunte'] / $avviate * 100 ) : 0;
						$persi  = $riga['raggiunte'] ? round( $riga['ferme'] / $riga['raggiunte'] * 100 ) : 0;
						?>
						<div class="pdz-funnel__row<?php echo $riga['ferme'] ? ' has-drop' : ''; ?>">
							<div class="pdz-funnel__label">
								<strong><?php echo esc_html( $titolo ); ?></strong>
								<span>passaggio <?php echo (int) $riga['indice']; ?></span>
							</div>
							<div class="pdz-funnel__bar">
								<span style="width:<?php echo (int) $quota; ?>%"></span>
							</div>
							<div class="pdz-funnel__n">
								<?php echo (int) $riga['raggiunte']; ?>
								<em>
									<?php if ( $riga['ferme'] ) : ?>
										&minus;<?php echo (int) $riga['ferme']; ?> qui (<?php echo (int) $persi; ?>%)
									<?php else : ?>
										nessun abbandono
									<?php endif; ?>
								</em>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="pdz-panel" style="margin-top:20px;">
			<div class="pdz-panel__toolbar">
				<div>
					<h2>Ultime compilazioni</h2>
					<p class="pdz-panel__hint">Quando è iniziata, quando si è interrotta e a che punto era arrivata.</p>
				</div>
				<div class="pdz-pills">
					<a class="pdz-pill<?php echo $solo ? '' : ' is-active'; ?>" href="<?php echo esc_url( add_query_arg( 'giorni', $giorni, $base ) ); ?>">Tutte</a>
					<a class="pdz-pill<?php echo $solo ? ' is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( array( 'giorni' => $giorni, 'solo' => 'abbandoni' ), $base ) ); ?>">Solo abbandoni</a>
				</div>
			</div>

			<?php if ( ! $sessioni ) : ?>
				<p class="pdz-empty">Nessuna compilazione registrata.</p>
			<?php else : ?>
				<table class="pdz-table pdz-table--sessions">
					<thead>
						<tr>
							<th>Iniziata</th>
							<th>Ultima attività</th>
							<th>Arrivata a</th>
							<th>Percorso</th>
							<th>Durata</th>
							<th>Esito</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $sessioni as $riga ) : ?>
							<?php
							$titolo = isset( $titoli[ $riga->step_id ] ) ? $titoli[ $riga->step_id ] : $riga->step_id;
							$lead   = $riga->lead_id ? admin_url( 'admin.php?page=pdz-conf-records&lead=' . (int) $riga->lead_id ) : '';
							?>
							<tr>
								<td><?php echo esc_html( mysql2date( 'd/m/Y H:i', $riga->created_at ) ); ?></td>
								<td><?php echo esc_html( mysql2date( 'd/m/Y H:i', $riga->updated_at ) ); ?></td>
								<td>
									<strong><?php echo esc_html( $titolo ); ?></strong>
									<span class="pdz-muted"><?php echo (int) $riga->indice; ?> di <?php echo (int) $riga->totale; ?></span>
								</td>
								<td><?php echo $riga->percorso ? esc_html( pdz_conf_path_label( $riga->percorso ) ) : '&mdash;'; ?></td>
								<td><?php echo esc_html( pdz_conf_durata( $riga->secondi ) ); ?></td>
								<td>
									<?php if ( $riga->completata ) : ?>
										<span class="pdz-status pdz-status--nuovo">inviata</span>
										<?php if ( $lead ) : ?><a href="<?php echo esc_url( $lead ); ?>">apri</a><?php endif; ?>
									<?php else : ?>
										<span class="pdz-status pdz-status--mail">interrotta</span>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>

		<div class="pdz-panel" style="margin-top:20px;">
			<h2>Percorsi scelti</h2>
			<?php if ( ! $stats['percorsi'] ) : ?>
				<p class="pdz-empty">Ancora nessun dato.</p>
			<?php else : ?>
				<table class="pdz-table">
					<thead><tr><th>Percorso</th><th>Iniziate</th><th>Completate</th><th>Tasso</th></tr></thead>
					<tbody>
						<?php foreach ( $stats['percorsi'] as $riga ) : ?>
							<?php
							$n  = (int) $riga->n;
							$ok = (int) $riga->ok;
							?>
							<tr>
								<td><?php echo esc_html( pdz_conf_path_label( $riga->percorso ) ); ?></td>
								<td><?php echo (int) $n; ?></td>
								<td><?php echo (int) $ok; ?></td>
								<td><?php echo $n ? (int) round( $ok / $n * 100 ) : 0; ?>%</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>

		<div class="pdz-box pdz-reset" style="margin-top:24px;">
			<h2>Azzeramento</h2>
			<p>Cancella tutte le tracce di andamento e riparte da zero. Utile dopo le prove, prima di andare online. Le richieste ricevute e i loro dati non vengono toccati.</p>
			<p>
				<a class="button pdz-danger-btn"
				   href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=pdz_conf_reset_stats' ), 'pdz_conf_reset_stats' ) ); ?>"
				   data-pdz-confirm="Azzerare tutte le statistiche di andamento? L’operazione non è reversibile.">Azzera le statistiche</a>
			</p>
		</div>

		<p class="pdz-desc" style="margin-top:16px;max-width:64ch;">
			Vengono registrati solo il passaggio raggiunto, il percorso, gli orari e il tempo trascorso: nessuna risposta, nessun dato di contatto, nessun indirizzo IP.
			Le tracce più vecchie di <?php echo (int) pdz_conf_get( 'track_days', 180 ); ?> giorni vengono cancellate ogni notte.
		</p>
	</div>
	<?php
}

/**
 * Durata leggibile a partire dai secondi.
 */
function pdz_conf_durata( $secondi ) {
	$secondi = (int) $secondi;

	if ( $secondi < 60 ) {
		return $secondi . 's';
	}
	if ( $secondi < 3600 ) {
		return floor( $secondi / 60 ) . 'm ' . str_pad( $secondi % 60, 2, '0', STR_PAD_LEFT ) . 's';
	}
	return round( $secondi / 3600, 1 ) . 'h';
}

add_action( 'admin_post_pdz_conf_reset_stats', function () {
	if ( ! current_user_can( pdz_conf_cap() ) ) {
		wp_die( 'Non autorizzato.' );
	}
	check_admin_referer( 'pdz_conf_reset_stats' );

	pdz_conf_reset_sessions();

	wp_safe_redirect( admin_url( 'admin.php?page=pdz-conf-stats&msg=reset' ) );
	exit;
} );
