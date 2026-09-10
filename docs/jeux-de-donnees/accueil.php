<?php
/** Sélection de recette de l’accueil, sans création ni suppression de produit.
 *  Usage : php docs/jeux-de-donnees/accueil.php
 *  Pré-requis : catalogue de test présent et setup:upgrade du lot 3 appliqué.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\State;

require dirname(__DIR__, 2) . '/shop/app/bootstrap.php';
$om = Bootstrap::create(BP, $_SERVER)->getObjectManager();
$om->get(State::class)->setAreaCode('adminhtml');
$repository = $om->get(ProductRepositoryInterface::class);
$selection = ['MA-TRO-ROM', 'MA-POC-CEL', 'MA-SAC-AUR', 'MA-SAC-VER'];
foreach (array_merge($selection, ['MA-COT-CIN', 'MA-COT-DIX']) as $sku) {
    $product = $repository->get($sku, false, 0, true);
    $product->setData('home_featured', in_array($sku, $selection, true) ? 1 : 0);
    $repository->save($product);
    echo ($product->getData('home_featured') ? 'Incontournable : ' : 'Retiré de la sélection : ') . $sku . "\n";
}
