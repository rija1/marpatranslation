<?php
/**
 * CPT de contenu éditorial des branches (D14 : « tout éditable via CPT »)
 * + helper mts_section() + seeds (sections pré-remplies avec la copie du
 * mockup-1, pages, menu). Spec : doc/specs/theme-mts-base.md §4-§5.
 *
 * Les templates du thème mts-base ne manipulent JAMAIS de WP_Post pour ces
 * contenus : ils reçoivent des tableaux via mts_section_one()/mts_section_posts(),
 * avec repli sur les défauts du mockup (le site ne se vide jamais).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enregistre les CPT de contenu (appelé sur init branches + dans les seeds).
 */
function mts_register_content_types() {

	register_taxonomy( 'section_zone', array( 'mts_section' ), array(
		'labels'            => array( 'name' => __( 'Section Zones', 'mts-network' ) ),
		'public'            => false,
		'show_ui'           => true,
		'show_admin_column' => true,
		'hierarchical'      => false,
		'show_in_rest'      => true,
	) );

	register_taxonomy( 'hub_type', array( 'hub_card' ), array(
		'labels'            => array( 'name' => __( 'Hub Card Types', 'mts-network' ) ),
		'public'            => false,
		'show_ui'           => true,
		'show_admin_column' => true,
		'hierarchical'      => false,
		'show_in_rest'      => true,
	) );

	register_post_type( 'mts_section', array(
		'labels'              => array(
			'name'          => __( 'Site Sections', 'mts-network' ),
			'singular_name' => __( 'Site Section', 'mts-network' ),
		),
		'public'              => false,
		'show_ui'             => true,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'menu_icon'           => 'dashicons-layout',
		'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		'show_in_rest'        => true,
		'taxonomies'          => array( 'section_zone' ),
	) );

	register_post_type( 'partner', array(
		'labels'              => array(
			'name'          => __( 'Partners', 'mts-network' ),
			'singular_name' => __( 'Partner', 'mts-network' ),
		),
		'public'              => false,
		'show_ui'             => true,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'menu_icon'           => 'dashicons-groups',
		'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		'show_in_rest'        => true,
	) );

	register_post_type( 'hub_card', array(
		'labels'              => array(
			'name'          => __( 'Hub Cards', 'mts-network' ),
			'singular_name' => __( 'Hub Card', 'mts-network' ),
		),
		'public'              => false,
		'show_ui'             => true,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'menu_icon'           => 'dashicons-screenoptions',
		'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		'show_in_rest'        => true,
		'taxonomies'          => array( 'hub_type' ),
	) );
}

add_action( 'init', function () {
	if ( ! is_main_site() ) {
		mts_register_content_types();
	}
}, 11 );

add_action( 'init', function () {
	if ( is_main_site() ) {
		return;
	}
	$string_meta = array(
		'type'              => 'string',
		'single'            => true,
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
		'auth_callback'     => 'mts_meta_auth_callback',
		'show_in_rest'      => true,
	);
	foreach ( array( 'eyebrow', 'cta_label', 'cta_url', 'cta2_label', 'cta2_url', 'pull_quote', 'pull_quote_cite' ) as $key ) {
		register_post_meta( 'mts_section', $key, $string_meta );
	}
	register_post_meta( 'partner', 'partner_url', array_merge( $string_meta, array( 'sanitize_callback' => 'esc_url_raw' ) ) );
	register_post_meta( 'hub_card', 'card_icon', $string_meta );
	register_post_meta( 'hub_card', 'link_url', array_merge( $string_meta, array( 'sanitize_callback' => 'esc_url_raw' ) ) );
}, 12 );

// -------------------------------------------------------------------------
// Helpers de lecture (utilisés par le thème)
// -------------------------------------------------------------------------

/**
 * Aplatit un post de contenu en tableau pour les templates.
 */
function mts_section_flatten( $post ) {
	$meta = array();
	foreach ( array( 'eyebrow', 'cta_label', 'cta_url', 'cta2_label', 'cta2_url', 'pull_quote', 'pull_quote_cite', 'partner_url', 'card_icon', 'link_url' ) as $key ) {
		$meta[ $key ] = (string) get_post_meta( $post->ID, $key, true );
	}
	return array(
		'id'        => (int) $post->ID,
		'slug'      => $post->post_name,
		'title'     => $post->post_title,
		'content'   => apply_filters( 'the_content', $post->post_content ),
		'raw'       => $post->post_content,
		'thumb_url' => (string) get_the_post_thumbnail_url( $post, 'large' ),
		'meta'      => $meta,
	);
}

