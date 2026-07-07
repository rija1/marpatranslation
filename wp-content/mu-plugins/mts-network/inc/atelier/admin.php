<?php
/**
 * Atelier — écran d'admin des branches (spec atelier-terminologique.md §3).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', function () {
	if ( is_main_site() ) {
		return;
	}
	add_menu_page(
		__( 'Terminology Workbench', 'mts-network' ),
		__( 'Atelier', 'mts-network' ),
		'mts_use_atelier',
		'mts-atelier',
		'mts_atelier_render_admin_page',
		'dashicons-translation',
		27
	);
} );

function mts_atelier_render_admin_page() {
	$configured = mts_atelier_is_configured();
	$products   = get_posts( array( 'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => 100, 'orderby' => 'title', 'order' => 'ASC' ) );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Terminology Workbench', 'mts-network' ); ?></h1>

		<?php if ( ! $configured ) : ?>
			<div class="notice notice-warning"><p>
				<?php esc_html_e( 'Extraction engine not configured: add ANTHROPIC_API_KEY to wp-config.php (see doc/specs/llm-extraction.md). The screen stays usable for reviewing existing candidates.', 'mts-network' ); ?>
			</p></div>
		<?php endif; ?>

		<div id="mts-atelier-app"
			data-rest="<?php echo esc_attr( esc_url_raw( rest_url( 'mts/v1/atelier' ) ) ); ?>"
			data-nonce="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>"
			data-lang="<?php echo esc_attr( get_option( 'mts_default_language', 'en' ) ); ?>">

			<table class="form-table" role="presentation">
				<tr>
					<th><label for="mts-source"><?php esc_html_e( 'Tibetan source', 'mts-network' ); ?></label></th>
					<td><textarea id="mts-source" rows="7" class="large-text" style="font-size:1.2em;" placeholder="བོད་ཡིག …"></textarea></td>
				</tr>
				<tr>
					<th><label for="mts-target"><?php esc_html_e( 'Your translation', 'mts-network' ); ?></label></th>
					<td><textarea id="mts-target" rows="7" class="large-text"></textarea></td>
				</tr>
				<tr>
					<th><label for="mts-translation"><?php esc_html_e( 'Attach to translation', 'mts-network' ); ?></label></th>
					<td>
						<select id="mts-translation">
							<option value="0"><?php esc_html_e( '— none —', 'mts-network' ); ?></option>
							<?php foreach ( $products as $product ) : ?>
								<option value="<?php echo esc_attr( $product->ID ); ?>"><?php echo esc_html( $product->post_title ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( 'Term usages will be linked to this published translation.', 'mts-network' ); ?></p>
					</td>
				</tr>
			</table>
			<p>
				<button class="button button-primary button-hero" id="mts-extract" <?php disabled( ! $configured ); ?>><?php esc_html_e( 'Extract term pairs', 'mts-network' ); ?></button>
				<span id="mts-progress" style="margin-left:12px;"></span>
			</p>

			<h2><?php esc_html_e( 'Candidates', 'mts-network' ); ?></h2>
			<table class="widefat striped" id="mts-candidates">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Tibetan', 'mts-network' ); ?></th>
						<th><?php esc_html_e( 'Wylie', 'mts-network' ); ?></th>
						<th><?php esc_html_e( 'Target term', 'mts-network' ); ?></th>
						<th><?php esc_html_e( 'Context', 'mts-network' ); ?></th>
						<th><?php esc_html_e( 'Conf.', 'mts-network' ); ?></th>
						<th><?php esc_html_e( 'Status', 'mts-network' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'mts-network' ); ?></th>
					</tr>
				</thead>
				<tbody><tr><td colspan="7"><?php esc_html_e( 'Run an extraction to see candidates.', 'mts-network' ); ?></td></tr></tbody>
			</table>
		</div>
	</div>
	<?php
	wp_enqueue_script( 'mts-atelier', plugins_url( 'mts-network/assets/atelier.js', dirname( __DIR__, 2 ) . '/mts-network.php' ), array(), MTS_NETWORK_VERSION, true );
}
