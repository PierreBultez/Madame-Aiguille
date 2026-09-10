<?php
/**
 * Madame Aiguille — jeu de données de test du catalogue (lot 2)
 *
 * Crée ou met à jour une douzaine de produits à partir des photos de
 * docs/maquettes-direction-artistique/brand/p-*.png, dans les catégories créées
 * au lot 1. Couvre tous les états du Design System : nouveauté, série limitée,
 * « Plus que 2 », épuisé, configurable à deux tailles dont une épuisée, produit
 * hors série limitée. Supprime les données d'essai Sneakers / T-Shirts / Jordan.
 *
 * Hors code applicatif : rien de tout ceci n'est un data patch, donc rien ne
 * sera créé en production. Rejouable (upsert par SKU, par nom pour les catégories).
 *
 * Usage, depuis la racine du dépôt :
 *   php docs/jeux-de-donnees/produits-test.php
 *   cd shop && bin/magento indexer:reindex && bin/magento cache:flush
 */

declare(strict_types=1);

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory as ConfigurableOptionsFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollectionFactory;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;

$shopRoot = dirname(__DIR__, 2) . '/shop';
require $shopRoot . '/app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();
$objectManager->get(State::class)->setAreaCode('adminhtml');
$objectManager->get(Registry::class)->register('isSecureArea', true);

$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productFactory = $objectManager->get(ProductFactory::class);
$categoryRepository = $objectManager->get(CategoryRepositoryInterface::class);
$categoryCollectionFactory = $objectManager->get(CategoryCollectionFactory::class);
$attributeSetCollectionFactory = $objectManager->get(AttributeSetCollectionFactory::class);
$eavConfig = $objectManager->get(EavConfig::class);
$configurableOptionsFactory = $objectManager->get(ConfigurableOptionsFactory::class);
$productLinkFactory = $objectManager->get(ProductLinkInterfaceFactory::class);

$sourceImagesDir = dirname(__DIR__) . '/maquettes-direction-artistique/brand';

// Magento n'accepte que des images situées sous pub/media : copie de travail
// (pub/media est ignoré par git).
$imagesDir = $shopRoot . '/pub/media/import/madameaiguille';
if (!is_dir($imagesDir) && !mkdir($imagesDir, 0775, true)) {
    fwrite(STDERR, "Impossible de créer {$imagesDir}\n");
    exit(1);
}
foreach (glob($sourceImagesDir . '/p-*.png') as $source) {
    copy($source, $imagesDir . '/' . basename($source));
}

// ---------------------------------------------------------------------------
// 1. Données d'essai à supprimer (Sneakers, T-Shirts, Jordan Brooklyn)
// ---------------------------------------------------------------------------

foreach (['IR5596-900', 'IV5200-010'] as $sku) {
    try {
        $productRepository->deleteById($sku);
        echo "Supprimé : produit {$sku}\n";
    } catch (NoSuchEntityException) {
        // déjà absent
    }
}

foreach (['Sneakers', 'T-Shirts'] as $categoryName) {
    $collection = $categoryCollectionFactory->create()
        ->addAttributeToFilter('name', $categoryName)
        ->addAttributeToFilter('level', 2);

    foreach ($collection as $category) {
        $categoryRepository->deleteByIdentifier((int) $category->getId());
        echo "Supprimé : catégorie {$categoryName} (#{$category->getId()})\n";
    }
}

// ---------------------------------------------------------------------------
// 2. Références : catégories par nom, attribute set « Création », options de taille
// ---------------------------------------------------------------------------

$categoryIds = [];
$categoryCollection = $categoryCollectionFactory->create()
    ->addAttributeToSelect('name')
    ->addAttributeToFilter('level', ['gt' => 1]);

foreach ($categoryCollection as $category) {
    $categoryIds[$category->getName()] = (int) $category->getId();
}

