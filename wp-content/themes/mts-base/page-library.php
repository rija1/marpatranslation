<?php
/**
 * Page Library (portage mockup-1/library.html). Livres = produits Woo
 * (Phase 4) avec repli échantillons mockup.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

mts_page_hero( 'library' );
mts_ornament_strip( 'library' );

$books = mts_get_branch_books( 12 );
if ( ! $books ) {
	$books = mts_sample_books();
}
?>

<div class="catalog-filters">
	<button class="filter-btn active"><?php esc_html_e( 'All Texts', 'mts-base' ); ?></button>
	<?php
	$cats = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true ) );
	if ( ! is_wp_error( $cats ) && $cats ) :
		foreach ( $cats as $cat ) :
			?>
			<button class="filter-btn"><?php echo esc_html( $cat->name ); ?></button>
			<?php
		endforeach;
	else :
		foreach ( array( __( 'Practice Texts', 'mts-base' ), __( 'Philosophical Treatises', 'mts-base' ), __( 'Commentaries', 'mts-base' ), __( 'Poetry', 'mts-base' ) ) as $label ) :
			?>
			<button class="filter-btn"><?php echo esc_html( $label ); ?></button>
			<?php
		endforeach;
	endif;
	?>
</div>

<div class="book-grid">
	<?php foreach ( $books as $book ) : ?>
		<?php mts_the_book_card( $book, true ); ?>
	<?php endforeach; ?>
</div>

<?php $forthcoming_intro = mts_section_one( 'forthcoming-intro', 'library' ); ?>
<section class="forthcoming">
	<div class="section-header">
		<?php if ( $forthcoming_intro ) : ?>
			<h2><?php echo esc_html( $forthcoming_intro['title'] ); ?></h2>
			<p><?php echo esc_html( wp_strip_all_tags( $forthcoming_intro['raw'] ) ); ?></p>
		<?php endif; ?>
	</div>
	<div class="coming-soon-grid">
		<?php foreach ( mts_get_forthcoming() as $item ) : ?>
			<div class="coming-soon-card">
				<div class="coming-soon-badge"><?php echo esc_html( $item['badge'] ); ?></div>
				<h3><?php echo esc_html( $item['title'] ); ?></h3>
				<div class="book-author"><?php echo esc_html( $item['author'] ); ?></div>
				<p><?php echo esc_html( $item['description'] ); ?></p>
			</div>
		<?php endforeach; ?>
	</div>
</section>

<?php get_footer(); ?>
