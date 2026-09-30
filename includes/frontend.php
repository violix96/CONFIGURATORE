<?php
/**
 * Shortcode [configuratore_pedrinazzi] e asset frontend.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode( 'configuratore', 'pdz_conf_shortcode' );
// Alias storico: le pagine già pubblicate continuano a funzionare.
add_shortcode( 'configuratore_pedrinazzi', 'pdz_conf_shortcode' );

function pdz_conf_shortcode( $atts ) {
	$s = pdz_conf_settings();

	$atts = shortcode_atts(
		array(
			'titolo' => $s['intro_title'],
			'cta'    => $s['intro_cta'],
		),
		$atts,
		'configuratore'
	);

	pdz_conf_enqueue_assets();

	$data = array(
		'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
		'nonce'            => wp_create_nonce( 'pdz_conf' ),
		'schema'           => pdz_conf_schema(),
		'pathMap'          => pdz_conf_path_map(),
		'pathLabels'       => pdz_conf_path_labels(),
		'province'         => pdz_conf_province(),
		'maxFiles'         => (int) $s['max_files'],
		'maxSize'          => pdz_conf_max_file_size(),
		'maxTotal'         => pdz_conf_max_total_upload(),
		'allowedExt'       => pdz_conf_allowed_ext(),
		'privacyText'      => $s['privacy_text'],
		'privacyUrl'       => $s['privacy_url'],
		'privacyLinkLabel' => $s['privacy_link_label'],
		'track'            => ! empty( $s['track_enabled'] ),
	);

	wp_add_inline_script( 'pdz-conf', 'window.PDZ_CONF = ' . wp_json_encode( $data ) . ';', 'before' );

	$intro_paragraphs = '';
	foreach ( preg_split( '/\n\s*\n/', (string) $s['intro_text'] ) as $par ) {
		$par = trim( $par );
		if ( $par ) {
			$intro_paragraphs .= '<p>' . esc_html( $par ) . '</p>';
		}
	}

	// Punti di apertura: "Titolo|Descrizione" uno per riga.
	$punti = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $s['intro_points'] ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$parts   = array_map( 'trim', explode( '|', $line, 2 ) );
		$punti[] = array( $parts[0], isset( $parts[1] ) ? $parts[1] : '' );
		if ( count( $punti ) >= 3 ) {
			break;
		}
	}

	ob_start();
	?>
	<?php $hero_style = pdz_conf_hero_style(); ?>
	<section class="pdz-conf pdz-conf--tema-<?php echo esc_attr( pdz_conf_tema() ); ?><?php echo 'nessuna' === $hero_style ? ' pdz-conf--novisual' : ''; ?><?php echo empty( $s['rail_counts'] ) ? ' pdz-conf--nocount' : ''; ?><?php echo ( 'vivace' === pdz_conf_tema() && empty( $s['vivace_aloni'] ) ) ? ' pdz-vivace--senza-aloni' : ''; ?><?php echo ( 'vivace' === pdz_conf_tema() && empty( $s['vivace_vetro'] ) ) ? ' pdz-vivace--senza-vetro' : ''; ?><?php echo ( 'vivace' === pdz_conf_tema() && 'pieno' === $s['vivace_bottoni'] ) ? ' pdz-vivace--bottoni-pieni' : ''; ?>" id="pdz-conf">
		<div class="pdz-hero" data-pdz-intro>
			<div class="pdz-hero__text">
				<?php if ( $s['intro_kicker'] ) : ?>
					<p class="pdz-conf__kicker"><span class="pdz-conf__dot" aria-hidden="true"></span><?php echo esc_html( $s['intro_kicker'] ); ?></p>
				<?php endif; ?>

				<h2 class="pdz-conf__title"><?php echo esc_html( $atts['titolo'] ); ?></h2>

				<div class="pdz-hero__lead"><?php echo $intro_paragraphs; // già passato da esc_html ?></div>

				<?php if ( $s['intro_note'] ) : ?>
					<p class="pdz-conf__note"><?php echo esc_html( $s['intro_note'] ); ?></p>
				<?php endif; ?>

				<div class="pdz-hero__cta">
					<button type="button" class="pdz-btn pdz-btn--primary pdz-btn--lg" data-pdz-start>
						<?php echo esc_html( $atts['cta'] ); ?>
						<span class="pdz-btn__arrow" aria-hidden="true"></span>
					</button>
					<?php if ( $s['intro_cta_note'] ) : ?>
						<span class="pdz-hero__ctanote"><?php echo esc_html( $s['intro_cta_note'] ); ?></span>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( 'nessuna' !== $hero_style ) : ?>
				<div class="pdz-hero__visual" aria-hidden="true"><?php echo pdz_conf_hero_svg( $hero_style ); ?></div>
			<?php endif; ?>
		</div>

		<?php if ( $punti ) : ?>
			<ol class="pdz-points" data-pdz-intro-points>
				<?php foreach ( $punti as $i => $punto ) : ?>
					<li class="pdz-point">
						<span class="pdz-point__n"><?php echo (int) ( $i + 1 ); ?></span>
						<div>
							<strong><?php echo esc_html( $punto[0] ); ?></strong>
							<?php if ( $punto[1] ) : ?><span><?php echo esc_html( $punto[1] ); ?></span><?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>

		<div class="pdz-conf__app" data-pdz-app hidden></div>
		<noscript><p>Per usare il configuratore è necessario attivare JavaScript.</p></noscript>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * Illustrazione di apertura. Restituisce l'SVG dello stile richiesto.
 */