foreach (['Trousses de toilette', 'Grandes trousses', 'Petites trousses', 'Pochettes à livre', 'Cotons démaquillants', 'Petits sacs'] as $name) {
    if (!isset($categoryIds[$name])) {
        fwrite(STDERR, "Catégorie manquante : {$name}. Créer les catégories du lot 1 avant de lancer ce script.\n");
        exit(1);
    }
}

$attributeSet = $attributeSetCollectionFactory->create()
    ->setEntityTypeFilter($eavConfig->getEntityType(Product::ENTITY)->getId())
    ->addFieldToFilter('attribute_set_name', 'Création')
    ->getFirstItem();

if (!$attributeSet->getId()) {
    fwrite(STDERR, "Attribute set « Création » introuvable : lancer bin/magento setup:upgrade d'abord.\n");
    exit(1);
}
$attributeSetId = (int) $attributeSet->getId();

$tailleAttribute = $eavConfig->getAttribute(Product::ENTITY, 'taille');
$tailleOptions = [];
foreach ($tailleAttribute->getSource()->getAllOptions(false) as $option) {
    $tailleOptions[$option['label']] = (int) $option['value'];
}

// ---------------------------------------------------------------------------
// 3. Définition des produits
// ---------------------------------------------------------------------------

$yesterday = (new DateTimeImmutable('yesterday'))->format('Y-m-d');
$lastMonth = (new DateTimeImmutable('-40 days'))->format('Y-m-d');

$entretienDefaut = 'Lavage à la main à 30 °C ou éponge humide. Pas de sèche-linge : la ouatine se tasse. '
    . 'Repassage à fer doux sur l’envers.';

/**
 * Clés : sku, name, price, weight (kg), qty, serie_limitee, taille_serie,
 * categories (noms), images (fichiers p-*.png, la première est l'image principale),
 * short_description, description, news_from (facultatif), news_to (facultatif),
 * taille (option, pour les enfants du configurable), visibility (facultatif),
 * url_key (facultatif, sinon dérivée du nom), related (SKU),
 * composition, dimensions, entretien (caractéristiques ; entretien par défaut).
 */
