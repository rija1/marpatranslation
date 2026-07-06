<?php
/**
 * Header — util-bar + navigation (portage mockup-1). Les sections de page
 * vivent directement dans <body> (structure du mockup, pas de <main>).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="util-bar">
	<span><?php
		$notice = function_exists( 'mts_section_one' ) ? mts_section_one( 'util-bar', 'notice' ) : null;
		echo esc_html( $notice ? wp_strip_all_tags( $notice['raw'] ) : __( 'All translations provided free of charge', 'mts-base' ) );
	?></span>
	<div class="util-bar-right">
		<a href="#"><?php esc_html_e( 'Newsletter', 'mts-base' ); ?></a>
		<a href="<?php echo esc_url( get_search_link( '' ) ); ?>">&#128269; <?php esc_html_e( 'Search', 'mts-base' ); ?></a>
	</div>
</div>

<nav>
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nav-logo">
		<img src="<?php echo esc_url( mts_get_logo_url() ); ?>" alt="<?php echo esc_attr( mts_get_brand_name() ); ?>">
		<div class="nav-logo-text">
			<?php echo esc_html( mts_get_brand_name() ); ?>
			<span><?php echo esc_html( mts_get_brand_tag() ); ?></span>
		</div>
	</a>
	<?php
	wp_nav_menu( array(
		'theme_location' => 'primary',
		'container'      => false,
		'menu_class'     => 'nav-links',
		'fallback_cb'    => 'mts_nav_fallback',
		'depth'          => 1,
	) );
	?>
	<div class="nav-right">
		<a href="<?php echo esc_url( mts_get_donate_url() ); ?>" class="nav-donate"><?php echo esc_html( mts_get_donate_label() ); ?></a>
	</div>
</nav>