function pdz_conf_hero_svg( $style = '' ) {
	$style = $style ? $style : pdz_conf_hero_style();

	switch ( $style ) {
		case 'schema':
			return pdz_conf_hero_schema();
		case 'pompa':
			return pdz_conf_hero_pompa();
		case 'onda':
			return pdz_conf_hero_onda();
		case 'resa':
			return pdz_conf_hero_resa();
		case 'rete':
			return pdz_conf_hero_rete();
		case 'emissione':
			return pdz_conf_hero_emissione();
		case 'nessuna':
			return '';
		default:
			return pdz_conf_hero_circuito();
	}
}




/**
 * Circuito chiuso: il fluido esce caldo dalla pompa, cede calore e rientra freddo.
 */
function pdz_conf_hero_circuito() {
	ob_start();
	?>
	<svg class="pdz-scene pdz-loop" viewBox="18 40 380 182" role="img" aria-hidden="true" focusable="false">
		<defs>
			<linearGradient id="pdz-hot" x1="60" y1="0" x2="300" y2="0" gradientUnits="userSpaceOnUse">
				<stop class="pdz-loop__cold-stop" offset="0"/>
				<stop class="pdz-loop__hot-stop" offset="0.55"/>
				<stop class="pdz-loop__hot-stop" offset="1"/>
			</linearGradient>
			<linearGradient id="pdz-cool" x1="360" y1="0" x2="120" y2="0" gradientUnits="userSpaceOnUse">
				<stop class="pdz-loop__hot-stop" offset="0"/>
				<stop class="pdz-loop__cold-stop" offset="0.55"/>
				<stop class="pdz-loop__cold-stop" offset="1"/>
			</linearGradient>
		</defs>

		<path class="pdz-loop__tube" d="M60 130a70 70 0 0 1 70-70h160a70 70 0 0 1 0 140H130a70 70 0 0 1-70-70z"/>
		<path class="pdz-loop__hot" d="M60 130a70 70 0 0 1 70-70h160a70 70 0 0 1 70 70"/>
		<path class="pdz-loop__cool" d="M360 130a70 70 0 0 1-70 70H130a70 70 0 0 1-70-70"/>
		<path class="pdz-loop__sheen" d="M60 130a70 70 0 0 1 70-70h160a70 70 0 0 1 0 140H130a70 70 0 0 1-70-70z"/>

		<g class="pdz-loop__exchanger">
			<rect x="330" y="100" width="60" height="60" rx="10"/>
			<path class="pdz-loop__fins" d="M344 118h32M344 130h32M344 142h32"/>
		</g>

		<circle class="pdz-loop__body" cx="60" cy="130" r="36"/>
		<circle class="pdz-loop__inner" cx="60" cy="130" r="24"/>
		<g class="pdz-loop__rotor">
			<path d="M60 111c10.5 0 19 8.5 19 19M60 149c-10.5 0-19-8.5-19-19"/>
		</g>
		<circle class="pdz-loop__hubdot" cx="60" cy="130" r="4.5"/>
	</svg>
	<?php
	return ob_get_clean();
}