$products = [
    // Configurable à deux tailles : Petit disponible, Grand épuisé
    [
        'sku' => 'MA-TRO-ROM',
        'composition' => 'Extérieur 100 % coton, doublure coton enduit, ouatine polyester',
        'dimensions' => 'Petit : 18 × 12 × 8 cm · Grand : 24 × 16 × 10 cm',
        'type' => Configurable::TYPE_CODE,
        'name' => 'Trousse Romantique',
        'price' => 29.00,
        'weight' => 0.19,
        'serie_limitee' => 1,
        'taille_serie' => 8,
        'categories' => ['Trousses de toilette', 'Grandes trousses'],
        'images' => ['p-trousse-romantique.png', 'p-trousse-romantique-detail.png', 'p-trousse-romantique-situation.png'],
        'short_description' => 'Coton fleuri rose poudré, doublé et matelassé à la main.',
        'description' => '<p>Une trousse de toilette au format généreux, taillée dans un coton fleuri rose poudré chiné chez un grossiste de Roubaix — il n’en restait que trois mètres, d’où la série de huit.</p>'
            . '<p>L’extérieur est matelassé à la main, ligne par ligne, ce qui lui donne son épaisseur et sa tenue. La doublure est un coton ivoire enduit, qui se nettoie d’un coup d’éponge. La fermeture éclair est en laiton vieilli, cousue avec une petite languette de cuir pour l’attraper facilement.</p>'
            . '<p>Elle tient debout toute seule quand elle est remplie, et passe dans un bagage cabine sans faire de volume.</p>',
        'children' => [
            ['sku' => 'MA-TRO-ROM-P', 'name' => 'Trousse Romantique — Petit', 'taille' => 'Petit', 'price' => 29.00, 'weight' => 0.14, 'qty' => 4],
            ['sku' => 'MA-TRO-ROM-G', 'name' => 'Trousse Romantique — Grand', 'taille' => 'Grand', 'price' => 34.00, 'weight' => 0.19, 'qty' => 0],
        ],
        'related' => ['MA-TRO-LIN', 'MA-POC-ISA', 'MA-COT-CIN', 'MA-SAC-AUR'],
    ],
    [
        'sku' => 'MA-TRO-LIN',
        'composition' => 'Lin lavé, doublure coton ivoire, fermeture éclair laiton',
        'dimensions' => '20 × 13 × 7 cm',
        'name' => 'Trousse Lin & Aiguille',
        'price' => 26.00,
        'weight' => 0.16,
        'qty' => 7,
        'serie_limitee' => 1,
        'taille_serie' => 7,
        'categories' => ['Trousses de toilette', 'Petites trousses'],
        'images' => ['p-trousse-romantique-situation.png', 'p-trousse-romantique-detail.png'],
        'short_description' => 'Lin lavé, fermeture éclair laiton.',
        'description' => '<p>Une petite trousse en lin lavé, souple et solide, doublée d’un coton ivoire. La fermeture éclair en laiton est cousue à la main.</p>',
        'related' => ['MA-TRO-ROM', 'MA-POC-CEL', 'MA-COT-CIN'],
    ],
    // Épuisé
    [
        'sku' => 'MA-TRO-PER',
        'composition' => 'Lin perle, doublure coton ivoire',
        'dimensions' => '18 × 12 × 7 cm',
        'name' => 'Trousse Lin Perle',
        'price' => 22.00,
        'weight' => 0.12,
        'qty' => 0,
        'serie_limitee' => 1,
        'taille_serie' => 6,
        'categories' => ['Trousses de toilette', 'Petites trousses'],
        'images' => ['p-trousse-lin-perle.png'],
        'short_description' => 'Lin perle, doublure coton ivoire.',
        'description' => '<p>Série de six trousses en lin perle, écoulée au printemps. Le coupon est terminé : ce modèle ne sera pas recousu à l’identique.</p>',
        'related' => ['MA-TRO-ROM', 'MA-TRO-LIN'],
    ],
    // Nouveauté
    [
        'sku' => 'MA-POC-CEL',
        'composition' => 'Coton imprimé, doublure coton ivoire, aimants plats',
        'dimensions' => '23 × 16 cm, pour un livre de poche',
        'name' => 'Pochette à Livre Céleste',
        'price' => 24.00,
        'weight' => 0.11,
        'qty' => 8,
        'serie_limitee' => 1,
        'taille_serie' => 8,
        'news_from' => $yesterday,
        'categories' => ['Pochettes à livre'],
        'images' => ['p-pochette-celeste.png'],
        'short_description' => 'Coton fleuri bleu, fermeture aimantée.',
        'description' => '<p>Une pochette pour protéger votre livre dans un sac, doublée d’un coton ivoire et fermée par deux aimants plats. Le rabat est piqué d’un surfil ton sur ton.</p>',
        'related' => ['MA-POC-ISA', 'MA-POC-NOM', 'MA-TRO-LIN'],
    ],
    [
        'sku' => 'MA-POC-ISA',
        'composition' => 'Coton imprimé, doublure coton ivoire, aimants plats',
        'dimensions' => '23 × 16 cm, pour un livre de poche',
        'name' => 'Pochette à Livre Isabelle',
        'price' => 24.00,
        'weight' => 0.11,
        'qty' => 4,
        'serie_limitee' => 1,
        'taille_serie' => 6,
        'categories' => ['Pochettes à livre'],
        'images' => ['p-pochette-isabelle.png', 'p-trousse-romantique-detail.png', 'p-trousse-romantique-situation.png'],
        'short_description' => 'Coton fleuri terracotta, fermeture aimantée.',
        'description' => '<p>Une pochette pour protéger votre livre dans un sac, doublée d’un coton ivoire et fermée par deux aimants plats. Le rabat est piqué d’un surfil ton sur ton.</p>',
        'related' => ['MA-POC-CEL', 'MA-POC-NOM', 'MA-COT-CIN', 'MA-SAC-AUR'],
    ],
    // Épuisé
    [
        'sku' => 'MA-POC-ROS',
        'composition' => 'Coton imprimé, doublure coton ivoire',
        'dimensions' => '23 × 16 cm',
        'name' => 'Pochette Bouton de Rose',
        'price' => 19.00,
        'weight' => 0.09,
        'qty' => 0,
        'serie_limitee' => 1,
        'taille_serie' => 5,
        'categories' => ['Pochettes à livre'],
        'images' => ['p-pochette-bouton-rose.png'],
        'short_description' => 'Coton imprimé boutons de rose.',
        'description' => '<p>Série de cinq pochettes, écoulée en avril.</p>',
        'related' => ['MA-POC-CEL', 'MA-POC-ISA'],
    ],
    // Nouveauté
    [
        'sku' => 'MA-POC-NOM',
        'composition' => 'Coton matelassé, doublure coton, fermeture éclair',
        'dimensions' => '12 × 9 cm · 16 × 11 cm · 20 × 14 cm',
        'name' => 'Pochettes Nomades',
        'price' => 18.00,
        'weight' => 0.10,
        'qty' => 10,
        'serie_limitee' => 1,
        'taille_serie' => 10,
        'news_from' => $yesterday,
        'categories' => ['Pochettes à livre', 'Petits sacs'],
        'images' => ['p-pochettes-nomades.png'],
        'short_description' => 'Lot de trois pochettes matelassées.',
        'description' => '<p>Trois pochettes matelassées de tailles croissantes, pour ranger câbles, bijoux ou trousse de secours. Vendues par lot.</p>',
        'related' => ['MA-POC-CEL', 'MA-POC-ISA', 'MA-SAC-VER'],
    ],
    // Rareté : plus que 2
    [
        'sku' => 'MA-COT-CIN',
        'composition' => 'Éponge de bambou et coton imprimé',
        'dimensions' => 'Ø 10 cm, lot de 5',
        'name' => 'Cotons Démaquillants',
        'url_key' => 'cotons-demaquillants-lot-de-cinq', // la clé dérivée du nom serait celle de la catégorie
        'price' => 12.00,
        'weight' => 0.06,
        'qty' => 2,
        'serie_limitee' => 1,
        'taille_serie' => 10,
        'categories' => ['Cotons démaquillants'],
        'images' => ['p-cotons-demaquillants.png'],
        'short_description' => 'Lot de cinq, avec pochette de lavage.',
        'description' => '<p>Cinq cotons lavables en éponge de bambou et coton imprimé, livrés dans une pochette filet pour le lavage en machine.</p>',
        'related' => ['MA-COT-DIX', 'MA-TRO-LIN', 'MA-POC-ISA'],
    ],
    // Hors série limitée, stock confortable : aucun badge
    [
        'sku' => 'MA-COT-DIX',
        'composition' => 'Éponge de bambou et coton imprimé',
        'dimensions' => 'Ø 10 cm, lot de 10',
        'name' => 'Cotons Démaquillants — lot de dix',
        'price' => 20.00,
        'weight' => 0.11,
        'qty' => 15,
        'serie_limitee' => 0,
        'taille_serie' => null,
        'categories' => ['Cotons démaquillants'],
        'images' => ['p-cotons-demaquillants.png'],
        'short_description' => 'Lot de dix, éponge de bambou, imprimés assortis.',
        'description' => '<p>Dix cotons lavables, imprimés assortis selon les coupons disponibles. Ligne permanente de l’atelier, refaite au fil des chutes.</p>',
        'related' => ['MA-COT-CIN', 'MA-POC-NOM'],
    ],
    [
        'sku' => 'MA-SAC-AUR',
        'composition' => 'Coton fleuri, doublure coton, ouatine, bandoulière coton',
        'dimensions' => '22 × 16 × 8 cm, bandoulière 120 cm',
        'name' => 'Sac Aurora Mini',
        'price' => 32.00,
        'weight' => 0.24,
        'qty' => 5,
        'serie_limitee' => 1,
        'taille_serie' => 5,
        'news_from' => $lastMonth,
        'news_to' => $yesterday,
        'categories' => ['Petits sacs'],
        'images' => ['p-sac-aurora-mini.png'],
        'short_description' => 'Coton fleuri ivoire, bandoulière amovible.',
        'description' => '<p>Un petit sac à bandoulière en coton fleuri ivoire, doublé et matelassé, avec une poche intérieure zippée.</p>',
        'related' => ['MA-SAC-VER', 'MA-TRO-ROM', 'MA-POC-ISA'],
    ],
    [
        'sku' => 'MA-SAC-VER',
        'composition' => 'Coton enduit, doublure coton ivoire, ouatine',
        'dimensions' => '22 × 16 × 8 cm, bandoulière 120 cm',
        'name' => 'Sac Aurora Verveine',
        'price' => 34.00,
        'weight' => 0.26,
        'qty' => 6,
        'serie_limitee' => 1,
        'taille_serie' => 6,
        'categories' => ['Petits sacs'],
        'images' => ['p-sac-verveine.png'],
        'short_description' => 'Coton enduit vert, doublure ivoire.',
        'description' => '<p>Le même patron que l’Aurora Mini, dans un coton enduit vert verveine qui se nettoie d’un coup d’éponge.</p>',
        'related' => ['MA-SAC-AUR', 'MA-POC-NOM', 'MA-TRO-LIN'],
    ],
];

