<?php
/**
 * Composants de template + échantillons de repli (copie mockup-1).
 * Règle : tout affichage passe par esc_html/esc_attr/esc_url/wp_kses.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Balises tolérées dans les titres décoratifs (hero, support).
 */
function mts_kses_title( $text ) {
	return wp_kses( $text, array(
		'br' => array(),
		'em' => array(),
	) );
}

/**
 * Bande ornementale : items du post mts_section zone `ornament` (une ligne
 * par libellé), séparés par ◆.
 */
function mts_ornament_strip( $page_key ) {
	$section = function_exists( 'mts_section_one' ) ? mts_section_one( 'ornament', $page_key ) : null;
	if ( ! $section ) {
		return;
	}
	$labels = array_filter( array_map( 'trim', explode( "\n", (string) $section['raw'] ) ) );
	if ( ! $labels ) {
		return;
	}
	echo '<div class="gold-divider"></div><div class="ornament-strip">';
	$first = true;
	foreach ( $labels as $label ) {
		if ( ! $first ) {
			echo '<span class="ornament">&#9670;</span>';
		}
		echo '<span>' . esc_html( $label ) . '</span>';
		$first = false;
	}
	echo '</div><div class="gold-divider"></div>';
}

/**
 * Hero de page intérieure (zone page-hero, slug = clé de page).
 */
function mts_page_hero( $page_key, $wrapper_class = 'page-hero', $inner_class = 'page-hero-content' ) {
	$hero = function_exists( 'mts_section_one' ) ? mts_section_one( 'page-hero', $page_key ) : null;
	if ( ! $hero ) {
		return;
	}
	printf(
		'<section class="%1$s"><div class="%2$s"><h1>%3$s</h1><p>%4$s</p></div></section>',
		esc_attr( $wrapper_class ),
		esc_attr( $inner_class ),
		esc_html( $hero['title'] ),
		esc_html( wp_strip_all_tags( $hero['raw'] ) )
	);
}

/**
 * Carte livre (catalogue / library).
 *
 * @param array $book { url, cover_class, tibetan, tag, lang, title, author, description }
 */
function mts_the_book_card( $book, $with_description = false ) {
	?>
	<a href="<?php echo esc_url( $book['url'] ); ?>" class="book-card">
		<div class="book-cover <?php echo esc_attr( $book['cover_class'] ); ?>">
			<?php if ( ! empty( $book['tibetan'] ) ) : ?>
				<span class="book-cover-tibetan"><?php echo esc_html( $book['tibetan'] ); ?></span>
			<?php endif; ?>
			<?php if ( ! empty( $book['tag'] ) ) : ?>
				<span class="book-tag"><?php echo esc_html( $book['tag'] ); ?></span>
			<?php endif; ?>
			<div class="book-free"><?php esc_html_e( 'Free Download', 'mts-base' ); ?></div>
		</div>
		<div class="book-info">
			<div class="book-lang"><?php echo esc_html( $book['lang'] ); ?></div>
			<div class="book-title"><?php echo esc_html( $book['title'] ); ?></div>
			<div class="book-author"><?php echo esc_html( $book['author'] ); ?></div>
			<?php if ( $with_description && ! empty( $book['description'] ) ) : ?>
				<div class="book-description"><?php echo esc_html( $book['description'] ); ?></div>
			<?php endif; ?>
		</div>
	</a>
	<?php
}

/**
 * Livres réels de la branche (produits Woo) — [] si Woo absent (Phase 4).
 */
function mts_get_branch_books( $limit = 8 ) {
	if ( ! post_type_exists( 'product' ) ) {
		return array();
	}
	$products = get_posts( array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
	) );
	$books = array();
	$index = 0;
	foreach ( $products as $product ) {
		$cats    = get_the_terms( $product->ID, 'product_cat' );
		$books[] = array(
			'url'         => get_permalink( $product ),
			'cover_class' => 'book-cover-' . ( ( $index++ % 8 ) + 1 ),
			'tibetan'     => '',
			'tag'         => ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : '',
			'lang'        => '',
			'title'       => $product->post_title,
			'author'      => '',
			'description' => get_the_excerpt( $product ),
		);
	}
	return $books;
}

/**
 * Échantillons mockup (repli tant que le catalogue n'est pas migré — Phase 4).
 */