/**
 * Schema d'impianto: captazione, macchina ed edificio collegati da un flusso.
 */
function pdz_conf_hero_schema() {
	ob_start();
	?>
	<svg class="pdz-scene pdz-schema" viewBox="0 0 460 340" role="img" aria-hidden="true" focusable="false">
		<defs>
			<pattern id="pdz-grid" width="20" height="20" patternUnits="userSpaceOnUse">
				<circle class="pdz-schema__gridpoint" cx="1" cy="1" r="1"/>
			</pattern>
		</defs>

		<rect class="pdz-schema__grid" x="10" y="10" width="440" height="320" fill="url(#pdz-grid)"/>

		<g class="pdz-schema__marks">
			<path d="M20 34V20h14M426 20h14v14M440 306v14h-14M34 320H20v-14"/>
		</g>

		<g>
			<rect class="pdz-schema__frame" x="50" y="50" width="130" height="82" rx="3"/>
			<path class="pdz-schema__detail" d="M50 77h130M50 105h130M93 50v82M137 50v82"/>
		</g>

		<g>
			<rect class="pdz-schema__frame" x="280" y="50" width="130" height="82" rx="3"/>
			<circle class="pdz-schema__hub" cx="345" cy="91" r="27"/>
			<circle class="pdz-schema__ring" cx="345" cy="91" r="19"/>
			<circle class="pdz-schema__core" cx="345" cy="91" r="3.5"/>
		</g>

		<g>
			<rect class="pdz-schema__frame" x="165" y="212" width="130" height="82" rx="3"/>
			<path class="pdz-schema__detail" d="M165 240h130M182 294v-32h24v32M218 294v-32h24v32M254 294v-32h24v32"/>
		</g>

		<g>
			<path class="pdz-schema__link" d="M180 91h100"/>
			<path class="pdz-schema__link pdz-schema__link--2" d="M345 132v40a12 12 0 0 1-12 12h-71a12 12 0 0 0-12 12v16"/>
			<path class="pdz-schema__link pdz-schema__link--3" d="M115 132v40a12 12 0 0 0 12 12h71a12 12 0 0 1 12 12v16"/>
		</g>

		<g class="pdz-schema__nodes">
			<circle cx="180" cy="91" r="4"/>
			<circle cx="280" cy="91" r="4"/>
			<circle cx="250" cy="212" r="4"/>
			<circle cx="210" cy="212" r="4"/>
		</g>

		<g class="pdz-schema__dim">
			<path d="M320 212v82M314 212h12M314 294h12"/>
		</g>
	</svg>
	<?php
	return ob_get_clean();
}

/**
 * Pompa e dosaggio: trasferimento del fluido con erogazione controllata.
 */
function pdz_conf_hero_pompa() {
	ob_start();
	?>
	<svg class="pdz-scene pdz-pump" viewBox="0 36 404 230" role="img" aria-hidden="true" focusable="false">
		<g class="pdz-pump__tube">
			<path d="M14 104h132"/>
			<path d="M254 104h74a16 16 0 0 1 16 16v40"/>
		</g>

		<g class="pdz-pump__fluid">
			<path class="pdz-pump__in" d="M14 104h132"/>
			<path class="pdz-pump__out" d="M254 104h74a16 16 0 0 1 16 16v40"/>
		</g>

		<g class="pdz-pump__sheen">
			<path d="M14 104h132"/>
			<path d="M254 104h74a16 16 0 0 1 16 16v40"/>
		</g>

		<circle class="pdz-pump__body" cx="200" cy="104" r="54"/>
		<circle class="pdz-pump__inner" cx="200" cy="104" r="38"/>
		<g class="pdz-pump__rotor">
			<path d="M200 74c17 0 30 13 30 30M200 134c-17 0-30-13-30-30"/>
		</g>
		<circle class="pdz-pump__hubdot" cx="200" cy="104" r="6"/>

		<g class="pdz-pump__drops">
			<circle class="pdz-pump__drop pdz-pump__drop--1" cx="344" cy="174" r="7"/>
			<circle class="pdz-pump__drop pdz-pump__drop--2" cx="344" cy="174" r="6"/>
		</g>

		<path class="pdz-pump__level" d="M304 228v12a6 6 0 0 0 6 6h68a6 6 0 0 0 6-6v-12z"/>
		<path class="pdz-pump__vessel" d="M300 198v42a10 10 0 0 0 10 10h68a10 10 0 0 0 10-10v-42"/>
	</svg>
	<?php
	return ob_get_clean();
}