/**
 * Posts d'une zone, ordonnés (menu_order) — [] si zone vide.
 */
function mts_section_posts( $zone ) {
	$posts = get_posts( array(
		'post_type'      => 'mts_section',
		'post_status'    => 'publish',
		'posts_per_page' => 20,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
		'tax_query'      => array(
			array(
				'taxonomy' => 'section_zone',
				'field'    => 'slug',
				'terms'    => $zone,
			),
		),
	) );
	$out = array();
	foreach ( $posts as $post ) {
		$out[] = mts_section_flatten( $post );
	}
	// Repli : défauts du mockup si la zone est vide (le site ne casse jamais).
	if ( empty( $out ) ) {
		$defaults = mts_section_defaults();
		if ( isset( $defaults[ $zone ] ) ) {
			foreach ( $defaults[ $zone ] as $slug => $d ) {
				$out[] = array(
					'id'        => 0,
					'slug'      => $slug,
					'title'     => isset( $d['title'] ) ? $d['title'] : '',
					'content'   => isset( $d['content'] ) ? wpautop( $d['content'] ) : '',
					'raw'       => isset( $d['content'] ) ? $d['content'] : '',
					'thumb_url' => '',
					'meta'      => isset( $d['meta'] ) ? array_merge( array_fill_keys( array( 'eyebrow', 'cta_label', 'cta_url', 'cta2_label', 'cta2_url', 'pull_quote', 'pull_quote_cite', 'partner_url', 'card_icon', 'link_url' ), '' ), $d['meta'] ) : array_fill_keys( array( 'eyebrow', 'cta_label', 'cta_url', 'cta2_label', 'cta2_url', 'pull_quote', 'pull_quote_cite', 'partner_url', 'card_icon', 'link_url' ), '' ),
				);
			}
		}
	}
	return $out;
}

/**
 * Un post d'une zone (par slug, sinon le premier).
 */
function mts_section_one( $zone, $slug = null ) {
	$posts = mts_section_posts( $zone );
	if ( null !== $slug ) {
		foreach ( $posts as $post ) {
			if ( $post['slug'] === $slug ) {
				return $post;
			}
		}
	}
	return $posts ? $posts[0] : null;
}

/**
 * Partenaires (tableaux aplatis).
 */
function mts_get_partners() {
	$posts = get_posts( array(
		'post_type'      => 'partner',
		'post_status'    => 'publish',
		'posts_per_page' => 20,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	) );
	return array_map( 'mts_section_flatten', $posts );
}

/**
 * Hub cards par type (pillar|resource).
 */
function mts_get_hub_cards( $type ) {
	$posts = get_posts( array(
		'post_type'      => 'hub_card',
		'post_status'    => 'publish',
		'posts_per_page' => 20,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
		'tax_query'      => array(
			array(
				'taxonomy' => 'hub_type',
				'field'    => 'slug',
				'terms'    => $type,
			),
		),
	) );
	return array_map( 'mts_section_flatten', $posts );
}

// -------------------------------------------------------------------------
// Défauts (copie du mockup-1) — source unique pour le repli ET le seed
// -------------------------------------------------------------------------

