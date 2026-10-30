<?php
/**
 * Madame Aiguille — catalogue réel transmis par Céline (document « Présentation site », octobre 2026)
 *
 * Crée les deux catégories et leurs treize sous-catégories, les trois premières fiches produits
 * (prix provisoire 999 €, sans photo ni poids), et remplace dans « À propos » et dans le bloc
 * d'accueil `home_story` les seuls textes provisoires encore en place par la présentation de Céline.
 *
 * Seule exception à la règle « rien ici ne tourne en production » (décision de Pierre du 23/10/2026) :
 * le même script peuple le poste local et le serveur. Il ne crée que ce qui manque et ne modifie ni
 * ne supprime jamais rien : une catégorie (clé d'URL sous son parent) ou un produit (SKU) déjà présent
 * est laissé tel quel, les retouches de Céline dans l'administration survivent à un rejeu.
 *
 * Les sous-catégories sans produit sont créées hors du menu ; Céline les y ajoute avec leur premier produit.
 * Les produits sont créés **désactivés** (999 € ne doit pas pouvoir être commandé), sauf avec
 * --produits-actifs, réservé au poste local, qui les active avec un stock provisoire de 1.
 *
 * Usage, depuis la racine du dépôt :
 *   php docs/jeux-de-donnees/catalogue-celine.php [--simulation] [--produits-actifs] [--magento=<racine Magento>]
 * puis bin/magento cache:clean (et indexer:reindex si les index ne sont pas en mode « planifié »).
 */

declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

$options = getopt('', ['simulation', 'produits-actifs', 'magento:']);
$simulation = isset($options['simulation']);
$activeProducts = isset($options['produits-actifs']);
$magentoRoot = rtrim((string) ($options['magento'] ?? dirname(__DIR__, 2) . '/shop'), '/');

if (!is_file($magentoRoot . '/app/bootstrap.php')) {
    fwrite(STDERR, "Racine Magento introuvable : {$magentoRoot}\n");
    exit(1);
}
require $magentoRoot . '/app/bootstrap.php';

$objectManager = Bootstrap::create(BP, $_SERVER)->getObjectManager();
$objectManager->get(State::class)->setAreaCode('adminhtml');

$categoryFactory = $objectManager->get(CategoryFactory::class);
$categoryCollectionFactory = $objectManager->get(CategoryCollectionFactory::class);
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productFactory = $objectManager->get(ProductFactory::class);
$pageRepository = $objectManager->get(PageRepositoryInterface::class);
$blockRepository = $objectManager->get(BlockRepositoryInterface::class);
$criteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);

$say = function (string $message) use ($simulation): void {
    echo ($simulation ? '[simulation] ' : '') . $message . "\n";
};

// ---------------------------------------------------------------------------
// 1. Catégories : nom, clé d'URL, position, description et métadonnées SEO, sous-catégories
// ---------------------------------------------------------------------------

