<?php
/**
 * TEMPORARY SCRIPT: Import Glossary Entries
 * Run this once to import glossary entries with automatic term creation
 * 
 * Usage: Visit http://marpatranslation.local/import-glossary-entries.php
 */

// Load WordPress
require_once('wp-load.php');

// Check if user has permissions
if (!current_user_can('manage_options')) {
    die('<h1>Access Denied</h1><p>You must be logged in as an administrator to run this import.</p>');
}

// Raw glossary content
$glossary_content = "Absolute (Skt. paramārtha) – Tib. dam pa'i don: The ultimate nature of reality, beyond all conceptual constructions and conventional designations.

Afflictive emotions (Skt. kleśa) – Tib. nyon mongs: Mental factors that disturb the mind and cause suffering, including attachment, hatred, ignorance, pride, and jealousy.

Aggregates (Skt. skandha) – Tib. phung po: The five components that constitute a sentient being according to Buddhist psychology: form, sensation, perception, mental formations, and consciousness.

Arhat (Skt.) – Tib. dgra bcom pa: One who has achieved liberation from samsara through the path of individual liberation, having eliminated all afflictive emotions.

Awakening mind (Skt. bodhicitta) – Tib. byang chub kyi sems: The altruistic intention to achieve enlightenment for the benefit of all sentient beings.

Bardo (Tib.) – Skt. antarābhava: The intermediate state between death and rebirth, lasting up to forty-nine days according to traditional teachings.

Bodhisattva (Skt.) – Tib. byang chub sems dpa': A being who has generated the awakening mind and is dedicated to achieving enlightenment for the benefit of all sentient beings.

Buddha (Skt.) – Tib. sangs rgyas: One who has achieved perfect enlightenment, having eliminated all obscurations and realized all knowledge.

Buddhahood (Skt. buddhatva) – Tib. sangs rgyas kyi go 'phang: The state of perfect enlightenment characterized by the elimination of all obscurations and the realization of all knowledge.

Calm abiding (Skt. śamatha) – Tib. zhi gnas: A meditative state characterized by mental stability, clarity, and bliss, achieved through sustained concentration.

Cyclic existence (Skt. saṃsāra) – Tib. 'khor ba: The continuous cycle of birth, death, and rebirth driven by karma and afflictive emotions.

Demon (Skt. māra) – Tib. bdud: Forces or influences that obstruct spiritual progress, traditionally classified into four types: the demon of the aggregates, afflictive emotions, death, and the divine child.

Desire realm (Skt. kāmadhātu) – Tib. 'dod khams: The lowest of the three realms of existence, characterized by beings driven primarily by sensual desires.

Form realm (Skt. rūpadhātu) – Tib. gzugs khams: The middle realm of existence, inhabited by beings who have transcended gross sensual desires but still possess subtle forms.

Formless realm (Skt. arūpadhātu) – Tib. gzugs med khams: The highest realm of samsaric existence, characterized by beings who have transcended all form and dwell in states of pure consciousness.

Empowerment (Skt. abhiṣeka) – Tib. dbang bskur: A tantric initiation ritual that authorizes a practitioner to engage in specific tantric practices and introduces them to the mandala of a particular deity.

Dhāraṇī (Skt.) – Tib. gzungs: Sacred formulas or mantras that are believed to contain and preserve spiritual teachings and powers.

Dharma (Skt.) – Tib. chos: The teachings of the Buddha; more broadly, the truth or natural law that governs existence.

Dharmakāya (Skt.) – Tib. chos sku: The truth body of a Buddha, representing the ultimate nature of enlightened mind, beyond form and conceptualization.

Dharma protector (Skt. dharmapāla) – Tib. chos skyong: Protective deities who guard the Buddhist teachings and practitioners from obstacles and negative influences.

Eight worldly dharmas (Skt. aṣṭalokadharma) – Tib. 'jig rten chos brgyad: Four pairs of worldly concerns that bind beings to samsara: gain and loss, pleasure and pain, praise and blame, fame and disgrace.

Emanation body (Skt. nirmāṇakāya) – Tib. sprul sku: The form body of a Buddha that appears in the world to teach sentient beings, adapted to their needs and capacities.

Enjoyment body (Skt. sambhogakāya) – Tib. longs spyod rdzogs pa'i sku: The subtle form body of a Buddha that appears to advanced bodhisattvas in pure realms.