// ---------------------------------------------------------------------------
// 4. Création / mise à jour
// ---------------------------------------------------------------------------

/**
 * Charge le produit par SKU ou en prépare un nouveau.
 */
$loadOrCreate = function (string $sku, string $typeId) use ($productRepository, $productFactory, $attributeSetId): Product {
    try {
        /** @var Product $product */
        $product = $productRepository->get($sku, true, 0, true);
    } catch (NoSuchEntityException) {
        $product = $productFactory->create();
        $product->setSku($sku);
    }

    $product->setStoreId(0)
        ->setTypeId($typeId)
        ->setAttributeSetId($attributeSetId)
        ->setWebsiteIds([1])
        ->setStatus(Status::STATUS_ENABLED);

    return $product;
};

$slugify = fn(string $name): string => trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(
    iconv('UTF-8', 'ASCII//TRANSLIT', str_replace(['’', '&'], ['', 'et'], $name))
)), '-');

$applyCommonData = function (Product $product, array $data) use ($categoryIds, $imagesDir, $slugify): void {
    $product->setName($data['name'])
        ->setUrlKey($data['url_key'] ?? $slugify($data['name']))
        ->setPrice($data['price'])
        ->setWeight($data['weight'])
        ->setTaxClassId(2)
        ->setVisibility($data['visibility'] ?? Visibility::VISIBILITY_BOTH)
        ->setCategoryIds(array_map(fn($name) => $categoryIds[$name], $data['categories'] ?? []))
        ->setData('serie_limitee', $data['serie_limitee'] ?? 0)
        ->setData('taille_serie', $data['taille_serie'] ?? null)
        ->setData('news_from_date', $data['news_from'] ?? null)
        ->setData('news_to_date', $data['news_to'] ?? null)
        ->setShortDescription($data['short_description'] ?? null)
        ->setDescription($data['description'] ?? null)
        ->setData('composition', $data['composition'] ?? null)
        ->setData('dimensions', $data['dimensions'] ?? null)
        ->setData('entretien', $data['entretien'] ?? $entretienDefaut);

    if (isset($data['taille'])) {
        $product->setData('taille', $data['taille']);
    }

    if (isset($data['qty'])) {
        $product->setStockData([
            'use_config_manage_stock' => 1,
            'manage_stock' => 1,
            'qty' => $data['qty'],
            'is_in_stock' => $data['qty'] > 0 ? 1 : 0,
        ]);
    }

    // Images : uniquement à la création, pour ne pas dupliquer la galerie au rejeu
    if (!$product->getId() && !empty($data['images'])) {
        foreach ($data['images'] as $index => $file) {
            $path = $imagesDir . '/' . $file;
            if (!is_file($path)) {
                fwrite(STDERR, "Image introuvable : {$path}\n");
                continue;
            }
            $roles = $index === 0 ? ['image', 'small_image', 'thumbnail'] : [];
            $product->addImageToMediaGallery($path, $roles, false, false);
        }
    }
};