$tree = [
    [
        'name' => 'Réutilisables au quotidien',
        'url_key' => 'reutilisables-au-quotidien',
        'description' => '<p>Des accessoires lavables, cousus main, pour remplacer le jetable au quotidien : '
            . 'cotons démaquillants, essuie-tout, gants, pétales de douche et écrins de savon.</p>',
        'meta_title' => 'Accessoires réutilisables faits main | Madame Aiguille',
        'meta_description' => 'Cotons démaquillants lavables, essuie-tout réutilisables, gants et pétales de douche '
            . 'cousus main par Céline, pour un quotidien plus doux et plus responsable.',
        'children' => [
            ['name' => 'Le pétale de douche', 'url_key' => 'petale-de-douche'],
            [
                'name' => 'Le tendre coton',
                'url_key' => 'cotons-demaquillants-reutilisables',
                'description' => '<p>Des cotons démaquillants réutilisables, coton d’un côté et éponge de bambou '
                    . 'de l’autre, à l’unité ou en coffret à offrir.</p>',
                'meta_title' => 'Cotons démaquillants réutilisables faits main | Madame Aiguille',
                'meta_description' => 'Cotons démaquillants lavables en coton et éponge de bambou, doux pour la peau, '
                    . 'cousus main par Céline : à l’unité ou en coffret tendresse.',
            ],
            ['name' => 'Les essuie-tout réutilisables', 'url_key' => 'essuie-tout-reutilisables'],
            ['name' => 'Le gant douceur', 'url_key' => 'gant-douceur'],
            ['name' => 'L’écrin de savon', 'url_key' => 'ecrin-de-savon'],
        ],
    ],
    [
        'name' => 'Accessoires & beauté',
        'url_key' => 'accessoires-et-beaute',
        'description' => '<p>Trousses de voyage, sacs, étuis, chouchous et bandeaux cousus main en petites séries, '
            . 'pour emporter vos essentiels avec élégance.</p>',
        'meta_title' => 'Trousses, sacs et accessoires faits main | Madame Aiguille',
        'meta_description' => 'Trousses de voyage, sacs, étuis, chouchous et bandeaux cousus main par Céline : '
            . 'des accessoires uniques et durables pour tous les jours.',
        'children' => [
            ['name' => 'Le sac Adèle', 'url_key' => 'sac-adele'],
            ['name' => 'L’étui solaire', 'url_key' => 'etui-solaire'],
            [
                'name' => 'La trousse de voyage',
                'url_key' => 'trousse-de-voyage',
                'description' => '<p>Une trousse de voyage en velours côtelé, cousue main, pour ranger tous vos '
                    . 'essentiels de soin pendant vos déplacements.</p>',
                'meta_title' => 'Trousse de voyage en velours côtelé faite main | Madame Aiguille',
                'meta_description' => 'Trousse de voyage élégante et résistante en velours côtelé, intérieur coton, '
                    . 'cousue main par Céline pour organiser vos essentiels de soin.',
            ],
            ['name' => 'Le chouchou Océanne', 'url_key' => 'chouchou-oceanne'],
            ['name' => 'Le bandeau Louane', 'url_key' => 'bandeau-louane'],
            ['name' => 'Le sac à tarte « Mamie »', 'url_key' => 'sac-a-tarte-mamie'],
            ['name' => 'Le mini étui poétique', 'url_key' => 'mini-etui-poetique'],
            ['name' => 'L’étui poétique classique', 'url_key' => 'etui-poetique-classique'],
        ],
    ],
];

// ---------------------------------------------------------------------------
// 2. Produits : SKU, nom, clé d'URL, catégories (clés d'URL parent/enfant), textes, caractéristiques
// ---------------------------------------------------------------------------

$products = [
    [
        'sku' => 'MA-TRO-VOY',
        'name' => 'La trousse de voyage',
        'url_key' => 'trousse-de-voyage',
        'categories' => ['accessoires-et-beaute', 'accessoires-et-beaute/trousse-de-voyage'],
        'home_featured' => 1,
        'short_description' => 'Élégante et résistante, pour organiser tous vos essentiels de soin en voyage.',
        'description' => '<p>Pensée pour vos déplacements, cette trousse de voyage cousue main garde vos essentiels '
            . 'de soin bien rangés et à portée de main.</p>'
            . '<p>Son extérieur en velours côtelé lui donne de la tenue et du caractère ; son intérieur en coton '
            . 'se lave facilement.</p>',
        'composition' => 'Extérieur velours côtelé, intérieur coton',
        'dimensions' => '30 × 14 × 7 cm (longueur × hauteur × profondeur)',
        'entretien' => 'Lavable en machine à 30 °C ou à la main',
    ],
    [
        'sku' => 'MA-COF-TEN',
        'name' => 'Le coffret tendresse',
        'url_key' => 'coffret-tendresse',
        'categories' => ['reutilisables-au-quotidien', 'reutilisables-au-quotidien/cotons-demaquillants-reutilisables'],
        'short_description' => 'Une panière et quatre cotons réutilisables, idéal à offrir.',
        'description' => '<p>Idéal pour offrir, ce coffret mêle praticité et charme pour sublimer votre salle de bain : '
            . 'une panière et quatre cotons réutilisables.</p>'
            . '<p>L’alliance du coton et de l’éponge de bambou, une matière naturelle particulièrement agréable au '
            . 'toucher et respectueuse de la peau, accompagne votre routine en douceur, jour après jour.</p>',
        'composition' => 'Panière : extérieur velours côtelé, intérieur coton, thermocollant pour la tenue. '
            . 'Cotons : coton d’un côté, éponge de bambou de l’autre',
        'dimensions' => 'Panière : 14 × 12 × 14 cm (longueur × profondeur × hauteur) · cotons : 10 × 10 cm',
    ],
    [
        'sku' => 'MA-COT-TEN',
        'name' => 'Les tendres cotons',
        'url_key' => 'tendres-cotons-reutilisables',
        'categories' => ['reutilisables-au-quotidien', 'reutilisables-au-quotidien/cotons-demaquillants-reutilisables'],
        'short_description' => 'Réutilisables et tout doux, pour une routine beauté écoresponsable.',
        'description' => '<p>Ces cotons démaquillants réutilisables remplacent les cotons jetables, sans rien perdre '
            . 'en douceur.</p>'
            . '<p>Coton d’un côté, éponge de bambou de l’autre : une matière naturelle, agréable au toucher et '
            . 'respectueuse de la peau, pour un usage quotidien.</p>',
        'composition' => 'Coton d’un côté, éponge de bambou de l’autre',
        'dimensions' => '10 × 10 cm',
    ],
];

