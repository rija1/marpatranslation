<?php
/**
 * Article seul — hero de page + contenu dans la mise en page content-section.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$cats = get_the_category();
	?>
	<section class="page-hero">
		<div class="page-hero-content">
			<h1><?php the_title(); ?></h1>
			<p><?php echo esc_html( ( $cats ? $cats[0]->name . ' · ' : '' ) . get_the_date() ); ?></p>
		</div>
	</section>
	<div class="gold-divider"></div>
	<section class="content-section">
		<?php the_content(); ?>
	</section>
	<?php
endwhile;

get_footer();
