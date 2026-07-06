<?php
/**
 * Front neutralisé du référentiel (D13) : page technique unique.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'mts-referentiel' ); ?>>
<div class="mts-referentiel-card">
	<h1><?php esc_html_e( 'MTS Terminological Referential', 'mts-referentiel' ); ?></h1>
	<p><?php esc_html_e( 'This site hosts the shared referential of the Marpa Translation Society network. It has no public content.', 'mts-referentiel' ); ?></p>
	<p><a href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'Editor access', 'mts-referentiel' ); ?></a></p>
</div>
<?php wp_footer(); ?>
</body>
</html>