// Une sous-catégorie n'entre dans le menu qu'avec au moins un produit
$categoriesWithProducts = array_merge(...array_column($products, 'categories'));

// ---------------------------------------------------------------------------
// 3. Présentation de Céline : fragments provisoires exacts à remplacer
// ---------------------------------------------------------------------------

$aboutPlaceholder = '<p class="cms-lead">Bienvenue dans l’univers de Madame Aiguille, un atelier de créations textiles '
    . 'imaginées au fil des tissus et des envies.</p>' . "\n"
    . '<h2>Une histoire à raconter</h2>' . "\n"
    . '<p>Cette page accueillera l’histoire de Céline, son parcours et ce qui l’a menée à créer Madame Aiguille. '
    . 'Le texte définitif sera publié après sa validation.</p>' . "\n"
    . '<h2>Des créations en petites séries</h2>' . "\n"
    . '<p>Les collections évoluent avec les motifs et les matières sélectionnés pour l’atelier. Chaque fiche produit '
    . 'précise les caractéristiques de la création et les quantités encore disponibles.</p>';

$aboutText = '<p class="cms-lead">Je m’appelle Céline, j’ai 23 ans, et je suis la créatrice de Madame Aiguille.</p>' . "\n"
    . '<h2>Une passion cousue main</h2>' . "\n"
    . '<p>Passionnée de couture, j’aime les créations faites avec soin. Chaque pièce est réalisée à la main, avec amour '
    . 'et attention, pour vous proposer des créations uniques.</p>' . "\n"
    . '<h2>Des créations utiles au quotidien</h2>' . "\n"
    . '<p>À travers Madame Aiguille, je souhaite partager ma passion et donner vie à vos envies : des accessoires '
    . 'réutilisables pour une routine plus douce et plus responsable (cotons démaquillants lavables, essuie-tout, '
    . 'gants, pétales de douche) et des accessoires qui vous suivent partout (trousses de voyage, sacs, étuis, '
    . 'chouchous, bandeaux).</p>' . "\n"
    . '<h2>Le fait main avant tout</h2>' . "\n"
    . '<p>Mettre en avant le savoir-faire artisanal, c’est choisir de belles matières, comme le velours côtelé, le coton '
    . 'ou l’éponge de bambou, et prendre le temps de bien faire.</p>';

$aboutMetaTitle = 'Céline, créatrice de Madame Aiguille | Couture faite main';
$aboutMetaDescription = 'Je m’appelle Céline et je couds à la main des accessoires réutilisables et des pièces uniques : '
    . 'cotons lavables, trousses, sacs, chouchous.';

$storyPlaceholder = '<p>Tout commence avec un tissu, une couleur, une envie de créer.</p>'
    . '<p>Madame Aiguille vous invite dans un univers de créations textiles pensées pour accompagner votre quotidien.</p>';