function mts_section_defaults() {
	return array(
		'hero'          => array(
			'home' => array(
				'title'   => 'Preserving Wisdom<br><em>Through Language</em>',
				'content' => 'We translate classical Tibetan Buddhist texts into modern languages — free of charge, by practitioner-scholars, to the highest scholarly standard.',
				'meta'    => array(
					'eyebrow'    => 'Kagyu Buddhist Translation',
					'cta_label'  => 'Browse Translations',
					'cta_url'    => '/library/',
					'cta2_label' => 'Our Mission →',
					'cta2_url'   => '/about/',
				),
			),
		),
		'ornament'      => array(
			'home'          => array( 'title' => 'home', 'content' => "Translation\nPreservation\nFree Access\nPractitioner-Scholars" ),
			'about'         => array( 'title' => 'about', 'content' => "Founded 2008\nKagyu Lineage\nFree Translations" ),
			'library'       => array( 'title' => 'library', 'content' => "Translation\nPreservation\nFree Access" ),
			'glossary'      => array( 'title' => 'glossary', 'content' => "Terminology\nTranslation\nReference" ),
			'articles'      => array( 'title' => 'articles', 'content' => "Essays\nTeachings\nScholarship" ),
			'knowledge-hub' => array( 'title' => 'knowledge-hub', 'content' => "Learning\nTradition\nWisdom" ),
		),
		'principles'    => array(
			'cost-only'            => array( 'title' => 'Cost Only', 'content' => 'All translations are provided free of charge. We believe authentic dharma teachings should be accessible to everyone, regardless of means.' ),
			'practitioner-scholars' => array( 'title' => 'Practitioner-Scholars', 'content' => 'Our translators are practicing Buddhists first. Translation inseparable from contemplative realization is the founding principle of our lineage.' ),
			'scholarly-excellence' => array( 'title' => 'Scholarly Excellence', 'content' => 'Rigorous philological standards, commentary consultation, and peer review ensure translations that honor both the letter and spirit of the source.' ),
		),
		'mission'       => array(
			'home' => array(
				'title'   => 'The Lineage of Marpa the Translator',
				'content' => "Named for the great 11th-century translator Marpa Chökyi Lodrö, who crossed the Himalayas three times to bring tantric transmissions from India to Tibet, our Society continues that tradition in the modern world.\n\nWe focus on the Kagyu lineage: mahamudra manuals, mind-training texts, Gampopa's collected works, and the spiritual biographies of the great masters.",
				'meta'    => array(
					'eyebrow'         => 'Our Story',
					'pull_quote'      => '"Translation is not merely scholarship — it is an act of transmission across time and language."',
					'pull_quote_cite' => '— Founding Vision',
					'cta_label'       => 'Read our full story →',
					'cta_url'         => '/about/',
				),
			),
		),
		'catalog-intro' => array(
			'home' => array(
				'title'   => 'Published Translations',
				'content' => 'All texts free to read and download. Offered at cost of printing if you wish a physical copy.',
				'meta'    => array( 'eyebrow' => 'Translation Library', 'cta_label' => 'View All Translations', 'cta_url' => '/library/' ),
			),
		),
		'knowledge'     => array(
			'home' => array(
				'title'   => 'The MTS Knowledge Hub',
				'content' => 'A living reference database connecting Tibetan source texts, their translations across languages, the translators who rendered them, and the key terminology in each tradition.',
				'meta'    => array( 'eyebrow' => 'Scholarly Resource', 'cta_label' => 'Enter the Knowledge Hub →', 'cta_url' => '/knowledge-hub/' ),
			),
		),
		'support'       => array(
			'home' => array(
				'title'   => 'Help Preserve the Wisdom<br>of the Kagyu Lineage',
				'content' => 'All our translations are offered free of charge. This is possible only through the generosity of donors who recognize the value of making authentic Buddhist teachings available in modern languages.',
				'meta'    => array( 'eyebrow' => 'Support Our Work', 'cta_label' => 'Make a Donation', 'cta_url' => '#' ),
			),
		),
		'page-hero'     => array(
			'about'         => array( 'title' => 'About the Marpa Translation Society', 'content' => 'Dedicating ourselves to bringing authentic Buddhist teachings to the world through rigorous, practitioner-led translation.' ),
			'library'       => array( 'title' => 'The Translation Library', 'content' => 'Explore our complete catalog of Buddhist texts translated from classical Tibetan into modern languages. All works are available free of charge to support access to authentic dharma teachings worldwide.' ),
			'glossary'      => array( 'title' => 'Tibetan-Sanskrit-English Glossary', 'content' => 'A comprehensive reference guide to essential Buddhist terminology across multiple languages and traditions.' ),
			'articles'      => array( 'title' => 'Articles & Essays', 'content' => 'Insights on Buddhist translation, practice, philosophy, and the Kagyu lineage from our translators and scholars.' ),
			'knowledge-hub' => array( 'title' => 'Knowledge Hub', 'content' => 'Explore our comprehensive resources for learning Buddhist philosophy, practice, terminology, and lineage history.' ),
		),
		'about'         => array(
			'our-mission' => array(
				'title'   => 'Translating Wisdom for the Modern World',
				'content' => "The Marpa Translation Society is dedicated to preserving and sharing the teachings of the Kagyu Buddhist lineage through high-quality translations into modern languages. We believe that authentic dharma instruction should be accessible to everyone, regardless of their background or means.\n\nAll of our translations are provided completely free of charge. This commitment reflects the Buddhist principle that wisdom is not a commodity but a gift meant for all beings. We sustain this work through the generosity of donors who recognize the value of making these teachings available worldwide.",
				'meta'    => array( 'eyebrow' => 'Our Mission' ),
			),
			'our-history' => array(
				'title'   => 'The Lineage of Marpa the Translator',
				'content' => "Founded in 2008, the Marpa Translation Society takes its name from Marpa Chökyi Lodrö, the great 11th-century Tibetan translator who crossed the Himalayas three times to bring the most profound tantric teachings from India to Tibet. Like Marpa, we are committed to the sacred work of transmitting living wisdom across cultural and linguistic boundaries.\n\nFor over a thousand years, the Kagyu lineage has preserved the teachings of mahamudra—the \"great seal\" of the nature of mind—through an unbroken chain of realized masters and devoted students. Our translators represent this living tradition, working as practicing Buddhists first and scholars second to ensure that each word carries not merely intellectual meaning but the resonance of authentic spiritual realization.",
				'meta'    => array( 'eyebrow' => 'Our History' ),
			),
		),
		'values'        => array(
			'accuracy'      => array( 'title' => 'Accuracy', 'content' => 'We apply rigorous philological standards, consult multiple historical manuscripts, and engage in extensive peer review to ensure translations honor both the letter and spirit of the source.' ),
			'accessibility' => array( 'title' => 'Accessibility', 'content' => 'All translations are provided free of charge and presented in clear, modern language accessible to contemporary readers without sacrificing depth or sophistication.' ),
			'authenticity'  => array( 'title' => 'Authenticity', 'content' => 'Our translators are practicing Buddhists whose work emerges from lived experience with the teachings. Translation inseparable from realization is the founding principle of our lineage.' ),
		),
		'forthcoming-intro' => array(
			'library' => array(
				'title'   => 'Forthcoming Translations',
				'content' => 'These texts are currently in preparation and will be available soon. Subscribe to our newsletter for updates.',
			),
		),
		'hub-intro'     => array(
			'knowledge-hub' => array(
				'title'   => 'Five Pillars of Understanding',
				'content' => 'Navigate Buddhist teachings through essays, terminology, recommended resources, lineage history, and structured study programs.',
			),
		),
		'resources-intro' => array(
			'knowledge-hub' => array( 'title' => 'Learning Resources', 'content' => '' ),
		),
		'util-bar'      => array(
			'notice' => array( 'title' => 'notice', 'content' => 'All translations provided free of charge ◆ Sustained by your generosity' ),
		),
	);
}

