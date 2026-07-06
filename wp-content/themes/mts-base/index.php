<?php
/**
 * Fallback minimal de la hiérarchie de templates.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<section class="content-section">
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			?>
			<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<p><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php
		endwhile;
		the_posts_pagination();
	else :
		?>
		<p><?php esc_html_e( 'Nothing found.', 'mts-base' ); ?></p>
	<?php endif; ?>
</section>
<?php
get_footer();