function mts_sample_books() {
	return array(
		array( 'url' => '#', 'cover_class' => 'book-cover-1', 'tibetan' => 'ངེས་དོན།', 'tag' => __( 'Philosophy', 'mts-base' ), 'lang' => 'English', 'title' => 'Torch for the Definitive Meaning', 'author' => 'Jamgon Kongtrul Lodro Thaye', 'description' => __( 'A foundational guide to Buddhist practice and philosophical understanding.', 'mts-base' ) ),
		array( 'url' => '#', 'cover_class' => 'book-cover-2', 'tibetan' => 'ཕྱག་ཆེན།', 'tag' => __( 'Mahamudra', 'mts-base' ), 'lang' => 'English', 'title' => 'Distilling the Concomitant Quintessence', 'author' => 'Kagyu Lineage', 'description' => __( 'Essential guidance on transforming the mind through meditation.', 'mts-base' ) ),
		array( 'url' => '#', 'cover_class' => 'book-cover-3', 'tibetan' => 'བདེ་གཤེགས།', 'tag' => __( 'Commentary', 'mts-base' ), 'lang' => 'French', 'title' => 'Elucidating the Single Intention', 'author' => 'Drikung Kyobpa Jigten Sumgön', 'description' => __( 'The classic overview of the Buddhist path.', 'mts-base' ) ),
		array( 'url' => '#', 'cover_class' => 'book-cover-4', 'tibetan' => 'བློ་སྦྱོང།', 'tag' => __( 'Mind Training', 'mts-base' ), 'lang' => 'Norwegian', 'title' => 'Den Definitive Sannhets Fakkel', 'author' => 'Jamgon Kongtrul', 'description' => __( 'Direct instruction on recognizing the nature of mind.', 'mts-base' ) ),
	);
}

/**
 * Termes réels du glossaire de la branche, joints au référentiel central.
 */
function mts_get_branch_glossary_entries( $limit = 60 ) {
	if ( ! post_type_exists( 'translated_term' ) || ! function_exists( 'mts_get_tibetan_term' ) ) {
		return array();
	}
	$terms   = get_posts( array(
		'post_type'      => 'translated_term',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'orderby'        => 'title',
		'order'          => 'ASC',
	) );
	$entries = array();
	foreach ( $terms as $term_post ) {
		$central_id = (int) get_post_meta( $term_post->ID, 'central_tibetan_term_id', true );
		$central    = $central_id ? mts_get_tibetan_term( $central_id ) : null;
		$entries[]  = array(
			'tibetan'         => $central ? $central['tibetan'] : '',
			'transliteration' => $central && '' !== $central['wylie'] ? $central['wylie'] : ( $central ? $central['title'] : '' ),
			'sanskrit'        => $central ? $central['sanskrit'] : '',
			'english'         => $term_post->post_title,
			'explanation'     => $central && '' !== trim( $central['definition'] ) ? wp_strip_all_tags( $central['definition'] ) : (string) get_post_meta( $term_post->ID, 'term_notes', true ),
			'url'             => get_permalink( $term_post ),
			'central_id'      => $central_id,
		);
	}
	return $entries;
}

function mts_sample_glossary_entries() {
	return array(
		array( 'tibetan' => 'རིགས་པ་', 'transliteration' => 'rigpa', 'sanskrit' => 'vidyā', 'english' => 'Awareness / Knowledge', 'explanation' => __( 'In Buddhist philosophy, rigpa refers to the luminous, primordial awareness that is the fundamental nature of mind.', 'mts-base' ) ),
		array( 'tibetan' => 'སེམས་', 'transliteration' => 'sems', 'sanskrit' => 'citta', 'english' => 'Mind', 'explanation' => __( 'Sems is the Tibetan term for mind or consciousness, central to Buddhist psychology and meditation practice.', 'mts-base' ) ),
		array( 'tibetan' => 'སྙིང་རྗེ་', 'transliteration' => 'nyingjé', 'sanskrit' => 'karuṇā', 'english' => 'Compassion', 'explanation' => __( 'Nyingjé denotes deep empathy and the wish to free all beings from suffering.', 'mts-base' ) ),
	);
}

/**
 * Term-cards du bloc Knowledge Hub (home) : réels sinon échantillons.
 */
function mts_get_home_term_cards() {
	$entries = mts_get_branch_glossary_entries( 3 );
	if ( $entries ) {
		return $entries;
	}
	return array(
		array( 'tibetan' => 'ཕྱག་ཆེན།', 'english' => 'Mahamudra — "The Great Seal"', 'sanskrit' => 'mahāmudrā' ),
		array( 'tibetan' => 'བློ་སྦྱོང།', 'english' => 'Mind Training — Lojong', 'sanskrit' => 'cittabhāvanā' ),
		array( 'tibetan' => 'ངེས་དོན།', 'english' => 'Definitive meaning', 'sanskrit' => 'nītārtha' ),
	);
}

/**
 * Équipe : traducteurs du référentiel central, sinon échantillons mockup.
 */
