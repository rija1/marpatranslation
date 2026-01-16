<?php
/**
 * TEMPORARY SCRIPT: Import Glossary Entries with Unicode Tibetan
 * Run this to properly import glossary entries with Unicode Tibetan characters
 * 
 * Usage: Visit http://marpatranslation.local/import-glossary-unicode.php
 */

// Load WordPress
require_once('wp-load.php');

// Check if user has permissions
if (!current_user_can('manage_options')) {
    die('<h1>Access Denied</h1><p>You must be logged in as an administrator to run this import.</p>');
}

// Raw glossary content with Unicode Tibetan
$glossary_content = "Abhidharma (Skt.) – Tib. ཆོས་མངོན་པ། The collection of teachings concerned with the systematic analysis and examination of phenomena – the foundation of Buddhist philosophy and logic. One of the tripitaka or three baskets of the Buddhist canon, comprising the vinaya, sūtra and abhidharma.
Adept – Skt. siddha, Tib. གྲུབ་ཐོབ། An accomplished spiritual master or saint. The name for a practitioner who has gained either the ordinary or supreme attainments. See attainment.
Approach and accomplishment – Skt. sevāsādhana, Tib. བསྙེན་སྒྲུབ། These are the four levels of development in secret mantra practice: approach, further approach, accomplishment and great accomplishment. See generation stage.
Ancient School – Tib. རྙིང་མ། The first wave of teachings brought to Tibet, translated and propagated during the reign of King Trisong Deutsen up until the time of Lochen Rinchen Sangpo in the ninth century. Compare with New schools.
Atisha (982-1054) – The Indian scholar from Vikramalashila monastery who, although highly accomplished and renowned, undertook a perilous journey overseas to receive teachings and rely on Serlingpa. He spent the latter years of his life in Tibet and his teachings became the inspiration and basis for the Kadampa school. Atisha is credited with firmly re-establishing the basic precepts of Buddhism in Tibet, following a widespread misunderstanding of tantric principles which had been severely damaging the buddha dharma. See Kadampa.
Attainments – Skt. siddhi, Tib. དངོས་གྲུབ། The accomplishments of those who gain the results of the Buddhist path. There are two types: ordinary and supreme attainments. The ordinary attainments are worldly powers, such as clairvoyance. The supreme attainment is complete awakening.
Awakening mind – Skt. bodhicitta, Tib. བྱང་ཆུབ་ཀྱི་སེམས། The wish to attain buddhahood in order to benefit sentient beings and free them from the sufferings of cyclic existence. There are two types; relative awakening mind, with aspirational and engaged aspects; and ultimate awakening mind, the intelligence that realises the abiding nature.
Between-state – Skt. antarābhava, Tib. བར་དོ། Commonly used to refer to the various experiences encountered between death and the next rebirth. More specifically, four specific between-states are described: the between-state of this life, the between-state of the time of death, the between-state of absolute reality and the between-state of rebirth.
Bodhisattva (Skt.) – Tib. བྱང་ཆུབ་སེམས་དཔའ། A noble being of the Greater Vehicle whose intention and conduct is solely directed towards benefiting sentient beings, and attaining awakening for that sake.
Buddha (Skt.) – Tib. སངས་རྒྱས། Lit. awake and developed. Refers to a being who has attained awakening but also to the state where all afflictions, delusions and their tendencies have been purified, all qualities developed and all knowledge and wisdom perfected. A state where all afflictions, delusions and their tendencies have been purified, all qualities developed and all knowledge and wisdom perfected. As they are beyond ordinary beings' perception and concepts, an understanding of buddha qualities is to be approached first though extensive study, contemplation and meditation. It is also the name of the principal Teacher and founder of Buddhism, Buddha Shakyamuni, Siddharta Gautama, who lived in India during the fifth century BCE.
Buddhahood – Tib. སངས་རྒྱས་ཀྱི་གོ་འཕང་། The level or state of a buddha. See Buddha.
Calm-abiding – Skt. śamatha, Tib. ཞི་གནས། Tib. Pr. shinnay. The practice of calming, pacifying or settling the mind so as to set a foundation for more advanced Buddhist practice. The practice of calm-abiding is shared in common with many spiritual and secular traditions.
Cyclic existence – Skt. saṃsāra, Tib. འཁོར་བ། The Tibetan term has a nominal sense of a 'circle' or 'cycle' and refers to the conditioned existence of all living beings. Propelled by desire, anger, confusion and the karmic force of actions, beings are trapped in a perpetual cycle of birth, death and rebirth. Each new rebirth is characterised by misery. For example, humans suffer from busyness and poverty, deprived spirits from the agony of hunger and thirst, and animals from stupidity, dullness, fear and so on. The Buddha taught the path of liberation as a way out of the suffering of cyclic existence. See desire, form and formless realms.
Demon – Skt. māra, Tib. བདུད། Any type of negative or opposing force that hinders the practice of the dharma. Seen either as something external, like a type of negative spirit or ghost, or internal, symbolising our inner afflictions. One of the twelve deeds of the Buddha was his victory over the forces of Mara just before he manifested awakening.
Desire, form and formless realms – Skt. kāmadhātu, rūpadhātu, ārūpyadhātu, Tib. འདོད་ཁམས། གཟུགས་ཁམས། གཟུགས་མེད་ཁམས། The three realms of cyclic existence. The desire realm comprises six classes of beings: hell beings, deprived spirits, animals, humans, demigods and some of the classes of gods. The form realm comprises seventeen other classes of gods who abide in one of the four meditative absorptions, albeit tainted with clinging. The formless realm comprises four classes of gods and is the highest rebirth of the three realms of cyclic existence, where life is at the level of consciousness only.
Devadatta – Tib. ལྷས་བྱིན། A cousin of the Buddha who later turned against him. His pride and jealousy prevented him from being able to appreciate the Buddha's qualities. He caused a schism in the sangha and tried to kill the Buddha, and consequently was reborn as a hell being.
Dhāraṇī – Skt. dhāraṇī, Tib. གཟུངས། Succinct formulas or verses that express the essence of the dharma. They may be used to remember key points, and are mostly in Sanskrit. Dhāraṇī is sometimes used as a synonym for mantra.
Dharma (Skt.) – Tib. ཆོས། The Sanskrit word is traditionally said to have ten meanings. In the Buddhist context the term often refers to the teachings of the Buddha and the excellent qualities made manifest as one traverses the paths. It describes the basic laws of nature; the 'reality' or 'truth,' just the way things are. Dharma is also often used to mean 'things', 'phenomena', the basic elements of mind and matter. The Tibetan term Chos has the additional meaning of something which corrects, a remedy.
Dharma body – Skt. dharmakāya, Tib. ཆོས་སྐུ། One of the three bodies of a buddha. The body of truth, the emptiness aspect of buddhahood, the ultimate nature of awakened mind beyond concepts.
Dharma protectors – Skt. dharmapāla, Tib. ཆོས་སྐྱོང་། There are two types: wisdom and worldly dharma protectors. Wisdom dharma protectors are wrathful emanations of buddhas which manifest to protect sentient beings and the buddhadharma. Worldly dharma protectors are those who have been tamed, for example by Guru Rinpoche, bound by oath and entrusted with the similar activity of protecting the dharma and practitioners.
Dusum Khyenpa (1110-1193 CE). The first in a line of successive Karmapas, incarnating to benefit sentient beings and the buddhadharma. The figurehead of the Karma Kagyu school of Tibetan Buddhism. Dusum Khyenpa was one of the foremost disciples of Gampopa.
Eight worldly dharmas – Skt. aṣṭalokadharmāḥ, Tib. འཇིག་རྟེན་ཆོས་བརྒྱད། Ordinary human preoccupation with regard to gain and loss, pleasure and pain, praise and criticism, fame and infamy.
Emanation body – Skt. nirmāṇakāya, Tib. སྤྲུལ་སྐུ། Tib. Pr. tulku. One of the three bodies of a buddha, which manifests in any way that will bring benefit to beings. It is the embodied aspect of awakening which can be perceived by ordinary beings. The Tibetan term 'tulku' is also used commonly to refer to a reincarnate lama or bodhisattva.
Empowerment – Skt. abhiṣeka, Tib. དབང་བསྐུར། An initiation ceremony required for any practice within the vehicle of secret mantra, and through which the secret mantra pledges are taken. Empowerments are often received numerous times in order to restore and purify any impairments and breakages of the pledges that have been incurred. See secret mantra.
Enjoyment body – Skt. sambhogakāya, Tib. ལོངས་སྤྱོད་རྫོགས་པའི་སྐུ། One of the three bodies of a buddha, perceptible only to bodhisattvas of the ten levels. A precious treasury of inexhaustible resources where all qualities are spontaneously present. It has five certain or definite qualities: definite body, the thirty-two major and eighty minor marks; definite retinue, bodhisattvas; definite location, the Buddha Realm of Akanishta; definite dharma, Greater Vehicle; and definite duration, until cyclic existence is emptied.
Five aggregates – Skt. pañcaskandha, Tib. ཕུང་པོ་ལྔ། The psychophysical components of a sentient being which are: form, feelings, perception, formations and consciousness.
Five wisdoms – Skt. pañcajñāna, Tib. ཡེ་ཤེས་ལྔ། The five areas of non-dualistic awareness: ultimate expanse wisdom, mirror-like wisdom, discriminating wisdom, all-accomplishing wisdom and equality wisdom.
Four classes of tantra – Tib. རྒྱུད་སྡེ་བཞི། The root texts of the Secret Mantra Vehicle. They are classified according to the new translation schools as: kriyā, caryā, yoga and anuttara yoga.
Four continents – Skt. caturdvīpa, Tib. གླིང་བཞི། According to Indian cosmology, there are four continents surrounding Mount Meru. They are Majestic body, Land of Jambu, Bountiful Cow and Unpleasant Sound.
Gampopa – Tib. སྒམ་པོ་པ། (1079-1153 CE). Also known as Dakpo Rinpoche or Peerless Dakpo. He was the foremost disciple of the great adept Milarepa. He is renowned for having introduced the Kadampa instructions into the Kagyu lineage; joining them with the instructions on the great seal and six yogas of Naropa to create a single stream. Dakpo is a place in Central Tibet where Milarepa prophesied Gampopa should meditate and which later became the seat for the first major flourishing of the Kagyu lineage. Subsequent Kagyu branches are collectively known as the Dakpo Kagyu.
Generation stage – Skt. utpattikrama, Tib. བསྐྱེད་རིམ། The first of the two stages of tantric meditation practice whereby practitioners habituate themselves to purity through deity and mantra recitation practice. Generation stage meditation purifies habitual clinging to the four modes of birth: egg, warmth and moisture, womb and miraculous birth. The second stage is called the completion stage.
Greater vehicle – Skt. mahāyāna, Tib. ཐེག་པ་ཆེན་པོ། There are three vehicles in Buddhism: the Lesser Vehicle (Hinayana), the Greater Vehicle (Mahayana) and the Secret Mantra Vajra Vehicle (Vajrayana). The Greater Vehicle teachings are suitable for practitioners who aspire to complete awakening for the sake of all beings.
Great seal – Skt. mahāmudrā, Tib. ཕྱག་རྒྱ་ཆེན་པོ། The experience of the true nature of reality and the highest form of meditation taught in the New Schools. Commonly referred to by its Sanskrit term, mahamudra.
Hearer – Skt. śrāvaka, Tib. ཉན་ཐོས། One of two types of followers of the Lesser Vehicle or Hinayana, the other type being the solitary buddhas. Their goal is one of personal liberation from the sufferings of cyclic existence. They do not aspire to full awakening for the sake of all beings, the goal of the Greater Vehicle. See Lesser Vehicle.
Higher states – Skt. svarga, Tib. མཐོ་རིས། The pleasurable states of humans, demigods and gods. The human and demigod states exist solely within the desire realm, whereas the states of gods are made up of six classes from the desire realm, seventeen classes from the form realm and four classes from the formless realm.
Insight meditation – Skt. vipaśyana, Tib. ལྷག་མཐོང་། Tib. Pr. Lhaktong. Lit. higher seeing. A meditation of examining, developing and sustaining insight into the nature of mind and phenomena. Sometimes abbreviated to insight.
Jataka Tales – Tib. སྐྱེས་རབས། The Jataka Tales of the Buddha are the collection of accounts of the Bodhisattva's previous lives leading up to his life as Shakyamuni, the Buddha of our present age. The stories of his rebirths include lives as animals, for example, as a monkey and a fish, and as many types of person, for example, as an untouchable, merchant, cook, forester, warrior and king.
Kadampa – Tib. བཀའ་གདམས་པ། The tradition that emerged in Tibet based on Lord Atiśa's teachings in the eleventh century. The Kadam masters were said to be endowed with the Sevenfold Teachings and Deities, the teachings being the three baskets of vinaya, sūtra and abhidharma and the four deities being that of Buddha Shakyamuni, Avalokiteśvara, Tara and Acala. They were renowned for their fortitude in the practice of mind training, the development of compassion and the awakening mind.
Kagyu – Tib. བཀའ་བརྒྱུད། Lit. Instruction lineage. One of the four main schools of Tibetan Buddhism. The Kagyu lineage consists of the longer lineage of the great seal teachings, which came through Saraha; and the closer lineage of the six yogas of Naropa, which was transmitted from Buddha Vajradhara to Tilopa. Both lineages converged with Marpa who transmitted them to Tibet, where they became known as 'Kagyu' retrospectively.
Karmapa – Tib. ཀར་མ་པ། The spiritual leader of the Karma Kagyu; one of the four main branches of the Kagyu lineage of Tibetan Buddhism, which takes its name from Karmapa. The title given to successive incarnations of Dusum Khyenpa (1110 - 1193). See Kagyu.
Lama – Skt. guru, Tib. བླ་མ། Lama is a title or name given to a guide on the Buddhist path. In its true sense the term should signify someone with advanced spiritual development and qualities gained through practice and realisation. In the Kagyu tradition the lama through whom a disciple recognises the nature of mind is seen to be of utmost importance, and is called the root lama.
Lay aspirant – Skt. upasaka. Tib. དགེ་བསྙེན། A lay Buddhist who has vowed to uphold any or all of the five vows: not to kill, steal, lie, engage in sexual misconduct, or consume intoxicants.
Lesser Vehicle – Skt. hīnayāna, Tib. ཐེག་དམན། One of the three vehicles of Buddhism. The Lesser Vehicle refers to the path of personal liberation from cyclic existence, practised by the hearers and solitary buddhas. It is the Buddha's first turning of the wheel of dharma on the four noble truths. Practitioners of this vehicle typically realise the selflessness of the person but not the entire selflessness of phenomena. Also, as they are yet to develop the quality of compassion for all beings, their resultant awakening is not complete.
Liberation – Skt. mokṣa, Tib. ཐར་པ། The state of freedom from the sufferings of cyclic existence, where beings are trapped in a perpetual round of birth, death and rebirth due to ignorance, afflictions and karma. It is the result of the Lesser Vehicle and is not generally used to refer to the full awakening of buddhahood. See Lesser Vehicle.
Lord of death – Skt. yama, Tib. གཤིན་རྗེ། A personification of the forces of impermanence and undeceiving law of karma, cause and result. The Lord of Death, Yama, is traditionally depicted in a wrathful form and holding the wheel of cyclic life.
Lower states of misery – Skt. durgati, Tib. ངན་སོང་། The unhappy states of existence of animals, deprived spirits and hell beings, within the desire realm. The beings here experience unbearable suffering. Each state is dominated by a particular affliction: animals by confusion; deprived spirits by craving; hell beings by anger, and so forth. Like all states within cyclic existence, their experiences are deluded and produced by karma and obscurations. See desire, form and formless realms.
Marpa (1012-1097 CE). Tib. མར་པ། The great Tibetan translator who made three journeys to India in order to obtain the tantric teachings. Marpa spent around forty years receiving and practising the teachings with many Indian masters, most notably Naropa and Maitripa. Having realised these teachings he transmitted them to Tibet, where they became known as 'Kagyu'.
Meditative concentration – Skt. samādhi, Tib. ཏིང་ངེ་འཛིན། Meditative concentration is where one holds the mind firmly upon a particular focus. The term is used to describe various types of meditative states. Moral discipline is considered an indispensable prerequisite for achieving meditative concentration.
Mental afflictions – Skt. kleśa, Tib. ཉོན་མོངས། The disturbing, negative mental states that afflict the minds of sentient beings, also known as mind poisons. The three main mental afflictions are confusion, desire and anger. These in turn form the basis for pride, jealousy and stinginess.
Merit – Skt. puṇya, Tib. བསོད་ནམས། An individual's accumulation of wholesome or virtuous actions and the corresponding positive karmic result or benefit.
Milarepa (1040-1123 CE) – Tib. མི་ལ་རས་པ། The foremost student of Marpa the Translator, renowned as the great adept who attained awakening in one lifetime. His life story is one of exceptional hardship, immense diligence and fervent devotion and is a source of inspiration for practitioners and non-practitioners alike.
Misguided – Skt. tīrthika, Tib. མུ་སྟེགས་པ། Anyone who holds a wrong view. All wrong views fall to the extreme of nihilism or eternalism. A term often used to describe the philosophical and religious schools in India that contested Buddhism. See wrong views.
Moral discipline – Skt. śīla, Tib. ཚུལ་ཁྲིམས། The keeping of vows and ethical conduct, in order not to harm others. Discipline or right conduct pacifies the mental afflictions and is essential for the practice of meditation.
Mount Meru – Skt. sumeru, Tib. རི་རབ་ལྷུན་པོ། According to Indian cosmology, this is the giant four-sided mountain at the centre of our world system, surrounded by smaller mountains, continents and sub-continents, lakes and oceans.
Naga – Skt. nāga, Tib. ཀླུ། A powerful, serpent-like being that lives in water or under the ground. Nagas often guard great treasures and are sensitive to human activity around their dwellings. They belong half to the god realm and half to the animal realm.
Naropa – Skt. nāropa, Tib. ན་རོ་པ། 956-1040 CE. An Indian scholar, adept and disciple of Tilopa. Naropa transmitted many Secret Mantra teachings to Marpa the Translator who brought them to Tibet. Naropa is renowned for the tremendous hardships he endured as part of his unconventional apprenticeship with Tilopa.
New Schools – Tib. གསར་མ། The schools of the second wave of teachings and translations that spread throughout Tibet from the time of Lochen Rinchen Zangpo onwards. Of the four main schools, the New Schools are the Sakya, Kagyu and Geluk. Compare with the Ancient School.
Perfection of intelligence – Skt. prajñāpāramitā, Tib. ཤེས་རབ་ཀྱི་ཕ་རོལ་ཏུ་ཕྱིན་པ། The Greater Vehicle teachings on insight into emptiness, which belong to the Buddha's middle turning of the wheel of dharma.
Pith instructions – Skt. upadeśa, Tib. མན་ངག Instructions presented in a succinct and very direct way from lama to disciple, usually in a one-to-one transmission.
Pledge deity – Skt. iṣṭadevatā, Tib. ཡི་དམ། A tantric meditational deity that is an expression of the qualities of buddhahood and is practised in the Secret Mantra Vehicle. The pledge deity is the source of attainments. It can manifest in male or female form, with peaceful, wrathful or semi-wrathful attributes. See secret mantra.
Rigpa – Skt. vidyā, Tib. རིག་པ། The state of awareness devoid of ignorance and dualistic fixation. The Tibetan word has many uses, as a regular verb it can mean to know or to see. It is often translated as awareness.
Root lama – Tib. རྩ་བའི་བླ་མ། Generally speaking, a lama endowed with the three kindnesses of giving empowerment, explanations and pith instructions. In the Kagyu tradition, only the lama through whom the student recognises the nature of mind is considered to be the root lama.
Sacred pledge – Skt. samaya, Tib. དམ་ཚིག The vows and commitments predominantly of the Secret Mantra Vehicle. The sacred bonds between the lama and disciple, and between fellow disciples. It also refers to adhering to the words of the lama and maintaining a harmonious relationship with, and correct attitude toward, the lama and fellow disciples. It can also describe the commitment to a particular practice.
Sangha – Skt. saṅgha, Tib. དགེ་འདུན། Most commonly this refers to a community of monks and nuns, however it is also used for the realised, noble sangha of bodhisattvas and arhats. In its broadest sense it is used to refer to the community of Buddhist practitioners or companions on the path.
Secret Mantra Vehicle – Skt. guhyamantra, Tib. གསང་སྔགས། A branch of the Greater Vehicle. The Secret Mantra Vehicle is based on the tantras and contains profound methods for swiftly attaining the result of awakening. These teachings became widespread throughout Tibet. The Secret Mantra Vehicle is also commonly known as Vajrayana or Mantrayana.
Seven branches – Skt. saptāṅga, Tib. ཡན་ལག་བདུན་པ། A practice of seven parts: prostration, offering, confession, rejoicing, requesting the teachers to turn the wheel of dharma, requesting them not to pass into transcendence of misery, and dedication. It is taught to be the supreme method for gathering merit.
Six perfections – Skt. ṣaṭ pāramitāḥ, Tib. ཕ་རོལ་ཏུ་ཕྱིན་པ་དྲུག Lit. the six that have gone to the other shore. The main practices of the Greater Vehicle: generosity, discipline, forbearance, diligence, meditative absorption and intelligence. They are described as transcendent because, once perfected, they take one to the other shore, across the ocean of cyclic existence to awakening.
Sky-farer – Skt. ḍākinī, Tib. མཁའ་འགྲོ་མ། Tib. Pr. khandro ma. The name for pure realm goddesses or female wisdom divinities, the source of wisdom activity. A name given to highly realised women, female adepts. It is also the name for certain flesh-eating demonesses.
Solitary Buddha – Skt. pratyekabuddha, Tib. རང་སངས་རྒྱས། Someone who is practising for the sake of their own liberation, and similar to the hearers, follows the path of the Lesser Vehicle. It is only in this final birth as a solitary buddha that they practice and contemplate dependent origination without the help of a spiritual master. Their transcendence is not full because they do not completely realise the emptiness or selflessness of phenomena.
Stupa – Skt. stūpa, Tib. མཆོད་རྟེན། A sacred monument representing the awakened mind, which stores relics of awakened beings, scriptures, statues etc.
Sukhavati – Skt. sukhavatī, Tib. བདེ་བ་ཅན། Tib. Pr. dewachen. Lit. blissful. The Western buddha field and pure realm of Buddha Amitabha.
Sūtras – Skt. sūtrānta, Tib. མདོ་སྡེ། The words which were taught publicly by Shakyamuni Buddha to his disciples in the discourses of both the Lesser and Greater Vehicle teachings. The sūtras form one of the three baskets of the Buddhist canon, comprising the vinaya, sūtra and abhidharma.
Tantra (Skt.) – Tib. རྒྱུད། Lit. continuity. The teachings which comprise the practice of secret mantra. They were mainly taught by Buddha in his enjoyment body form, for example as Vajradhara. The word tantra also refers to the truth which the teachings present, the truth that is continuous throughout the phases of ground, path and fruition.
Ten directions – Skt. daśadik, Tib. ཕྱོགས་བཅུ། The four cardinal and four intermediate directions, plus the directions of above and below.
The Illustrious – Skt. bhagavat, Tib. བཅོམ་ལྡན་འདས། An epithet for buddha.
Three Jewels – Skt. triratna, Tib. དཀོན་མཆོག་གསུམ། The Buddha, dharma and sangha are the three principal objects of refuge for a Buddhist. See Buddha, dharma and sangha.
Three bodies – Skt. trikāya, Tib. སྐུ་གསུམ། Buddha is comprised of the three bodies: the dharma body, enjoyment body and emanation body.
Three roots – Tib. རྩ་གསུམ། They are the lama, the source of blessings; the yidam or pledge deity, the source of attainments; and the sky-farers and dharma protectors, the source of activity. These are the objects of inner refuge.
Three spheres – Skt. trimaṇḍala, Tib. འཁོར་གསུམ། Generally, this refers to the agent, the action and the object of the action.
Three vows – Skt. trisaṃvara, Tib. སྡོམ་པ་གསུམ། These comprise the Lesser Vehicle vows of personal liberation, which include all the lay and monastic precepts taught by the Buddha as found in the vinaya; the bodhisattva vows of the awakening mind; and the pledges of secret mantra. These precepts and commitments are taken voluntarily to facilitate an individual's progress on the path.
Thus-gone – Skt. tathāgata, Tib. དེ་བཞིན་གཤེགས་པ། A synonym for Buddha.
Tilopa – Skt. tailopa, Tib. ཏཻ་ལོ་པ། 928-1009 C.E. One of the eighty-four Indian great adepts. Tilopa received direct transmission from Vajradhara and became the root lama of Naropa.
Transcendence of misery – Skt. nirvāṇa, Tib. མྱ་ངན་ལས་འདས་པ། A state where suffering and the mental afflictions have been transcended, and delusion exhausted. Transcendence varies according to the realisation of the individual. For example, the transcendence of hearers and solitary buddhas is considered partial from the perspective of bodhisattvas because they have not fully realised the emptiness or selflessness of phenomena.
Treatises – Skt. śāstra, Tib. བསྟན་བཅོས། The authoritative texts and commentaries composed by accomplished masters. They clarify the profound meaning of the Buddha's words by presenting them systematically and explaining their intent.
Trichiliocosm – Skt. trisāhasra mahāsāhasra, Tib. སྟོང་གསུམ་འཇིག་རྟེན་གྱི་ཁམས། In Indian cosmology one world consists of the four continents and Mount Meru, a trichiliocosm is a billion such worlds (a thousand times to the power of three). A billionfold universe.
Two accumulations - Skt. dvisambhāra, Tib. ཚོགས་གཉིས། The accumulations of merit and wisdom form the basis of the Buddhist path in general, and are needed to attain the two bodies of a Buddha; the dharma body and the form body.
Two obscurations – Skt. āvaraṇa dvitidhaḥ, Tib. སྒྲིབ་པ་གཉིས། The afflictive obscurations and cognitive obscurations. The first of those is the obscuration caused by the mental afflictions which prevents freedom from cyclic existence (obstructing liberation). The second is the obscuration to knowing the actual state of things, hindered by dualistic perceptions (obstructing omniscience).
Ultimate expanse – Skt. dharmadhātu, Tib. ཆོས་དབྱིངས། A synonym for emptiness; the empty nature or ultimate, basic state of any or all phenomena.
Vajradhara (Skt.) – Tib. རྡོ་རྗེ་འཆང་། Tib. Pr. dorjé chang. A dharma body buddha, symbolised as a blue deity holding a vajra and bell. In the Kagyu lineage, Tilopa received secret mantra teachings directly from Vajradhara.
Vajra Being – Skt. vajrasattva, Tib. རྡོ་རྗེ་སེམས་དཔའ། Tib. Pr. dorjé sempa. An enjoyment body buddha who embodies all of the hundred buddha families. He is white and associated with purity. The practice of this deity and mantra recitation constitute a particularly effective practice of purification. See chapter on the Hundred-syllable Mantra.
Vajravarahi – Skt. vajravārāhī, Tib. རྡོ་རྗེ་ཕག་མོ། Tib. Pr. dorjé palmo. Lit. Vajra sow. One of the principal meditational deities (yidam) of the Kagyu tradition.
Victor – Skt. jina, Tib. རྒྱལ་བ། A synonym for Buddha.
Vinaya (Skt.) – Tib. འདུལ་བ། The Buddha's teachings on right conduct and moral discipline which explain the precepts for monastics, as well as those for lay Buddhists. Precepts are the very foundation for all dharma practice and meditation. The vinaya is one of the three baskets which make up the main body of the Buddha's teachings, the other two being the sūtras and abhidharma.
Wrong views – Skt. mithyādṛṣṭi, Tib. ལོག་པར་ལྟ་བ། Mistaken beliefs regarding the nature of things, which if acted upon lead to suffering. For example, to have no belief in past and future lives or karma, cause and result. See misguided.";