function mts_partner_defaults() {
	return array(
		array( 'title' => 'Pullahari Monastery', 'content' => "Nepal's premier center for Kagyu Buddhist studies and practice, providing scholarly guidance and consultation on difficult philosophical passages." ),
		array( 'title' => 'Rangjung Yeshe Publications', 'content' => "One of the world's leading publishers of Buddhist texts, supporting our work through editorial expertise and distribution worldwide." ),
		array( 'title' => 'KTD Monastery', 'content' => 'A major Kagyu center in North America offering support, guidance, and access to lineage teachers who help us maintain the authenticity of our work.' ),
	);
}

function mts_hub_card_defaults() {
	return array(
		'pillar'   => array(
			array( 'title' => 'Articles', 'icon' => '📝', 'url' => '/articles/', 'content' => 'Essays on translation, practice, and Buddhist philosophy from our translators and scholars.' ),
			array( 'title' => 'Glossary', 'icon' => '📖', 'url' => '/glossary/', 'content' => 'Tibetan, Sanskrit, and English terminology with definitions and explanations of key Buddhist concepts.' ),
			array( 'title' => 'Resources', 'icon' => '🌟', 'url' => '', 'content' => 'Recommended books, teachers, meditation centers, and study materials to deepen your practice.' ),
			array( 'title' => 'Lineage', 'icon' => '🏔️', 'url' => '', 'content' => 'The history of Kagyu transmission from Marpa through the centuries to present-day masters.' ),
			array( 'title' => 'Study Guides', 'icon' => '📚', 'url' => '', 'content' => 'Structured learning programs designed to guide your engagement with specific texts and practices.' ),
		),
		'resource' => array(
			array( 'title' => 'Recommended Books', 'icon' => '', 'url' => '', 'content' => 'Beyond our own translations, we recommend foundational Buddhist texts by accomplished teachers and scholars. These classics provide context and depth for understanding the Kagyu path.' ),
			array( 'title' => 'Meditation Centers', 'icon' => '', 'url' => '', 'content' => 'Throughout the world, authentic Kagyu centers offer instruction and community for practitioners. We maintain a curated list of reputable centers where you can study and practice with experienced teachers.' ),
			array( 'title' => 'Online Learning', 'icon' => '', 'url' => '', 'content' => 'Many Kagyu masters now offer teachings online, making authentic instruction accessible regardless of location. Explore recorded teachings and live virtual classes from accomplished teachers.' ),
			array( 'title' => 'Lineage History', 'icon' => '', 'url' => '', 'content' => 'Understanding the chain of transmission that brought these teachings to our time enriches practice. Explore the lives of the great masters whose realization made these teachings possible.' ),
		),
	);
}

