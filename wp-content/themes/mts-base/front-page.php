<?php
/**
 * Front page (portage mockup-1/index.html) — chaque section vient d'un CPT
 * (mts_section, product, post, translated_term) avec repli mockup.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$hero = mts_section_one( 'hero', 'home' );
?>

<section class="hero">
	<div class="hero-texture"></div>
	<div class="hero-mandala"></div>
	<div class="hero-image-area"></div>
	<div class="hero-content">
		<?php if ( $hero ) : ?>
			<div class="hero-eyebrow"><?php echo esc_html( $hero['meta']['eyebrow'] ); ?></div>
			<h1><?php echo mts_kses_title( $hero['title'] ); ?></h1>
			<p class="hero-desc"><?php echo esc_html( wp_strip_all_tags( $hero['raw'] ) ); ?></p>
			<div class="hero-ctas">
				<?php if ( $hero['meta']['cta_label'] ) : ?>
					<a href="<?php echo esc_url( home_url( $hero['meta']['cta_url'] ) ); ?>" class="btn-primary"><?php echo esc_html( $hero['meta']['cta_label'] ); ?></a>
				<?php endif; ?>
				<?php if ( $hero['meta']['cta2_label'] ) : ?>
					<a href="<?php echo esc_url( home_url( $hero['meta']['cta2_url'] ) ); ?>" class="btn-ghost"><?php echo esc_html( $hero['meta']['cta2_label'] ); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<div class="hero-scroll"><?php esc_html_e( 'Scroll', 'mts-base' ); ?></div>
</section>

<?php mts_ornament_strip( 'home' ); ?>

<section class="principles">
	<?php foreach ( mts_section_posts( 'principles' ) as $principle ) : ?>
		<div class="principle">
			<div class="principle-icon"></div>
			<h3><?php echo esc_html( $principle['title'] ); ?></h3>
			<?php echo wp_kses_post( $principle['content'] ); ?>
		</div>
	<?php endforeach; ?>
</section>

<?php $mission = mts_section_one( 'mission', 'home' ); ?>
<?php if ( $mission ) : ?>
<section class="mission">
	<div class="mission-text">
		<div class="section-eyebrow"><?php echo esc_html( $mission['meta']['eyebrow'] ); ?></div>
		<h2><?php echo esc_html( $mission['title'] ); ?></h2>
		<?php echo wp_kses_post( $mission['content'] ); ?>
		<?php if ( $mission['meta']['pull_quote'] ) : ?>
			<div class="pull-quote">
				<?php echo esc_html( $mission['meta']['pull_quote'] ); ?>
				<cite><?php echo esc_html( $mission['meta']['pull_quote_cite'] ); ?></cite>
			</div>
		<?php endif; ?>
		<?php if ( $mission['meta']['cta_label'] ) : ?>
			<a href="<?php echo esc_url( home_url( $mission['meta']['cta_url'] ) ); ?>" class="read-more"><?php echo esc_html( $mission['meta']['cta_label'] ); ?></a>
		<?php endif; ?>
	</div>
	<div class="mission-image"<?php echo $mission['thumb_url'] ? ' style="background-image:url(' . esc_url( $mission['thumb_url'] ) . ');background-size:cover;"' : ''; ?>></div>
</section>
<?php endif; ?>

<?php
$catalog_intro = mts_section_one( 'catalog-intro', 'home' );
$books         = mts_get_branch_books( 4 );
if ( ! $books ) {
	$books = mts_sample_books();
}
?>
<section class="catalog">
	<div class="catalog-header">
		<?php if ( $catalog_intro ) : ?>
			<div class="section-eyebrow" style="text-align:center;"><?php echo esc_html( $catalog_intro['meta']['eyebrow'] ); ?></div>
			<h2><?php echo esc_html( $catalog_intro['title'] ); ?></h2>
			<p><?php echo esc_html( wp_strip_all_tags( $catalog_intro['raw'] ) ); ?></p>
		<?php endif; ?>
	</div>
	<div class="catalog-filters">
		<button class="filter-btn active"><?php esc_html_e( 'All Languages', 'mts-base' ); ?></button>
		<?php
		$languages = get_terms( array( 'taxonomy' => 'mts_language', 'hide_empty' => false ) );
		if ( ! is_wp_error( $languages ) ) :
			foreach ( $languages as $language ) :
				?>
				<button class="filter-btn"><?php echo esc_html( $language->name ); ?></button>
				<?php
			endforeach;
		endif;
		?>
	</div>
	<div class="book-grid">
		<?php foreach ( $books as $book ) : ?>
			<?php mts_the_book_card( $book ); ?>
		<?php endforeach; ?>
	</div>
	<?php if ( $catalog_intro && $catalog_intro['meta']['cta_label'] ) : ?>
		<div class="catalog-footer">
			<a href="<?php echo esc_url( home_url( $catalog_intro['meta']['cta_url'] ) ); ?>" class="btn-outline"><?php echo esc_html( $catalog_intro['meta']['cta_label'] ); ?></a>
		</div>
	<?php endif; ?>
</section>

<?php $knowledge = mts_section_one( 'knowledge', 'home' ); ?>
<?php if ( $knowledge ) : ?>
<section style="background: var(--parchment); padding: 80px 40px;">
	<div class="knowledge" style="margin:0 auto; padding:0; max-width:1100px;">
		<div class="knowledge-text">
			<div class="section-eyebrow"><?php echo esc_html( $knowledge['meta']['eyebrow'] ); ?></div>
			<h2><?php echo esc_html( $knowledge['title'] ); ?></h2>
			<?php echo wp_kses_post( $knowledge['content'] ); ?>
			<ul class="knowledge-links">
				<li><a href="<?php echo esc_url( home_url( '/glossary/' ) ); ?>"><?php esc_html_e( 'Tibetan Term Dictionary → Translation choices across languages', 'mts-base' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/library/' ) ); ?>"><?php esc_html_e( 'Source Texts → Original Tibetan works with linked translations', 'mts-base' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/glossary/' ) ); ?>"><?php esc_html_e( 'Glossary → Defined Buddhist terms', 'mts-base' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'Translator Profiles → Practitioner-scholar bios', 'mts-base' ); ?></a></li>
			</ul>
			<?php if ( $knowledge['meta']['cta_label'] ) : ?>
				<a href="<?php echo esc_url( home_url( $knowledge['meta']['cta_url'] ) ); ?>" class="read-more"><?php echo esc_html( $knowledge['meta']['cta_label'] ); ?></a>
			<?php endif; ?>
		</div>
		<div class="knowledge-visual">
			<?php foreach ( mts_get_home_term_cards() as $card ) : ?>
				<div class="term-card">
					<div class="term-tibetan"><?php echo esc_html( $card['tibetan'] ); ?></div>
					<div class="term-english"><?php echo esc_html( $card['english'] ); ?></div>
					<?php if ( ! empty( $card['sanskrit'] ) ) : ?>
						<div class="term-sanskrit"><?php echo esc_html( sprintf( __( 'Sanskrit: %s', 'mts-base' ), $card['sanskrit'] ) ); ?></div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="articles">
	<div class="articles-header">
		<div>
			<div class="section-eyebrow" style="margin-bottom:10px;"><?php esc_html_e( 'Essays & Articles', 'mts-base' ); ?></div>
			<h2><?php esc_html_e( 'Translation and Tradition', 'mts-base' ); ?></h2>
		</div>
		<a href="<?php echo esc_url( home_url( '/articles/' ) ); ?>"><?php esc_html_e( 'All Articles →', 'mts-base' ); ?></a>
	</div>
	<div class="article-grid">
		<?php
		$latest = get_posts( array( 'posts_per_page' => 3 ) );
		if ( $latest ) :
			foreach ( $latest as $article ) :
				$cats = get_the_category( $article->ID );
				?>
				<a href="<?php echo esc_url( get_permalink( $article ) ); ?>" class="article-card">
					<div class="article-thumb"<?php echo has_post_thumbnail( $article ) ? ' style="background:url(' . esc_url( get_the_post_thumbnail_url( $article, 'medium_large' ) ) . ') center/cover no-repeat;"' : ''; ?>></div>
					<div class="article-body">
						<div class="article-cat"><?php echo esc_html( $cats ? $cats[0]->name : __( 'Article', 'mts-base' ) ); ?></div>
						<div class="article-title"><?php echo esc_html( get_the_title( $article ) ); ?></div>
						<div class="article-excerpt"><?php echo esc_html( get_the_excerpt( $article ) ); ?></div>
						<div class="article-meta"><?php echo esc_html( get_the_date( '', $article ) ); ?></div>
					</div>
				</a>
				<?php
			endforeach;
		else :
			foreach ( mts_sample_articles() as $article ) :
				?>
				<a href="<?php echo esc_url( home_url( '/articles/' ) ); ?>" class="article-card">
					<div class="article-thumb" style="background:url('<?php echo esc_url( $article['image'] ); ?>') center/cover no-repeat;"></div>
					<div class="article-body">
						<div class="article-cat"><?php echo esc_html( $article['cat'] ); ?></div>
						<div class="article-title"><?php echo esc_html( $article['title'] ); ?></div>
						<div class="article-excerpt"><?php echo esc_html( $article['excerpt'] ); ?></div>
						<div class="article-meta"><?php echo esc_html( $article['meta'] ); ?></div>
					</div>
				</a>
				<?php
			endforeach;
		endif;
		?>
	</div>
</section>

<?php $support = mts_section_one( 'support', 'home' ); ?>
<?php if ( $support ) : ?>
<section class="support">
	<div class="support-inner">
		<div class="section-eyebrow"><?php echo esc_html( $support['meta']['eyebrow'] ); ?></div>
		<h2><?php echo mts_kses_title( $support['title'] ); ?></h2>
		<p><?php echo esc_html( wp_strip_all_tags( $support['raw'] ) ); ?></p>
		<a href="<?php echo esc_url( $support['meta']['cta_url'] ? $support['meta']['cta_url'] : mts_get_donate_url() ); ?>" class="btn-gold"><?php echo esc_html( $support['meta']['cta_label'] ); ?></a>
	</div>
</section>
<?php endif; ?>

<?php get_footer(); ?>
