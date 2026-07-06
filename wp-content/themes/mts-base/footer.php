<?php
/**
 * Footer (portage mockup-1) — colonnes de liens vers les pages standard.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<footer>
	<div class="footer-grid">
		<div class="footer-brand">
			<div class="footer-logo">
				<img src="<?php echo esc_url( mts_get_logo_url() ); ?>" alt="<?php echo esc_attr( mts_get_brand_name() ); ?>">
				<div class="footer-logo-name"><?php echo esc_html( mts_get_brand_name() ); ?></div>
			</div>
			<p class="footer-tagline"><?php echo esc_html( mts_get_footer_tagline() ); ?></p>
		</div>
		<div class="footer-col">
			<h4><?php esc_html_e( 'Library', 'mts-base' ); ?></h4>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/library/' ) ); ?>"><?php esc_html_e( 'Published Translations', 'mts-base' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/glossary/' ) ); ?>"><?php esc_html_e( 'Glossary', 'mts-base' ); ?></a></li>
			</ul>
		</div>
		<div class="footer-col">
			<h4><?php esc_html_e( 'Learn', 'mts-base' ); ?></h4>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/articles/' ) ); ?>"><?php esc_html_e( 'Articles', 'mts-base' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/knowledge-hub/' ) ); ?>"><?php esc_html_e( 'Knowledge Hub', 'mts-base' ); ?></a></li>
			</ul>
		</div>
		<div class="footer-col">
			<h4><?php esc_html_e( 'Society', 'mts-base' ); ?></h4>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'About MTS', 'mts-base' ); ?></a></li>
				<li><a href="<?php echo esc_url( mts_get_donate_url() ); ?>"><?php echo esc_html( mts_get_donate_label() ); ?></a></li>
			</ul>
		</div>
	</div>
	<div class="footer-bottom">
		<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( mts_get_brand_name() ); ?>. <?php esc_html_e( 'All rights reserved.', 'mts-base' ); ?></p>
		<p><a href="#"><?php esc_html_e( 'Privacy Policy', 'mts-base' ); ?></a> &middot; <a href="#"><?php esc_html_e( 'Terms of Use', 'mts-base' ); ?></a></p>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