Five aggregates (Skt. pañcaskandha) – Tib. phung po lnga: The five components of a sentient being: form, sensation, perception, mental formations, and consciousness.

Five wisdoms (Skt. pañcajñāna) – Tib. ye shes lnga: The five aspects of enlightened awareness: mirror-like wisdom, wisdom of equality, discriminating wisdom, all-accomplishing wisdom, and dharmadhātu wisdom.

Four classes of tantra (Skt. caturyogatantra) – Tib. rgyud sde bzhi: The fourfold classification of tantric teachings: action tantra, performance tantra, yoga tantra, and highest yoga tantra.

Four continents (Skt. caturdvīpa) – Tib. gling bzhi: In Buddhist cosmology, the four continents surrounding Mount Meru: Jambudvīpa (south), Pūrvavideha (east), Aparagodānīya (west), and Uttarakuru (north).

Gampopa (Tib. sgam po pa): A renowned Tibetan master (1079-1153) who synthesized the Kadampa teachings with the Mahāmudrā instructions, founding the Dagpo Kagyu lineage.

Generation stage (Skt. utpattikrama) – Tib. bskyed rim: The first of two main phases in highest yoga tantra practice, involving the visualization of oneself as a deity and one's environment as a mandala.

Great Vehicle (Skt. mahāyāna) – Tib. theg pa chen po: The Buddhist path emphasizing the bodhisattva ideal and the goal of achieving enlightenment for the benefit of all sentient beings.

Great Seal (Skt. mahāmudrā) – Tib. phyag rgya chen po: In the Kagyu tradition, the ultimate nature of mind and the direct realization of its empty, luminous essence.

Hearer (Skt. śrāvaka) – Tib. nyan thos: A practitioner who follows the path of individual liberation, seeking to become an arhat through hearing and practicing the Buddha's teachings.

Higher realms (Skt. sugati) – Tib. mtho ris: The three upper realms of existence: humans, demigods, and gods, characterized by relatively favorable conditions for spiritual practice.

Superior insight (Skt. vipaśyanā) – Tib. lhag mthong: Analytical meditation that penetrates the ultimate nature of phenomena, often combined with calm abiding to achieve union of concentration and wisdom.

Jātaka (Skt.) – Tib. skyes rabs: Stories of the Buddha's previous lives, illustrating the development of virtues and the gradual path to enlightenment through countless lifetimes.