function parse_glossary_entries_unicode($content) {
    $lines = explode("\n", trim($content));
    $entries = array();
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) {
            continue;
        }
        
        $entry = array(
            'english_term' => '',
            'sanskrit_term' => '',
            'tibetan_term' => '',
            'definition' => ''
        );
        
        // More precise regex patterns for Unicode Tibetan
        // Pattern: "Term – Skt. sanskrit, Tib. ཏིབེཏན། Definition"
        // or "Term (Skt.) – Tib. ཏིབེཏན། Definition"
        
        // First, split on the definition (after the closing ། or after first sentence)
        if (preg_match('/^(.+?)\s+((?:Tib\.\s*Pr\.\s*\w+\.\s*)?(?:Lit\.\s*[^.]+\.\s*)?(?:The|A|An|One|Someone|Anyone|Most|In|According|Refers|Any|During|Ordinary|Succinct|This|See).*)$/u', $line, $def_matches)) {
            $term_part = trim($def_matches[1]);
            $entry['definition'] = trim($def_matches[2]);
        } else {
            // Fallback: try splitting on period followed by space and capital letter
            if (preg_match('/^(.+?\.)\s+([A-Z].*)$/u', $line, $def_matches)) {
                $term_part = trim($def_matches[1], ' .');
                $entry['definition'] = trim($def_matches[2]);
            } else {
                continue; // Skip if we can't parse
            }
        }
        
        // Extract Tibetan Unicode (between "Tib." and "།")
        if (preg_match('/Tib\.\s*([^།]+།)/u', $term_part, $tib_matches)) {
            $entry['tibetan_term'] = trim($tib_matches[1]);
        }
        
        // Extract Sanskrit term (between "Skt." and either "," or "," or "–")
        if (preg_match('/Skt\.\s*([^,–]+?)(?:\s*[,–]|\s*$)/u', $term_part, $skt_matches)) {
            $entry['sanskrit_term'] = trim($skt_matches[1]);
        }
        
        // Clean up English term - remove all the metadata
        $english_part = $term_part;
        $english_part = preg_replace('/\s*–\s*Skt\..*$/u', '', $english_part);
        $english_part = preg_replace('/\s*\(Skt\..*?\)/u', '', $english_part);
        $english_part = preg_replace('/\s*–\s*Tib\..*$/u', '', $english_part);
        $english_part = preg_replace('/\([0-9-]+\s*(?:CE|BCE)\.?\)/', '', $english_part); // Remove dates
        $entry['english_term'] = trim($english_part);
        
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
    
    // Clean term (remove any trailing periods, etc)
    $term = trim($term, ' .');
    
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
    <title>Import Unicode Glossary Entries</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .entry { margin: 15px 0; padding: 15px; background: #f9f9f9; border-left: 4px solid #0073aa; }
        .success { border-left-color: #46b450; }
        .error { border-left-color: #dc3232; }
        .skip { border-left-color: #e67e22; }
        .term-name { font-weight: bold; font-size: 16px; margin-bottom: 5px; }
        .term-details { font-size: 14px; color: #666; margin-bottom: 8px; }
        .tibetan { font-family: "Noto Serif Tibetan", serif; font-size: 18px; }
        .definition { font-size: 14px; line-height: 1.4; }
    </style>
</head>
<body>';

echo '<h1>Unicode Glossary Entries Import</h1>';

// Parse the glossary content
$entries = parse_glossary_entries_unicode($glossary_content);
echo '<p>Parsed ' . count($entries) . ' glossary entries with Unicode Tibetan. Starting import...</p>';

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
        $details[] = 'Tib. <span class="tibetan">' . esc_html($entry['tibetan_term']) . '</span>';
    }
    
    if (!empty($details)) {
        echo '<div class="term-details">' . implode(' | ', $details) . '</div>';
    }
    
    echo '<div class="definition">' . esc_html(substr($entry['definition'], 0, 150)) . '...</div>';
    
    // Check if glossary entry already exists (delete old incorrect ones first)
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
        // Delete the old one first
        wp_delete_post($existing[0]->ID, true);
        echo '<span style="color: #e67e22;">🗑️ Deleted existing incorrect entry</span><br>';
    }
    
    // Get or create Sanskrit and Tibetan terms
    $sanskrit_id = get_or_create_sanskrit_term($entry['sanskrit_term']);
    $tibetan_id = get_or_create_tibetan_term($entry['tibetan_term']);
    
    // Create glossary entry
    $post_data = array(
        'post_type' => 'glossary_entry',
        'post_status' => 'publish',
        'meta_input' => array(
            'glossary_term' => $entry['english_term'],
            'definitiion' => $entry['definition']
        )
    );
    
    $post_id = wp_insert_post($post_data);
    
    if ($post_id && !is_wp_error($post_id)) {
        echo '<span style="color: #46b450;">✓ Created successfully (ID: ' . $post_id . ')</span>';
        
        // Add proper Pods relationships
        global $wpdb;
        
        if ($sanskrit_id) {
            $wpdb->replace(
                $wpdb->prefix . 'podsrel',
                array(
                    'field_id' => 840,  // sanskrit_term field ID
                    'item_id' => $post_id,
                    'related_item_id' => $sanskrit_id,
                    'weight' => 1
                ),
                array('%d', '%d', '%d', '%d')
            );
            echo '<br><span style="color: #666; font-size: 12px;">→ Linked Sanskrit term ID: ' . $sanskrit_id . '</span>';
        }
        
        if ($tibetan_id) {
            $wpdb->replace(
                $wpdb->prefix . 'podsrel',
                array(
                    'field_id' => 839,  // tibetan_term field ID
                    'item_id' => $post_id,
                    'related_item_id' => $tibetan_id,
                    'weight' => 1
                ),
                array('%d', '%d', '%d', '%d')
            );
            echo '<br><span style="color: #666; font-size: 12px;">→ Linked Tibetan term ID: ' . $tibetan_id . '</span>';
        }
        
        $imported_count++;
    } else {
        $error_message = is_wp_error($post_id) ? $post_id->get_error_message() : 'Unknown error';
        echo '<span style="color: #dc3232;">✗ Failed: ' . esc_html($error_message) . '</span>';
        $errors[] = "Entry #{$index}: {$entry['english_term']} - {$error_message}";
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
echo '<li>Review the imported glossary entries with Unicode Tibetan</li>';
echo '<li>Delete this import script: <code>import-glossary-unicode.php</code></li>';
echo '<li>Delete the fix script: <code>fix-glossary-entries.php</code></li>';
echo '<li>Delete the old import script: <code>import-glossary-entries.php</code></li>';
echo '</ul>';

echo '<p><a href="' . admin_url('edit.php?post_type=glossary_entry') . '">← View Glossary Entries in Admin</a></p>';

echo '</body></html>';
?>