$saved = [];

foreach ($products as $data) {
    $typeId = $data['type'] ?? Type::TYPE_SIMPLE;

    if ($typeId === Configurable::TYPE_CODE) {
        $childIds = [];

        foreach ($data['children'] as $childData) {
            $child = $loadOrCreate($childData['sku'], Type::TYPE_SIMPLE);
            $applyCommonData($child, array_merge($data, $childData, [
                'visibility' => Visibility::VISIBILITY_NOT_VISIBLE,
                'categories' => [],
                'images' => $data['images'],
                'taille' => $tailleOptions[$childData['taille']],
                'news_from' => null,
                'news_to' => null,
            ]));
            $child = $productRepository->save($child);
            $childIds[] = (int) $child->getId();
            echo "OK : {$childData['sku']} (#{$child->getId()})\n";
        }

        $parent = $loadOrCreate($data['sku'], Configurable::TYPE_CODE);
        $applyCommonData($parent, $data);
        $parent->setStockData(['use_config_manage_stock' => 1, 'manage_stock' => 1, 'is_in_stock' => 1]);

        $options = $configurableOptionsFactory->create([[
            'attribute_id' => $tailleAttribute->getId(),
            'code' => 'taille',
            'label' => 'Taille',
            'position' => 0,
            'values' => array_map(
                fn(array $child) => ['label' => $child['taille'], 'attribute_id' => $tailleAttribute->getId(), 'value_index' => $tailleOptions[$child['taille']]],
                $data['children']
            ),
        ]]);

        $extension = $parent->getExtensionAttributes();
        $extension->setConfigurableProductOptions($options);
        $extension->setConfigurableProductLinks($childIds);
        $parent->setExtensionAttributes($extension);

        $parent = $productRepository->save($parent);
        $saved[$data['sku']] = $parent;
        echo "OK : {$data['sku']} configurable (#{$parent->getId()})\n";
        continue;
    }

    $product = $loadOrCreate($data['sku'], $typeId);
    $applyCommonData($product, $data);
    $product = $productRepository->save($product);
    $saved[$data['sku']] = $product;
    echo "OK : {$data['sku']} (#{$product->getId()})\n";
}

// ---------------------------------------------------------------------------
// 5. Produits liés (« Vous aimerez aussi ») — second passage, tous les SKU existent
// ---------------------------------------------------------------------------

foreach ($products as $data) {
    if (empty($data['related'])) {
        continue;
    }

    /** @var Product $product */
    $product = $productRepository->get($data['sku'], true, 0, true);
    $links = [];

    foreach ($data['related'] as $position => $relatedSku) {
        $links[] = $productLinkFactory->create()
            ->setSku($data['sku'])
            ->setLinkedProductSku($relatedSku)
            ->setLinkType('related')
            ->setPosition($position + 1);
    }

    $product->setProductLinks($links);
    $productRepository->save($product);
    echo "Liés : {$data['sku']} → " . implode(', ', $data['related']) . "\n";
}

echo "\nTerminé. Lancer maintenant : cd shop && bin/magento indexer:reindex && bin/magento cache:flush\n";