Kadampa (Tib. bka' gdams pa): A Tibetan Buddhist school founded by Atiśa and his disciple Dromtönpa, emphasizing gradual training, ethical conduct, and the synthesis of sutra and tantra teachings.

Kagyu (Tib. bka' brgyud): One of the four major schools of Tibetan Buddhism, tracing its lineage through Marpa, Milarepa, and Gampopa, known for its emphasis on meditation and Mahāmudrā teachings.

Karmapa (Tib. kar ma pa): The head of the Karma Kagyu lineage, recognized as one of the earliest tulku lineages in Tibetan Buddhism, beginning with the first Karmapa, Düsum Khyenpa.

Lama (Tib. bla ma) – Skt. guru: A spiritual teacher or guide, literally meaning 'none higher,' who provides instruction and guidance on the Buddhist path.

Lay practitioner (Skt. upāsaka) – Tib. dge bsnyen: A layperson who has taken refuge in the Three Jewels and committed to observing the five basic precepts of Buddhist ethical conduct.

Lesser Vehicle (Skt. hīnayāna) – Tib. theg dman: A term used in Mahāyāna literature to refer to earlier Buddhist schools that emphasize individual liberation rather than universal enlightenment.

Liberation (Skt. mokṣa) – Tib. thar pa: Freedom from the cycle of suffering and rebirth, achieved through the elimination of ignorance and the realization of ultimate truth.

Lord of Death (Skt. mārakāya) – Tib. gshin rje: Yama, the deity who presides over death and the intermediate state, often depicted as a wrathful protector in Tibetan Buddhism.

Lower realms (Skt. durgati) – Tib. ngan song: The three lower realms of existence: hell beings, hungry ghosts, and animals, characterized by intense suffering and limited opportunities for spiritual practice.

Marpa (Tib. mar pa): The great translator (1012-1097) who brought many tantric teachings from India to Tibet, particularly in the Kagyu lineage, and was the teacher of Milarepa.

Meditative concentration (Skt. samādhi) – Tib. ting nge 'dzin: One-pointed mental absorption achieved through sustained meditation practice, ranging from basic concentration to profound states of realization.

Afflictive emotions (Skt. kleśa) – Tib. nyon mongs: Mental factors that disturb the mind's natural clarity and lead to suffering, including attachment, hatred, ignorance, pride, and jealousy.

Merit (Skt. puṇya) – Tib. bsod nams: Positive karmic energy accumulated through virtuous actions, speech, and thoughts, which contributes to favorable conditions and spiritual progress.

Milarepa (Tib. mi la ras pa): The great Tibetan yogi (1040-1123), student of Marpa and teacher of Gampopa, renowned for his intense practice, realization songs, and embodiment of the tantric path.

Non-Buddhist (Skt. tīrthika) – Tib. mu stegs pa: Practitioners of non-Buddhist spiritual traditions, often referred to in Buddhist texts when distinguishing Buddhist views from other philosophical systems.

Moral conduct (Skt. śīla) – Tib. tshul khrims: Ethical discipline that forms the foundation of Buddhist practice, involving the restraint from harmful actions and the cultivation of beneficial ones.

Mount Meru (Skt. meru) – Tib. ri rab lhun po: In Buddhist cosmology, the sacred mountain at the center of the world system, surrounded by oceans and continents.

Nāga (Skt.) – Tib. klu: Serpent-like beings in Buddhist cosmology, often associated with water and underground realms, who can be either helpful or harmful to humans.

Nāropa (Skt.) – Tib. na ro pa: The great Indian mahāsiddha (1016-1100), student of Tilopa and teacher of Marpa, who transmitted many important tantric teachings to Tibet.

New schools (Tib. gsar ma): The schools of Tibetan Buddhism that base their tantric transmissions on texts translated after the 11th-century revival, including Kagyu, Sakya, and Gelug.

Perfection of Wisdom (Skt. prajñāpāramitā) – Tib. shes rab kyi pha rol tu phyin pa: Both a genre of Mahāyāna literature and the transcendent wisdom that realizes the emptiness of inherent existence.

Pith instructions (Tib. man ngag) – Skt. upadeśa: Concise, practical teachings that capture the essence of spiritual practice, often transmitted orally from teacher to student.

Personal deity (Skt. iṣṭadevatā) – Tib. yi dam: In tantric practice, a specific Buddha or deity with whom the practitioner identifies through visualization and mantra recitation.

Awareness (Skt. vidyā) – Tib. rig pa: Pure consciousness or knowing awareness, particularly emphasized in Dzogchen teachings as the natural state of mind.

Root guru (Skt. mūlaguru) – Tib. rtsa ba'i bla ma: The primary spiritual teacher who introduces a student to the ultimate nature of mind and provides the most profound instructions.

Samaya (Skt.) – Tib. dam tshig: Sacred commitments or vows taken in tantric practice that maintain the spiritual connection between teacher and student and protect the integrity of the teachings.

Sangha (Skt.) – Tib. dge 'dun: The community of Buddhist practitioners, particularly referring to those who have taken monastic vows or achieved high realizations.

Secret mantra (Skt. guhyamantra) – Tib. gsang sngags: The tantric teachings of Buddhism, characterized by the use of mantras, visualizations, and esoteric practices to achieve rapid enlightenment.

Seven-branch practice (Skt. saptāṅga) – Tib. yan lag bdun pa: A comprehensive purification and merit-accumulation practice consisting of prostration, offering, confession, rejoicing, requesting teachings, requesting longevity, and dedication.

Six perfections (Skt. ṣaṭpāramitā) – Tib. pha rol tu phyin pa drug: The six transcendent practices of a bodhisattva: generosity, ethics, patience, joyous effort, meditative concentration, and wisdom.

Ḍākinī (Skt.) – Tib. mkha' 'gro ma: Female wisdom beings in tantric Buddhism who embody enlightened feminine energy and often serve as spiritual guides and protectors.

Solitary realizer (Skt. pratyekabuddha) – Tib. rang sangs rgyas: An individual who achieves personal liberation through understanding dependent origination without relying on a teacher in their final life.

Stupa (Skt.) – Tib. mchod rten: A Buddhist monument representing the enlightened mind of a Buddha, often containing relics and serving as a focus for circumambulation and offerings.

Sukhāvatī (Skt.) – Tib. bde ba can: The pure land of Amitābha Buddha, characterized by perfect conditions for spiritual practice and the swift attainment of enlightenment.

Sutra collection (Skt. sūtrapiṭaka) – Tib. mdo sde: One of the three divisions of the Buddhist canon, containing the discourses attributed to the Buddha and his close disciples.

Tantra (Skt.) – Tib. rgyud: The esoteric teachings and practices of Vajrayāna Buddhism, emphasizing transformation rather than renunciation and the view of inherent purity.

Ten directions (Skt. daśadiś) – Tib. phyogs bcu: The spatial directions in Buddhist cosmology: the four cardinal directions, four intermediate directions, zenith, and nadir, representing the totality of space.

Blessed One (Skt. bhagavat) – Tib. bcom ldan 'das: An epithet of the Buddha, indicating one who has overcome all obstacles, possesses all excellent qualities, and has transcended the world.

Three Jewels (Skt. triratna) – Tib. dkon mchog gsum: The three objects of refuge in Buddhism: the Buddha, Dharma, and Sangha.

Three bodies (Skt. trikāya) – Tib. sku gsum: The three aspects of full enlightenment: dharmakāya (truth body), sambhogakāya (enjoyment body), and nirmāṇakāya (emanation body).

Three roots (Tib. rtsa gsum): In tantric practice, the three sources of blessings and accomplishment: the guru (source of blessings), personal deity (source of accomplishments), and ḍākinī (source of activities).

Three spheres (Skt. trimaṇḍala) – Tib. 'khor gsum: In the context of virtuous actions, the three aspects to be understood as empty: the agent, action, and object of the action.

Three vows (Skt. trisamvara) – Tib. sdom pa gsum: The three levels of Buddhist vows: individual liberation (prātimokṣa), bodhisattva, and tantric vows.

Thus-gone one (Skt. tathāgata) – Tib. de bzhin gshegs pa: An epithet of the Buddha, meaning 'one who has gone thus' or 'one who has come thus,' indicating complete realization of suchness.

Tailopa (Skt.) – Tib. tai lo pa: The great Indian mahāsiddha who received the Mahāmudrā teachings directly from Vajradhara and transmitted them to Nāropa, establishing the Kagyu lineage.

Nirvāṇa (Skt.) – Tib. mya ngan las 'das pa: The state of liberation from suffering and the cycle of rebirth, characterized by the cessation of all afflictive emotions and karmic imprints.

Treatise (Skt. śāstra) – Tib. bstan bcos: Commentarial literature written by great masters to explain and elaborate upon the Buddha's teachings, often presenting systematic expositions of particular topics.

Trichiliocosm (Skt. trisāhasramahāsāhasralokadhātu) – Tib. stong gsum 'jig rten gyi khams: In Buddhist cosmology, a vast universe system containing a billion world systems, representing the sphere of activity of a Buddha.

Two accumulations (Skt. dvisambhāra) – Tib. tshogs gnyis: The accumulations of merit and wisdom, which together constitute the complete path to enlightenment and the causes for a Buddha's form and truth bodies respectively.

Two obscurations (Skt. dvāvaraṇa) – Tib. sgrib pa gnyis: The two types of mental obscurations that prevent enlightenment: afflictive obscurations (preventing liberation) and cognitive obscurations (preventing omniscience).

Dharmadhātu (Skt.) – Tib. chos dbyings: The ultimate nature of reality, the sphere of all phenomena, often synonymous with emptiness or the absolute truth.

Vajradhara (Skt.) – Tib. rdo rje 'chang: The primordial Buddha in tantric Buddhism, representing the dharmakāya aspect of enlightenment and the source of all tantric teachings.

Vajrasattva (Skt.) – Tib. rdo rje sems dpa': A tantric deity associated with purification practices, whose hundred-syllable mantra is widely used for removing negative karma and ritual violations.

Vajravārāhī (Skt.) – Tib. rdo rje phag mo: A prominent female deity in highest yoga tantra, representing the wisdom aspect of enlightenment and often practiced in conjunction with Cakrasaṃvara.

Victorious one (Skt. jina) – Tib. rgyal ba: An epithet of the Buddha, meaning 'conqueror' or 'victorious one,' indicating victory over all internal enemies such as ignorance and afflictive emotions.

Vinaya (Skt.) – Tib. 'dul ba: The code of monastic discipline that forms one of the three divisions of the Buddhist canon, containing rules and guidelines for monastic conduct.

Wrong view (Skt. mithyādṛṣṭi) – Tib. log par lta ba: Philosophical or spiritual views that contradict the fundamental principles of Buddhism, particularly those that deny karma, rebirth, or the possibility of liberation.";

function parse_glossary_entries($content) {
    $lines = explode("\n", trim($content));
    $entries = array();
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) {
            continue;
        }
        
        // Parse format: "Term (Skt. sanskrit) – Tib. tibetan: Definition"
        // or "Term (Tib.) – Skt. sanskrit: Definition"  
        // or "Term: Definition"
        
        $entry = array(
            'english_term' => '',
            'sanskrit_term' => '',
            'tibetan_term' => '',
            'definition' => ''
        );
        
        // Split at the colon to separate term info from definition
        $parts = explode(':', $line, 2);
        if (count($parts) != 2) {
            continue; // Skip malformed entries
        }
        
        $term_info = trim($parts[0]);
        $entry['definition'] = trim($parts[1]);
        
        // Extract Sanskrit and Tibetan terms
        $sanskrit_pattern = '/\(Skt\.(?:\s+([^)]+))?\)/';
        $tibetan_pattern = '/(?:–\s*)?Tib\.(?:\s+([^:]+?))?(?:\s*:|$)/';
        
        // Remove Sanskrit notation and extract term
        if (preg_match($sanskrit_pattern, $term_info, $skt_matches)) {
            if (isset($skt_matches[1])) {
                $entry['sanskrit_term'] = trim($skt_matches[1]);
            }
            $term_info = preg_replace($sanskrit_pattern, '', $term_info);
        }
        
        // Remove Tibetan notation and extract term
        if (preg_match($tibetan_pattern, $term_info, $tib_matches)) {
            if (isset($tib_matches[1])) {
                $entry['tibetan_term'] = trim($tib_matches[1]);
            }
            $term_info = preg_replace($tibetan_pattern, '', $term_info);
        }
        
        // Clean up the English term
        $entry['english_term'] = trim($term_info, ' –');
        
        if (!empty($entry['english_term']) && !empty($entry['definition'])) {
            $entries[] = $entry;
        }
    }
    
    return $entries;
}

