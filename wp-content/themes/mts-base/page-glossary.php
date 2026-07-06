<?php
/**
 * Page Glossary (portage mockup-1/glossary.html) — translated_term de la
 * branche joints au référentiel central (tibétain/Wylie/sanskrit/définition).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

mts_page_hero( 'glossary' );
mts_ornament_strip( 'glossary' );

$entries = mts_get_branch_glossary_entries( 60 );
if ( ! $entries ) {
	$entries = mts_sample_glossary_entries();
}

$letters = array();
foreach ( $entries as $entry ) {
	$initial = strtoupper( mb_substr( trim( $entry['transliteration'] ? $entry['transliteration'] : $entry['english'] ), 0, 1 ) );
	if ( '' !== $initial ) {
		$letters[ $initial ] = true;
	}
}
?>

<div class="alphabet-nav">
	<button class="alphabet-btn active"><?php esc_html_e( 'All', 'mts-base' ); ?></button>
	<?php foreach ( array_keys( $letters ) as $letter ) : ?>
		<button class="alphabet-btn"><?php echo esc_html( $letter ); ?></button>
	<?php endforeach; ?>
</div>

<section class="glossary-section">
	<div class="glossary-grid">
		<?php foreach ( $entries as $entry ) : ?>
			<div class="term-entry">
				<div class="term-header">
					<div class="term-tibetan-block">
						<div class="term-tibetan"><?php echo esc_html( $entry['tibetan'] ); ?></div>
						<div class="term-transliteration"><?php echo esc_html( $entry['transliteration'] ); ?></div>
					</div>
					<?php if ( ! empty( $entry['sanskrit'] ) ) : ?>
						<div class="term-sanskrit-block">
							<div class="term-sanskrit"><?php echo esc_html( $entry['sanskrit'] ); ?></div>
							<div class="term-sanskrit-label"><?php esc_html_e( 'Sanskrit', 'mts-base' ); ?></div>
						</div>
					<?php endif; ?>
					<div class="term-english-block">
						<div class="term-english"><?php echo esc_html( mts_glossary_target_language_label() ); ?></div>
						<div class="term-english-text"><?php echo esc_html( $entry['english'] ); ?></div>
					</div>
				</div>
				<?php if ( ! empty( $entry['explanation'] ) ) : ?>
					<div class="term-explanation"><?php echo esc_html( $entry['explanation'] ); ?></div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</section>

<?php get_footer(); ?>
