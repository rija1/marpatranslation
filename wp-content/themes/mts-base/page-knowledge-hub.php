<?php
/**
 * Page Knowledge Hub (portage mockup-1/knowledge-hub.html) — cartes = CPT
 * hub_card (types pillar/resource).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

mts_page_hero( 'knowledge-hub' );
mts_ornament_strip( 'knowledge-hub' );

$intro = mts_section_one( 'hub-intro', 'knowledge-hub' );
?>

<section class="hub-cards-section">
	<div class="hub-intro">
		<?php if ( $intro ) : ?>
			<h2><?php echo esc_html( $intro['title'] ); ?></h2>
			<p><?php echo esc_html( wp_strip_all_tags( $intro['raw'] ) ); ?></p>
		<?php endif; ?>
	</div>

	<div class="hub-grid">
		<?php foreach ( mts_get_hub_cards( 'pillar' ) as $card ) : ?>
			<a href="<?php echo esc_url( $card['meta']['link_url'] ? home_url( $card['meta']['link_url'] ) : '#' ); ?>" class="hub-card">
				<?php if ( $card['meta']['card_icon'] ) : ?>
					<span class="hub-card-icon"><?php echo esc_html( $card['meta']['card_icon'] ); ?></span>
				<?php endif; ?>
				<h3><?php echo esc_html( $card['title'] ); ?></h3>
				<?php echo wp_kses_post( $card['content'] ); ?>
			</a>
		<?php endforeach; ?>
	</div>
</section>

<?php $resources_intro = mts_section_one( 'resources-intro', 'knowledge-hub' ); ?>
<section class="resources-section">
	<h2><?php echo esc_html( $resources_intro ? $resources_intro['title'] : __( 'Learning Resources', 'mts-base' ) ); ?></h2>
	<div class="resources-grid">
		<?php foreach ( mts_get_hub_cards( 'resource' ) as $card ) : ?>
			<div class="resource-item">
				<h3><?php echo esc_html( $card['title'] ); ?></h3>
				<?php echo wp_kses_post( $card['content'] ); ?>
			</div>
		<?php endforeach; ?>
	</div>
</section>

<?php get_footer(); ?>
