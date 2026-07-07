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

// Recherche serveur (?gq=) sur tibétain / translittération / terme cible.
$gq = isset( $_GET['gq'] ) ? sanitize_text_field( wp_unslash( $_GET['gq'] ) ) : '';
if ( '' !== $gq ) {
	$entries = array_values( array_filter( $entries, function ( $entry ) use ( $gq ) {
		foreach ( array( 'tibetan', 'transliteration', 'sanskrit', 'english' ) as $field ) {
			if ( '' !== $entry[ $field ] && false !== mb_stripos( $entry[ $field ], $gq ) ) {
				return true;
			}
		}
		return false;
	} ) );
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
	<form method="get" action="" style="display:inline-flex; gap:8px; margin-right:16px;">
		<input type="search" name="gq" value="<?php echo esc_attr( $gq ); ?>" placeholder="<?php esc_attr_e( 'Search terms…', 'mts-base' ); ?>" style="padding:6px 12px;">
		<button type="submit" class="alphabet-btn"><?php esc_html_e( 'Search', 'mts-base' ); ?></button>
	</form>
	<a class="alphabet-btn<?php echo '' === $gq ? ' active' : ''; ?>" href="<?php echo esc_url( get_permalink() ); ?>"><?php esc_html_e( 'All', 'mts-base' ); ?></a>
	<?php foreach ( array_keys( $letters ) as $letter ) : ?>
		<a class="alphabet-btn<?php echo strtolower( $gq ) === strtolower( $letter ) ? ' active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'gq', $letter, get_permalink() ) ); ?>"><?php echo esc_html( $letter ); ?></a>
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
						<div class="term-english-text"><?php if ( ! empty( $entry['url'] ) ) : ?><a href="<?php echo esc_url( $entry['url'] ); ?>"><?php echo esc_html( $entry['english'] ); ?></a><?php else : ?><?php echo esc_html( $entry['english'] ); ?><?php endif; ?></div>
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