/**
 * Onda termica: il fluido attraversa il sistema e cambia temperatura.
 */
function pdz_conf_hero_onda() {
	ob_start();
	?>
	<svg class="pdz-scene pdz-wave" viewBox="0 0 400 210" role="img" aria-hidden="true" focusable="false">
		<defs>
			<linearGradient id="pdz-wavegrad" x1="40" y1="0" x2="360" y2="0" gradientUnits="userSpaceOnUse">
				<stop class="pdz-wave__cold" offset="0"/>
				<stop class="pdz-wave__hot" offset="0.75"/>
				<stop class="pdz-wave__hot" offset="1"/>
			</linearGradient>
			<clipPath id="pdz-waveclip">
				<rect x="20" y="10" width="360" height="190"/>
			</clipPath>
		</defs>

		<g clip-path="url(#pdz-waveclip)">
			<?php for ( $i = 0; $i < 7; $i++ ) : ?>
				<g class="pdz-wave__row" style="animation-delay:<?php echo esc_attr( -0.34 * $i ); ?>s">
					<path d="M-140 <?php echo (int) ( 34 + $i * 24 ); ?>c30-17 60 17 90 0s60-17 90 0 60 17 90 0 60-17 90 0 60 17 90 0 60-17 90 0"/>
				</g>
			<?php endfor; ?>
		</g>
	</svg>
	<?php
	return ob_get_clean();
}

/**
 * Resa: l'energia elettrica in ingresso e il calore restituito.
 */
function pdz_conf_hero_resa() {
	ob_start();
	?>
	<svg class="pdz-scene pdz-ratio" viewBox="0 0 300 300" role="img" aria-hidden="true" focusable="false">
		<circle class="pdz-ratio__track" cx="150" cy="150" r="104"/>
		<circle class="pdz-ratio__track pdz-ratio__track--in" cx="150" cy="150" r="72"/>

		<!-- energia resa: tre quarti dell'anello -->
		<circle class="pdz-ratio__out" cx="150" cy="150" r="104"/>
		<!-- energia assorbita: un quarto -->
		<circle class="pdz-ratio__in" cx="150" cy="150" r="72"/>

		<g class="pdz-ratio__runner">
			<circle cx="150" cy="46" r="7"/>
		</g>

		<circle class="pdz-ratio__core" cx="150" cy="150" r="16"/>
	</svg>
	<?php
	return ob_get_clean();
}

/**
 * Impulso in rete: la casa che si elettrifica, nodo dopo nodo.
 */
function pdz_conf_hero_rete() {
	ob_start();
	$dots = '';
	for ( $r = 0; $r < 5; $r++ ) {
		for ( $c = 0; $c < 9; $c++ ) {
			$dots .= sprintf( '<circle cx="%d" cy="%d" r="2.5"/>', 40 + $c * 40, 40 + $r * 40 );
		}
	}
	?>
	<svg class="pdz-scene pdz-net" viewBox="0 0 400 220" role="img" aria-hidden="true" focusable="false">
		<g class="pdz-net__dots"><?php echo $dots; // coordinate generate sopra ?></g>

		<path class="pdz-net__line" d="M40 160h80v-80h80v40h80v-80h80"/>
		<path class="pdz-net__pulse" d="M40 160h80v-80h80v40h80v-80h80"/>

		<g class="pdz-net__nodes">
			<circle class="pdz-net__node pdz-net__node--1" cx="120" cy="80" r="6"/>
			<circle class="pdz-net__node pdz-net__node--2" cx="200" cy="120" r="6"/>
			<circle class="pdz-net__node pdz-net__node--3" cx="280" cy="40" r="6"/>
		</g>
	</svg>
	<?php
	return ob_get_clean();
}

/**
 * Emissione: la macchina che diffonde calore nell'ambiente.
 */
