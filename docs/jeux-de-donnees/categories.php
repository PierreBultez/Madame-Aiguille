<?php
/**
 * Madame Aiguille — catégories du catalogue (structure issue des maquettes)
 *
 * Crée ou met à jour l'arborescence sous « Default Category » :
 *   Trousses de toilette (> Grandes trousses, Petites trousses),
 *   Pochettes à livre, Cotons démaquillants, Petits sacs.
 * Repérage par clé d'URL : rejouable sans doublon. Ne supprime jamais rien.
 *
 * Hors code applicatif : les catégories définitives seront saisies en admin
 * avec Céline. Usage, depuis la racine du dépôt :
 *   php docs/jeux-de-donnees/categories.php
 *   cd shop && bin/magento indexer:reindex && bin/magento cache:flush
 */

declare(strict_types=1);

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\State;

$shopRoot = dirname(__DIR__, 2) . '/shop';
require $shopRoot . '/app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();
$objectManager->get(State::class)->setAreaCode('adminhtml');

$categoryFactory = $objectManager->get(CategoryFactory::class);
$collectionFactory = $objectManager->get(CategoryCollectionFactory::class);

/** Racine « Default Category » du store par défaut */
$rootId = (int) $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)
    ->getStore('default')->getRootCategoryId();

/**
 * name, url_key, position, enfants (facultatif)
 */
$tree = [
    ['Trousses de toilette', 'trousses-de-toilette', 1, [
        ['Grandes trousses', 'grandes-trousses', 1],
        ['Petites trousses', 'petites-trousses', 2],
    ]],
    ['Pochettes à livre', 'pochettes-a-livre', 2],
    ['Cotons démaquillants', 'cotons-demaquillants', 3],
    ['Petits sacs', 'petits-sacs', 4],
];

/**
 * Crée ou met à jour une catégorie sous $parent. Le chemin est déduit par
 * Magento du chemin du parent (setPath(parent) puis ajout de l'identifiant à
 * l'enregistrement) ; on le vérifie ensuite, sans quoi le script s'arrête.
 */
$upsert = function (array $data, Category $parent) use ($categoryFactory, $collectionFactory): Category {
    [$name, $urlKey, $position] = $data;

    $existing = $collectionFactory->create()
        ->addAttributeToFilter('url_key', $urlKey)
        ->addAttributeToFilter('parent_id', (int) $parent->getId())
        ->setPageSize(1)
        ->getFirstItem();

    /** @var Category $category */
    $category = $existing->getId() ? $categoryFactory->create()->load((int) $existing->getId()) : $categoryFactory->create();

    if (!$category->getId()) {
        $category->setParentId((int) $parent->getId());
        $category->setPath($parent->getPath());
        $category->setUrlKey($urlKey);
    }

    $category->setStoreId(0)
        ->setName($name)
        ->setIsActive(true)
        ->setIncludeInMenu(true)
        ->setIsAnchor(true)
        ->setDisplayMode(Category::DM_PRODUCT)
        ->setPosition($position);

    $category->save();

    $expectedPath = $parent->getPath() . '/' . $category->getId();
    if ($category->getPath() !== $expectedPath) {
        fwrite(STDERR, sprintf("Chemin inattendu pour %s : %s (attendu %s). Arrêt.\n", $name, $category->getPath(), $expectedPath));
        exit(1);
    }

    printf("%s : %s (#%d, chemin %s)\n", $existing->getId() ? 'MAJ' : 'Créé', $name, $category->getId(), $category->getPath());

    return $category;
};

$root = $categoryFactory->create()->load($rootId);
if (!$root->getId()) {
    fwrite(STDERR, "Catégorie racine #{$rootId} introuvable.\n");
    exit(1);
}

foreach ($tree as $node) {
    $category = $upsert($node, $root);
    foreach ($node[3] ?? [] as $childNode) {
        $upsert($childNode, $category);
    }
}

echo "\nTerminé. Lancer ensuite php docs/jeux-de-donnees/produits-test.php (réaffecte les produits), puis :\n";
echo "cd shop && bin/magento indexer:reindex && bin/magento cache:flush\n";
