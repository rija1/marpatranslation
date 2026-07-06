<?php
/**
 * Page générique — hero + contenu.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<section class="page-hero">
		<div class="page-hero-content">
			<h1><?php the_title(); ?></h1>
		</div>
	</section>
	<div class="gold-divider"></div>
	<section class="content-section">
		<?php the_content(); ?>
	</section>
	<?php
endwhile;

get_footer();