$storyText = '<p>Je m’appelle Céline, créatrice de Madame Aiguille. Passionnée de couture, je réalise à la main des '
    . 'pièces uniques, avec amour et attention.</p>'
    . '<p>À travers l’atelier, je partage ma passion et mets en avant le fait main et le savoir-faire artisanal.</p>';

// ---------------------------------------------------------------------------
// 4. Création des catégories
// ---------------------------------------------------------------------------

$rootId = (int) $objectManager->get(StoreManagerInterface::class)->getStore('default')->getRootCategoryId();
$root = $categoryFactory->create()->load($rootId);
if (!$root->getId()) {
    fwrite(STDERR, "Catégorie racine #{$rootId} introuvable.\n");
    exit(1);
}

/** @var array<string, int> $categoryIds identifiant par chemin de clés d'URL (« parent/enfant ») */
$categoryIds = [];

$ensureCategory = function (array $data, Category $parent, int $position, string $keyPath, bool $inMenu) use (
    $categoryFactory,
    $categoryCollectionFactory,
    $simulation,
    $say
): ?Category {
    $existing = $categoryCollectionFactory->create()
        ->addAttributeToFilter('url_key', $data['url_key'])
        ->addAttributeToFilter('parent_id', (int) $parent->getId())
        ->setPageSize(1)
        ->getFirstItem();

    if ($existing->getId()) {
        $say(sprintf('Déjà présente, laissée telle quelle : %s (#%d)', $data['name'], $existing->getId()));
        return $categoryFactory->create()->load((int) $existing->getId());
    }

    $say(sprintf('Création de la catégorie %s (%s)%s', $data['name'], $keyPath, $inMenu ? '' : ', hors menu'));
    if ($simulation) {
        return null;
    }

    /** @var Category $category */
    $category = $categoryFactory->create();
    $category->setStoreId(0)
        ->setParentId((int) $parent->getId())
        ->setPath($parent->getPath())
        ->setName($data['name'])
        ->setUrlKey($data['url_key'])
        ->setIsActive(true)
        ->setIncludeInMenu($inMenu)
        ->setIsAnchor(true)
        ->setDisplayMode(Category::DM_PRODUCT)
        ->setPosition($position)
        ->setDescription($data['description'] ?? null)
        ->setMetaTitle($data['meta_title'] ?? null)
        ->setMetaDescription($data['meta_description'] ?? null);
    $category->save();

    // Garde-fou du 10/09/2026 : un chemin erroné rattacherait la catégorie au mauvais endroit de l'arbre
    $expectedPath = $parent->getPath() . '/' . $category->getId();
    if ($category->getPath() !== $expectedPath) {
        fwrite(STDERR, sprintf("Chemin inattendu pour %s : %s (attendu %s). Arrêt.\n", $data['name'], $category->getPath(), $expectedPath));
        exit(1);
    }

    return $category;
};

foreach ($tree as $topPosition => $node) {
    $top = $ensureCategory($node, $root, $topPosition + 1, $node['url_key'], true);
    if ($top !== null) {
        $categoryIds[$node['url_key']] = (int) $top->getId();
    }
    foreach ($node['children'] as $childPosition => $child) {
        $keyPath = $node['url_key'] . '/' . $child['url_key'];
        if ($top === null) {
            $say(sprintf('Création de la sous-catégorie %s (%s)%s', $child['name'], $keyPath, in_array($keyPath, $categoriesWithProducts, true) ? '' : ', hors menu'));
            continue;
        }
        $category = $ensureCategory($child, $top, $childPosition + 1, $keyPath, in_array($keyPath, $categoriesWithProducts, true));
        if ($category !== null) {
            $categoryIds[$keyPath] = (int) $category->getId();
        }
    }
}

// ---------------------------------------------------------------------------
// 5. Création des produits
// ---------------------------------------------------------------------------

$eavConfig = $objectManager->get(EavConfig::class);
$attributeSet = $objectManager->get(AttributeSetCollectionFactory::class)->create()
    ->setEntityTypeFilter($eavConfig->getEntityType(Product::ENTITY)->getId())
    ->addFieldToFilter('attribute_set_name', 'Création')
    ->getFirstItem();
