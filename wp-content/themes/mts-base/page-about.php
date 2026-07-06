<?php
/**
 * Page About (portage mockup-1/about.html). Équipe = référentiel central,
 * partenaires = CPT partner, le reste = mts_section.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

mts_page_hero( 'about', 'hero-image-section', 'hero-image-text' );
mts_ornament_strip( 'about' );

foreach ( mts_section_posts( 'about' ) as $block ) :
	?>
	<section class="content-section">
		<div class="section-eyebrow"><?php echo esc_html( $block['meta']['eyebrow'] ); ?></div>
		<h2><?php echo esc_html( $block['title'] ); ?></h2>
		<?php echo wp_kses_post( $block['content'] ); ?>
	</section>
	<?php
endforeach;
?>

<section class="team-section">
	<h2><?php esc_html_e( 'Meet Our Translators', 'mts-base' ); ?></h2>
	<div class="team-grid">
		<?php foreach ( mts_get_team_members( 4 ) as $member ) : ?>
			<div class="team-member">
				<h3><?php echo esc_html( $member['name'] ); ?></h3>
				<div class="team-role"><?php echo esc_html( $member['role'] ); ?></div>
				<p><?php echo esc_html( $member['bio'] ); ?></p>
			</div>
		<?php endforeach; ?>
	</div>
</section>

<?php $partners = function_exists( 'mts_get_partners' ) ? mts_get_partners() : array(); ?>
<?php if ( $partners ) : ?>
<section class="partners-section">
	<h2><?php esc_html_e( 'Our Partners', 'mts-base' ); ?></h2>
	<div class="partners-list">
		<?php foreach ( $partners as $partner ) : ?>
			<div class="partner-card">
				<h3><?php echo esc_html( $partner['title'] ); ?></h3>
				<?php echo wp_kses_post( $partner['content'] ); ?>
			</div>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>

<section class="values-section">
	<h2><?php esc_html_e( 'Our Core Values', 'mts-base' ); ?></h2>
	<div class="values-grid">
		<?php foreach ( mts_section_posts( 'values' ) as $value ) : ?>
			<div class="value-item">
				<h3><?php echo esc_html( $value['title'] ); ?></h3>
				<?php echo wp_kses_post( $value['content'] ); ?>
			</div>
		<?php endforeach; ?>
	</div>
</section>

<?php get_footer(); ?>