// -------------------------------------------------------------------------
// Seeds
// -------------------------------------------------------------------------

add_action( 'init', 'mts_run_content_seeds', 25 );

function mts_run_content_seeds() {
	mts_seed_sections();
	mts_seed_pages();
	mts_seed_menus();
}

/**
 * Sections/partenaires/hub cards pré-remplis avec la copie du mockup —
 * l'admin édite du contenu existant plutôt que de partir de zéro.
 */
function mts_seed_sections() {
	if ( get_site_option( 'mts_content_seed_v1' ) ) {
		return;
	}

	foreach ( mts_get_branch_sites() as $site ) {
		switch_to_blog( (int) $site->blog_id );
		mts_register_branch_types();
		mts_register_content_types();

		foreach ( mts_section_defaults() as $zone => $entries ) {
			if ( ! term_exists( $zone, 'section_zone' ) ) {
				wp_insert_term( $zone, 'section_zone', array( 'slug' => $zone ) );
			}
			$order = 0;
			foreach ( $entries as $slug => $d ) {
				// L'ordre avance même sur les posts déjà présents (reprise
				// partielle du seed — constat majeur revue Phase 2).
				$menu_order = $order++;
				$existing   = get_posts( array(
					'post_type'   => 'mts_section',
					'name'        => $slug,
					'post_status' => 'any',
					'tax_query'   => array( array( 'taxonomy' => 'section_zone', 'field' => 'slug', 'terms' => $zone ) ),
					'fields'      => 'ids',
				) );
				if ( $existing ) {
					continue;
				}
				$post_id = wp_insert_post( array(
					'post_type'    => 'mts_section',
					'post_status'  => 'publish',
					'post_title'   => isset( $d['title'] ) ? $d['title'] : $slug,
					'post_name'    => $slug,
					'post_content' => isset( $d['content'] ) ? $d['content'] : '',
					'menu_order'   => $menu_order,
				) );
				if ( $post_id && ! is_wp_error( $post_id ) ) {
					wp_set_object_terms( $post_id, $zone, 'section_zone' );
					if ( isset( $d['meta'] ) ) {
						foreach ( $d['meta'] as $key => $value ) {
							update_post_meta( $post_id, $key, $value );
						}
					}
				}
			}
		}

		$order = 0;
		foreach ( mts_partner_defaults() as $d ) {
			$menu_order = $order++;
			if ( get_page_by_path( sanitize_title( $d['title'] ), OBJECT, 'partner' ) ) {
				continue;
			}
			$post_id = wp_insert_post( array(
				'post_type'    => 'partner',
				'post_status'  => 'publish',
				'post_title'   => $d['title'],
				'post_content' => $d['content'],
				'menu_order'   => $menu_order,
			) );
			if ( ! $post_id || is_wp_error( $post_id ) ) {
				continue;
			}
		}

		foreach ( mts_hub_card_defaults() as $type => $cards ) {
			if ( ! term_exists( $type, 'hub_type' ) ) {
				wp_insert_term( $type, 'hub_type', array( 'slug' => $type ) );
			}
			$order = 0;
			foreach ( $cards as $d ) {
				$menu_order = $order++;
				if ( get_page_by_path( sanitize_title( $d['title'] ), OBJECT, 'hub_card' ) ) {
					continue;
				}
				$post_id = wp_insert_post( array(
					'post_type'    => 'hub_card',
					'post_status'  => 'publish',
					'post_title'   => $d['title'],
					'post_content' => $d['content'],
					'menu_order'   => $menu_order,
				) );
				if ( $post_id && ! is_wp_error( $post_id ) ) {
					wp_set_object_terms( $post_id, $type, 'hub_type' );
					update_post_meta( $post_id, 'card_icon', $d['icon'] );
					update_post_meta( $post_id, 'link_url', $d['url'] );
				}
			}
		}

		restore_current_blog();
	}

	update_site_option( 'mts_content_seed_v1', current_time( 'mysql' ) );
}

