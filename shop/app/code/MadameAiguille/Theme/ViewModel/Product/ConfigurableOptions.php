<?php
/**
 * Madame Aiguille — options d'un configurable vues par le bouton « Ajouter au panier »
 *
 * Renvoie le libellé vitrine de chaque attribut à choisir (« taille »,
 * « couleur »), indexé par identifiant d'attribut comme le JSON du sélecteur
 * Hyvä, pour que l'aide sous le bouton nomme ce qu'il reste à choisir.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\ViewModel\Product;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class ConfigurableOptions implements ArgumentInterface
{
    /**
     * Libellés en minuscules, dans l'ordre du sélecteur ; tableau vide hors configurable.
     *
     * @return array<string, string> identifiant d'attribut => libellé
     */
    public function getAttributeLabels(Product $product): array
    {
        $type = $product->getTypeInstance();
        if (!$type instanceof Configurable) {
            return [];
        }

        $labels = [];
        foreach ($type->getConfigurableAttributes($product) as $attribute) {
            $productAttribute = $attribute->getProductAttribute();
            if ($productAttribute === null) {
                continue;
            }
            $label = trim((string) $productAttribute->getStoreLabel());
            if ($label !== '') {
                $labels[(string) $attribute->getAttributeId()] = mb_strtolower($label);
            }
        }

        return $labels;
    }
}