function get_or_create_sanskrit_term($term) {
    if (empty($term)) {
        return null;
    }
    
    // Check if it already exists
    $existing = get_posts(array(
        'post_type' => 'sanskrit_term',
        'meta_query' => array(
            array(
                'key' => 'sanskrit_term',
                'value' => $term,
                'compare' => '='
            )
        ),
        'posts_per_page' => 1,
        'post_status' => 'any'
    ));
    
    if (!empty($existing)) {
        return $existing[0]->ID;
    }
    
    // Create new Sanskrit term
    $post_data = array(
        'post_type' => 'sanskrit_term',
        'post_status' => 'publish',
        'meta_input' => array(
            'sanskrit_term' => $term
        )
    );
    
    $post_id = wp_insert_post($post_data);
    return ($post_id && !is_wp_error($post_id)) ? $post_id : null;
}

function get_or_create_tibetan_term($term) {
    if (empty($term)) {
        return null;
    }
    
    // Check if it already exists
    $existing = get_posts(array(
        'post_type' => 'tibetan_term',
        'meta_query' => array(
            array(
                'key' => 'tibetan_term',
                'value' => $term,
                'compare' => '='
            )
        ),
        'posts_per_page' => 1,
        'post_status' => 'any'
    ));
    
    if (!empty($existing)) {
        return $existing[0]->ID;
    }
    
    // Create new Tibetan term
    $post_data = array(
        'post_type' => 'tibetan_term',
        'post_status' => 'publish',
        'meta_input' => array(
            'tibetan_term' => $term
        )
    );
    
    $post_id = wp_insert_post($post_data);
    return ($post_id && !is_wp_error($post_id)) ? $post_id : null;
}