function pdz_conf_hero_emissione() {
	ob_start();
	?>
	<svg class="pdz-scene pdz-emit" viewBox="0 0 380 240" role="img" aria-hidden="true" focusable="false">
		<g class="pdz-emit__waves">
			<path class="pdz-emit__wave pdz-emit__wave--1" d="M150 44a96 96 0 0 1 0 152"/>
			<path class="pdz-emit__wave pdz-emit__wave--2" d="M150 44a96 96 0 0 1 0 152"/>
			<path class="pdz-emit__wave pdz-emit__wave--3" d="M150 44a96 96 0 0 1 0 152"/>
		</g>

		<rect class="pdz-emit__unit" x="52" y="80" width="96" height="80" rx="10"/>
		<circle class="pdz-emit__fan" cx="100" cy="120" r="24"/>
		<g class="pdz-emit__blades">
			<path d="M100 102c9 5 9 15 0 20M100 138c-9-5-9-15 0-20M116 130c-5-9-14-11-20-5M84 110c5 9 14 11 20 5"/>
		</g>
		<path class="pdz-emit__vent" d="M62 92h16"/>
	</svg>
	<?php
	return ob_get_clean();
}

/**
 * Carica gli asset in head quando lo shortcode e' nel contenuto del post.
 */
add_action( 'wp_enqueue_scripts', 'pdz_conf_maybe_enqueue' );

function pdz_conf_maybe_enqueue() {
	if ( ! is_singular() ) {
		return;
	}
	$post = get_post();
	if ( $post && ( has_shortcode( $post->post_content, 'configuratore' ) || has_shortcode( $post->post_content, 'configuratore_pedrinazzi' ) ) ) {
		pdz_conf_enqueue_assets();
	}
}

function pdz_conf_enqueue_assets() {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;

	$s = pdz_conf_settings();

	if ( ! empty( $s['load_google_font'] ) ) {
		wp_enqueue_style(
			'pdz-inter',
			'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
			array(),
			null
		);
	}

	wp_enqueue_style( 'pdz-conf', PDZ_CONF_URL . 'assets/css/frontend.css', array(), PDZ_CONF_VERSION );
	wp_enqueue_script( 'pdz-conf', PDZ_CONF_URL . 'assets/js/frontend.js', array(), PDZ_CONF_VERSION, true );

	$accent = sanitize_hex_color( $s['accent'] );
	$width  = (int) $s['max_width'];
	$vars   = '.pdz-conf{';
	if ( $accent ) {
		$vars .= '--pdz-accent:' . $accent . ';--pdz-accent-soft:' . pdz_conf_hex_to_rgba( $accent, 0.06 ) . ';--pdz-accent-dark:' . pdz_conf_darken( $accent, 0.15 ) . ';';
	}
	if ( $width > 0 ) {
		$vars .= 'max-width:' . $width . 'px;';
	}
	$vars .= '--pdz-btn-radius:' . max( 0, min( 999, (int) $s['btn_radius'] ) ) . 'px;';

	// Personalizzazione del tema Vivace.
	if ( 'vivace' === pdz_conf_tema() ) {
		$bg   = sanitize_hex_color( $s['vivace_bg'] );
		$sec  = sanitize_hex_color( $s['vivace_secondo'] );
		$vars .= '--pdz-vivace-bg:' . ( $bg ? $bg : '#0c0f13' ) . ';';
		$vars .= '--pdz-secondo:' . ( $sec ? $sec : '#ff9a3d' ) . ';';
		$vars .= '--pdz-vivace-radius:' . max( 0, min( 60, (int) $s['vivace_radius'] ) ) . 'px;';
		$vars .= '--pdz-tempo:' . pdz_conf_fattore_velocita() . ';';
	}
	$vars .= '}';

	wp_add_inline_style( 'pdz-conf', $vars );
}

function pdz_conf_hex_to_rgba( $hex, $alpha ) {
	$hex = ltrim( $hex, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	return sprintf( 'rgba(%d,%d,%d,%s)', hexdec( substr( $hex, 0, 2 ) ), hexdec( substr( $hex, 2, 2 ) ), hexdec( substr( $hex, 4, 2 ) ), $alpha );
}

function pdz_conf_darken( $hex, $amount ) {
	$hex = ltrim( $hex, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	$out = '#';
	for ( $i = 0; $i < 3; $i++ ) {
		$c    = hexdec( substr( $hex, $i * 2, 2 ) );
		$c    = max( 0, (int) round( $c * ( 1 - $amount ) ) );
		$out .= str_pad( dechex( $c ), 2, '0', STR_PAD_LEFT );
	}
	return $out;
}