if (!$attributeSet->getId()) {
    fwrite(STDERR, "Attribute set « Création » introuvable : lancer bin/magento setup:upgrade d'abord.\n");
    exit(1);
}

foreach ($products as $data) {
    try {
        $productRepository->get($data['sku'], false, 0);
        $say(sprintf('Déjà présent, laissé tel quel : %s (%s)', $data['name'], $data['sku']));
        continue;
    } catch (NoSuchEntityException) {
        // à créer
    }

    $say(sprintf(
        'Création du produit %s (%s), 999 €, %s',
        $data['name'],
        $data['sku'],
        $activeProducts ? 'activé, stock provisoire 1' : 'désactivé, stock 0'
    ));
    if ($simulation) {
        continue;
    }

    $missing = array_diff($data['categories'], array_keys($categoryIds));
    if ($missing !== []) {
        fwrite(STDERR, sprintf("Catégories introuvables pour %s : %s. Arrêt.\n", $data['sku'], implode(', ', $missing)));
        exit(1);
    }

    /** @var Product $product */
    $product = $productFactory->create();
    $product->setStoreId(0)
        ->setSku($data['sku'])
        ->setTypeId(Type::TYPE_SIMPLE)
        ->setAttributeSetId((int) $attributeSet->getId())
        ->setWebsiteIds([1])
        ->setStatus($activeProducts ? Status::STATUS_ENABLED : Status::STATUS_DISABLED)
        ->setVisibility(Visibility::VISIBILITY_BOTH)
        ->setName($data['name'])
        ->setUrlKey($data['url_key'])
        ->setPrice(999.00)
        ->setTaxClassId(2)
        ->setCategoryIds(array_map(fn (string $key): int => $categoryIds[$key], $data['categories']))
        ->setShortDescription($data['short_description'])
        ->setDescription($data['description'])
        ->setMetaDescription($data['short_description'])
        ->setData('composition', $data['composition'] ?? null)
        ->setData('dimensions', $data['dimensions'] ?? null)
        ->setData('entretien', $data['entretien'] ?? null)
        ->setData('serie_limitee', 0)
        ->setData('home_featured', $data['home_featured'] ?? 0)
        ->setStockData([
            'use_config_manage_stock' => 1,
            'manage_stock' => 1,
            'qty' => $activeProducts ? 1 : 0,
            'is_in_stock' => $activeProducts ? 1 : 0,
        ]);
    $productRepository->save($product);
}

// ---------------------------------------------------------------------------
// 6. Présentation de Céline : « À propos » et bloc d'accueil `home_story`
// ---------------------------------------------------------------------------

$pages = $pageRepository->getList($criteriaBuilder->addFilter('identifier', 'a-propos')->create())->getItems();
foreach ($pages as $page) {
    $content = (string) $page->getContent();
    if (!str_contains($content, $aboutPlaceholder)) {
        $say('« À propos » déjà rédigée ou retouchée : laissée telle quelle');
        continue;
    }
    $say('« À propos » : présentation de Céline et métadonnées SEO');
    if ($simulation) {
        continue;
    }
    $page->setContent(str_replace($aboutPlaceholder, $aboutText, $content));
    if (trim((string) $page->getMetaTitle()) === '') {
        $page->setMetaTitle($aboutMetaTitle);
    }
    if (trim((string) $page->getMetaDescription()) === '') {
        $page->setMetaDescription($aboutMetaDescription);
    }
    $pageRepository->save($page);
}

try {
    $block = $blockRepository->getById('home_story');
    $content = (string) $block->getContent();
    if (!str_contains($content, $storyPlaceholder)) {
        $say('Bloc home_story déjà retouché : laissé tel quel');
    } else {
        $say('Bloc home_story : présentation courte de Céline');
        if (!$simulation) {
            $block->setContent(str_replace($storyPlaceholder, $storyText, $content));
            $blockRepository->save($block);
        }
    }
} catch (NoSuchEntityException) {
    $say('Bloc home_story absent : rien à faire');
}

echo "\nTerminé. Puis : bin/magento cache:clean (et indexer:reindex si les index ne sont pas planifiés).\n";