/**
 * Pages standard des branches (les templates s'appliquent par slug :
 * page-about.php, etc.) + réglages front page / posts page.
 */
function mts_seed_pages() {
	if ( get_site_option( 'mts_pages_seed_v1' ) ) {
		return;
	}

	$pages = array(
		'home'          => 'Home',
		'about'         => 'About',
		'library'       => 'Library',
		'glossary'      => 'Glossary',
		'articles'      => 'Articles',
		'knowledge-hub' => 'Knowledge Hub',
	);

	foreach ( mts_get_branch_sites() as $site ) {
		switch_to_blog( (int) $site->blog_id );

		$ids = array();
		foreach ( $pages as $slug => $title ) {
			$page = get_page_by_path( $slug );
			if ( ! $page ) {
				$page_id = wp_insert_post( array(
					'post_type'   => 'page',
					'post_status' => 'publish',
					'post_title'  => $title,
					'post_name'   => $slug,
				) );
			} else {
				$page_id = $page->ID;
			}
			$ids[ $slug ] = (int) $page_id;
		}

		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['home'] );
		update_option( 'page_for_posts', $ids['articles'] );

		restore_current_blog();
	}

	update_site_option( 'mts_pages_seed_v1', current_time( 'mysql' ) );
}

/**
 * Menu primaire des branches (5 entrées du mockup).
 */
function mts_seed_menus() {
	if ( get_site_option( 'mts_menus_seed_v1' ) ) {
		return;
	}

	$items = array(
		'Library'       => '/library/',
		'About'         => '/about/',
		'Knowledge Hub' => '/knowledge-hub/',
		'Articles'      => '/articles/',
		'Glossary'      => '/glossary/',
	);

	foreach ( mts_get_branch_sites() as $site ) {
		switch_to_blog( (int) $site->blog_id );

		$menu = wp_get_nav_menu_object( 'Primary' );
		if ( ! $menu ) {
			$menu_id = wp_create_nav_menu( 'Primary' );
			foreach ( $items as $label => $path ) {
				wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-title'  => $label,
					'menu-item-url'    => home_url( $path ),
					'menu-item-type'   => 'custom',
					'menu-item-status' => 'publish',
				) );
			}
		} else {
			$menu_id = $menu->term_id;
		}

		// Écrit dans les mods du thème mts-branch explicitement : les
		// emplacements de menu sont stockés PAR thème, et ce seed peut
		// tourner avant l'activation du thème sur la branche.
		$mods = get_option( 'theme_mods_mts-branch', array() );
		if ( ! is_array( $mods ) ) {
			$mods = array();
		}
		$mods['nav_menu_locations']            = isset( $mods['nav_menu_locations'] ) && is_array( $mods['nav_menu_locations'] ) ? $mods['nav_menu_locations'] : array();
		$mods['nav_menu_locations']['primary'] = (int) $menu_id;
		update_option( 'theme_mods_mts-branch', $mods );

		restore_current_blog();
	}

	update_site_option( 'mts_menus_seed_v1', current_time( 'mysql' ) );
}
