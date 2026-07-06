<?php
/**
 * Page des articles (posts page — portage mockup-1/articles.html) :
 * article en avant + grille.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

mts_page_hero( 'articles' );
mts_ornament_strip( 'articles' );

if ( have_posts() ) :
	$first = true;
	$grid_open = false;
	while ( have_posts() ) :
		the_post();
		if ( $first ) :
			$first = false;
			?>
			<section class="featured-section">
				<article class="featured-article">
					<div class="featured-image"<?php echo has_post_thumbnail() ? ' style="background:url(' . esc_url( get_the_post_thumbnail_url( null, 'large' ) ) . ') center/cover no-repeat;"' : ''; ?>></div>
					<div class="featured-content">
						<div class="article-cat"><?php esc_html_e( 'Featured Article', 'mts-base' ); ?></div>
						<h2><?php the_title(); ?></h2>
						<p><?php echo esc_html( get_the_excerpt() ); ?></p>
						<a href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read Full Article →', 'mts-base' ); ?></a>
					</div>
				</article>
			</section>
			<section class="articles-grid-section">
				<div class="articles-grid-header">
					<h2><?php esc_html_e( 'More Articles & Essays', 'mts-base' ); ?></h2>
					<p><?php esc_html_e( 'Explore our collection of writings on translation, practice, lineage history, and Buddhist philosophy.', 'mts-base' ); ?></p>
				</div>
				<div class="article-grid">
			<?php
			$grid_open = true;
		else :
			$cats = get_the_category();
			?>
			<a href="<?php the_permalink(); ?>" class="article-card">
				<div class="article-thumb"<?php echo has_post_thumbnail() ? ' style="background:url(' . esc_url( get_the_post_thumbnail_url( null, 'medium_large' ) ) . ') center/cover no-repeat;"' : ''; ?>></div>
				<div class="article-body">
					<div class="article-cat"><?php echo esc_html( $cats ? $cats[0]->name : __( 'Article', 'mts-base' ) ); ?></div>
					<h3 class="article-title"><?php the_title(); ?></h3>
					<p class="article-excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<div class="article-meta"><?php echo esc_html( get_the_date() ); ?></div>
				</div>
			</a>
			<?php
		endif;
	endwhile;
	if ( $grid_open ) :
		?>
				</div>
			</section>
		<?php
	endif;
	the_posts_pagination();
else :
	?>
	<section class="articles-grid-section">
		<div class="articles-grid-header">
			<h2><?php esc_html_e( 'Articles & Essays', 'mts-base' ); ?></h2>
			<p><?php esc_html_e( 'Our first articles are being prepared — here is a preview of what to expect.', 'mts-base' ); ?></p>
		</div>
		<div class="article-grid">
			<?php foreach ( mts_sample_articles() as $article ) : ?>
				<a href="#" class="article-card">
					<div class="article-thumb" style="background:url('<?php echo esc_url( $article['image'] ); ?>') center/cover no-repeat;"></div>
					<div class="article-body">
						<div class="article-cat"><?php echo esc_html( $article['cat'] ); ?></div>
						<h3 class="article-title"><?php echo esc_html( $article['title'] ); ?></h3>
						<p class="article-excerpt"><?php echo esc_html( $article['excerpt'] ); ?></p>
						<div class="article-meta"><?php echo esc_html( $article['meta'] ); ?></div>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
	</section>
	<?php
endif;

get_footer();
