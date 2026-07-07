<?php
/**
 * Fiche terme (Phase 5) : le terme local + le référentiel central
 * (tibétain/Wylie/sanskrit/définition) + équivalents des autres branches
 * + usages documentés avec citations. La valeur scientifique du réseau.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$term_id    = get_the_ID();
	$central_id = (int) get_post_meta( $term_id, 'central_tibetan_term_id', true );
	$central    = ( $central_id && function_exists( 'mts_get_tibetan_term' ) ) ? mts_get_tibetan_term( $central_id ) : null;
	$branches   = ( $central_id && function_exists( 'mts_get_term_translations' ) ) ? mts_get_term_translations( $central_id ) : array();
	$here       = function_exists( 'mts_branch_slug_from_site' ) ? mts_branch_slug_from_site( get_site() ) : '';
	?>

	<section class="page-hero">
		<div class="page-hero-content">
			<?php if ( $central ) : ?>
				<h1 class="term-tibetan" style="font-size:2.6em;"><?php echo esc_html( $central['tibetan'] ); ?></h1>
			<?php endif; ?>
			<p><?php the_title(); ?></p>
		</div>
	</section>
	<div class="gold-divider"></div>

	<section class="glossary-section">
		<div class="glossary-grid">
			<div class="term-entry">
				<div class="term-header">
					<div class="term-tibetan-block">
						<div class="term-tibetan"><?php echo esc_html( $central ? $central['tibetan'] : '' ); ?></div>
						<div class="term-transliteration"><?php echo esc_html( $central && $central['wylie'] ? $central['wylie'] : ( $central ? $central['title'] : '' ) ); ?></div>
					</div>
					<?php if ( $central && $central['sanskrit'] ) : ?>
						<div class="term-sanskrit-block">
							<div class="term-sanskrit"><?php echo esc_html( $central['sanskrit'] ); ?></div>
							<div class="term-sanskrit-label"><?php esc_html_e( 'Sanskrit', 'mts-base' ); ?></div>
						</div>
					<?php endif; ?>
					<div class="term-english-block">
						<div class="term-english"><?php echo esc_html( mts_glossary_target_language_label() ); ?></div>
						<div class="term-english-text"><?php the_title(); ?></div>
					</div>
				</div>
				<?php if ( $central && trim( $central['definition'] ) ) : ?>
					<div class="term-explanation"><?php echo esc_html( wp_strip_all_tags( $central['definition'] ) ); ?></div>
				<?php endif; ?>
				<?php $notes = get_post_meta( $term_id, 'term_notes', true ); ?>
				<?php if ( $notes ) : ?>
					<div class="term-explanation"><?php echo esc_html( $notes ); ?></div>
				<?php endif; ?>
			</div>

			<?php
			// Équivalents dans les autres branches du réseau.
			$others = array();
			foreach ( $branches as $branch => $terms ) {
				if ( $branch === $here ) {
					continue;
				}
				foreach ( $terms as $t ) {
					$others[] = $t;
				}
			}
			if ( $others ) :
				?>
				<div class="term-entry">
					<div class="term-header">
						<div class="term-english-block">
							<div class="term-english"><?php esc_html_e( 'Across the network', 'mts-base' ); ?></div>
							<div class="term-english-text"><?php esc_html_e( 'How our sister organisations render this term', 'mts-base' ); ?></div>
						</div>
					</div>
					<?php foreach ( $others as $other ) : ?>
						<div class="term-explanation">
							<strong><?php echo esc_html( $other['term'] ); ?></strong>
							<?php echo esc_html( ' — ' . strtoupper( $other['lang'] ) . ( $other['usages_count'] ? ' · ' . sprintf( _n( '%d documented usage', '%d documented usages', $other['usages_count'], 'mts-base' ), $other['usages_count'] ) : '' ) ); ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php
			// Usages documentés (citations source/cible).
			$usages = get_posts( array(
				'post_type'      => 'term_usage',
				'post_status'    => 'publish',
				'posts_per_page' => 20,
				'meta_query'     => array( array( 'key' => 'translated_term_id', 'value' => $term_id ) ),
			) );
			foreach ( $usages as $usage ) :
				$src = get_post_meta( $usage->ID, 'context_source', true );
				$tgt = get_post_meta( $usage->ID, 'context_target', true );
				$ref = get_post_meta( $usage->ID, 'quote_reference', true );
				if ( ! $src && ! $tgt ) {
					continue;
				}
				?>
				<div class="term-entry">
					<div class="term-header">
						<div class="term-english-block">
							<div class="term-english"><?php esc_html_e( 'In context', 'mts-base' ); ?></div>
							<div class="term-english-text"><?php echo esc_html( $usage->post_title ); ?></div>
						</div>
					</div>
					<?php if ( $src ) : ?>
						<div class="term-explanation term-tibetan" style="font-size:1.15em;"><?php echo nl2br( esc_html( $src ) ); ?></div>
					<?php endif; ?>
					<?php if ( $tgt ) : ?>
						<div class="term-explanation"><?php echo nl2br( esc_html( $tgt ) ); ?><?php echo $ref ? ' <em>(' . esc_html( $ref ) . ')</em>' : ''; ?></div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
	<?php
endwhile;

get_footer();