echo '<!DOCTYPE html>
<html>
<head>
    <title>Import Glossary Entries</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .entry { margin: 15px 0; padding: 15px; background: #f9f9f9; border-left: 4px solid #0073aa; }
        .success { border-left-color: #46b450; }
        .error { border-left-color: #dc3232; }
        .skip { border-left-color: #e67e22; }
        .term-name { font-weight: bold; font-size: 16px; margin-bottom: 5px; }
        .term-details { font-size: 14px; color: #666; margin-bottom: 8px; }
        .definition { font-size: 14px; line-height: 1.4; }
    </style>
</head>
<body>';

echo '<h1>Glossary Entries Import</h1>';

// Parse the glossary content
$entries = parse_glossary_entries($glossary_content);
echo '<p>Parsed ' . count($entries) . ' glossary entries. Starting import...</p>';

$imported_count = 0;
$skipped_count = 0;
$errors = array();

foreach ($entries as $index => $entry) {
    echo '<div class="entry">';
    echo '<div class="term-name">' . ($index + 1) . '. ' . esc_html($entry['english_term']) . '</div>';
    
    $details = array();
    if (!empty($entry['sanskrit_term'])) {
        $details[] = 'Skt. ' . esc_html($entry['sanskrit_term']);
    }
    if (!empty($entry['tibetan_term'])) {
        $details[] = 'Tib. ' . esc_html($entry['tibetan_term']);
    }
    
    if (!empty($details)) {
        echo '<div class="term-details">' . implode(' | ', $details) . '</div>';
    }
    
    echo '<div class="definition">' . esc_html(substr($entry['definition'], 0, 150)) . '...</div>';
    
    // Check if glossary entry already exists
    $existing = get_posts(array(
        'post_type' => 'glossary_entry',
        'meta_query' => array(
            array(
                'key' => 'english_term',
                'value' => $entry['english_term'],
                'compare' => '='
            )
        ),
        'posts_per_page' => 1,
        'post_status' => 'any'
    ));
    
    if (!empty($existing)) {
        echo '<span style="color: #e67e22;">✓ Already exists (ID: ' . $existing[0]->ID . ')</span>';
        $skipped_count++;
    } else {
        // Get or create Sanskrit and Tibetan terms
        $sanskrit_id = get_or_create_sanskrit_term($entry['sanskrit_term']);
        $tibetan_id = get_or_create_tibetan_term($entry['tibetan_term']);
        
        // Create glossary entry
        $post_data = array(
            'post_type' => 'glossary_entry',
            'post_status' => 'publish',
            'meta_input' => array(
                'english_term' => $entry['english_term'],
                'definitiion' => $entry['definition'], // Note: keeping the typo as in the field name
                'sanskrit_term' => $sanskrit_id,
                'tibetan_term' => $tibetan_id
            )
        );
        
        $post_id = wp_insert_post($post_data);
        
        if ($post_id && !is_wp_error($post_id)) {
            echo '<span style="color: #46b450;">✓ Created successfully (ID: ' . $post_id . ')</span>';
            if ($sanskrit_id) {
                echo '<br><span style="color: #666; font-size: 12px;">→ Sanskrit term ID: ' . $sanskrit_id . '</span>';
            }
            if ($tibetan_id) {
                echo '<br><span style="color: #666; font-size: 12px;">→ Tibetan term ID: ' . $tibetan_id . '</span>';
            }
            $imported_count++;
        } else {
            $error_message = is_wp_error($post_id) ? $post_id->get_error_message() : 'Unknown error';
            echo '<span style="color: #dc3232;">✗ Failed: ' . esc_html($error_message) . '</span>';
            $errors[] = "Entry #{$index}: {$entry['english_term']} - {$error_message}";
        }
    }
    
    echo '</div>';
    
    // Flush output to show progress
    if (ob_get_level()) {
        ob_flush();
    }
    flush();
}

echo '<hr>';
echo '<h2>Import Summary</h2>';
echo '<p><strong>Total entries parsed:</strong> ' . count($entries) . '</p>';
echo '<p><strong>Successfully imported:</strong> ' . $imported_count . '</p>';
echo '<p><strong>Skipped (already exist):</strong> ' . $skipped_count . '</p>';
echo '<p><strong>Errors:</strong> ' . count($errors) . '</p>';

if (!empty($errors)) {
    echo '<h3>Error Details:</h3>';
    echo '<ul>';
    foreach ($errors as $error) {
        echo '<li>' . esc_html($error) . '</li>';
    }
    echo '</ul>';
}

echo '<hr>';
echo '<p><strong>Next Steps:</strong></p>';
echo '<ul>';
echo '<li>Review the imported glossary entries in your WordPress admin</li>';
echo '<li>Delete this import script file for security: <code>import-glossary-entries.php</code></li>';
echo '<li>Check that Sanskrit and Tibetan terms were created correctly</li>';
echo '<li>Verify the relationships between glossary entries and their terms</li>';
echo '</ul>';

echo '<p><a href="' . admin_url('edit.php?post_type=glossary_entry') . '">← View Glossary Entries in Admin</a></p>';

echo '</body></html>';
?>