function mts_get_team_members( $limit = 4 ) {
	if ( function_exists( 'mts_get_translators' ) ) {
		$translators = mts_get_translators( array( 'limit' => $limit ) );
		if ( $translators ) {
			$out = array();
			foreach ( $translators as $t ) {
				$out[] = array(
					'name' => $t['name'],
					'role' => __( 'Translator', 'mts-base' ),
					'bio'  => $t['bio_excerpt'],
				);
			}
			return $out;
		}
	}
	return array(
		array( 'name' => 'Eric Pema Kunsang', 'role' => __( 'Senior Translator', 'mts-base' ), 'bio' => __( 'A translator and author of over 25 Buddhist texts with forty years of experience in translation and practice.', 'mts-base' ) ),
		array( 'name' => 'Helena Blankleder', 'role' => __( 'Translator, Tibetan-German', 'mts-base' ), 'bio' => __( 'Fluent in Tibetan, German, English and Sanskrit, Helena specializes in philosophical treatises and commentaries.', 'mts-base' ) ),
		array( 'name' => 'Khenpo Tenpa Yungdrung', 'role' => __( 'Advisor, Traditional Scholar', 'mts-base' ), 'bio' => __( 'A traditional Tibetan Buddhist scholar trained in classical monastic philosophy.', 'mts-base' ) ),
		array( 'name' => 'Dr. Sarah Chen', 'role' => __( 'Translator, Tibetan-English', 'mts-base' ), 'bio' => __( 'A university lecturer in Buddhist studies with expertise in Tibetan language and literature.', 'mts-base' ) ),
	);
}

/**
 * Articles de repli (home + page articles) tant qu'aucun post n'existe.
 */
function mts_sample_articles() {
	return array(
		array( 'cat' => __( 'Biography', 'mts-base' ), 'title' => 'Marpa, The Translator Who Risked Everything', 'excerpt' => __( 'He crossed the Himalayas three times, risked his inheritance, his health, and his life to bring tantric transmissions from India to Tibet.', 'mts-base' ), 'meta' => __( '15 min read', 'mts-base' ), 'image' => get_template_directory_uri() . '/assets/images/marpa-lotsawa.png' ),
		array( 'cat' => __( 'Craft', 'mts-base' ), 'title' => 'Lost in Translation? Common Pitfalls in Rendering Dharma Texts', 'excerpt' => __( 'Exploring the philosophical and linguistic obstacles in translating Buddhist terminology.', 'mts-base' ), 'meta' => __( '10 min read', 'mts-base' ), 'image' => get_template_directory_uri() . '/assets/images/article-lost-in-translation.png' ),
		array( 'cat' => __( 'History', 'mts-base' ), 'title' => "Twenty-Five Centuries of Bringing the Buddha's Words Across Languages", 'excerpt' => __( 'How translation carried the dharma from oral tradition to the digital age.', 'mts-base' ), 'meta' => __( '12 min read', 'mts-base' ), 'image' => get_template_directory_uri() . '/assets/images/photo-nature.jpg' ),
	);
}

/**
 * Traductions à paraître (repli mockup — branché sur produits en Phase 4).
 */
function mts_get_forthcoming() {
	return array(
		array( 'badge' => __( 'Coming 2026', 'mts-base' ), 'title' => 'The Nalanda Texts', 'author' => 'Buddhist Philosophers', 'description' => __( "A comprehensive translation of Buddhist philosophical commentaries from India's greatest university monastery.", 'mts-base' ) ),
		array( 'badge' => __( 'Coming 2026', 'mts-base' ), 'title' => 'The Golden Rosary', 'author' => 'Padmasambhava', 'description' => __( 'Biographical narratives of enlightened masters and their spiritual achievements.', 'mts-base' ) ),
		array( 'badge' => __( 'Coming 2027', 'mts-base' ), 'title' => 'Commentary on the Bodhisattva Way of Life', 'author' => 'Shantideva', 'description' => __( 'A revered guide to the path of compassion and wisdom.', 'mts-base' ) ),
	);
}

/**
 * Libellé de la colonne « langue cible » du glossaire : autonyme de la
 * langue par défaut de la branche (option seedée), sinon English.
 */
function mts_glossary_target_language_label() {
	$slug = (string) get_option( 'mts_default_language', 'en' );
	$term = get_term_by( 'slug', $slug, 'mts_language' );
	return ( $term && ! is_wp_error( $term ) ) ? $term->name : __( 'English', 'mts-base' );
}

/**
 * Fallback de menu : les 5 pages standard.
 */
function mts_nav_fallback() {
	$items = array(
		'library'       => __( 'Library', 'mts-base' ),
		'about'         => __( 'About', 'mts-base' ),
		'knowledge-hub' => __( 'Knowledge Hub', 'mts-base' ),
		'articles'      => __( 'Articles', 'mts-base' ),
		'glossary'      => __( 'Glossary', 'mts-base' ),
	);
	echo '<ul class="nav-links">';
	foreach ( $items as $slug => $label ) {
		$is_current = is_page( $slug );
		printf(
			'<li><a href="%s"%s>%s</a></li>',
			esc_url( home_url( '/' . $slug . '/' ) ),
			$is_current ? ' class="active"' : '',
			esc_html( $label )
		);
	}
	echo '</ul>';
}
