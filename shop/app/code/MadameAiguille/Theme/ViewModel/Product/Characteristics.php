<?php
/**
 * Madame Aiguille — lignes du tableau « Caractéristiques » (fiche produit)
 *
 * Composition, dimensions, poids (formaté en grammes ou kilogrammes, par
 * taille pour un configurable), série (« 8 exemplaires »), entretien.
 * Une ligne vide n'est pas renvoyée.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\ViewModel\Product;

use MadameAiguille\Theme\Setup\Patch\Data\CreateProductDetailAttributes as Attributes;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Characteristics implements ArgumentInterface
{
    public function __construct(
        private readonly LimitedSeries $limitedSeries
    ) {
    }

    /**
     * @return array<int, array{label: Phrase, value: string, multiline: bool}>
     */
    public function getRows(Product $product): array
    {
        $rows = [];

        $this->addRow($rows, __('Composition'), (string) $product->getData(Attributes::ATTRIBUTE_COMPOSITION));
        $this->addRow($rows, __('Dimensions'), (string) $product->getData(Attributes::ATTRIBUTE_DIMENSIONS));
        $this->addRow($rows, __('Poids'), $this->getWeightLabel($product));

        $size = $this->limitedSeries->getSeriesSize($product);
        if ($size !== null) {
            $this->addRow($rows, __('Série'), (string) __('%1 exemplaires', $size));
        }

        $this->addRow($rows, __('Entretien'), (string) $product->getData(Attributes::ATTRIBUTE_ENTRETIEN), true);

        return $rows;
    }

    /**
     * « 140 g » · « 1,2 kg » ; pour un configurable : « 140 g (Petit) · 190 g (Grand) ».
     */
    public function getWeightLabel(Product $product): string
    {
        if ($product->getTypeId() === Configurable::TYPE_CODE) {
            $typeInstance = $product->getTypeInstance();
            $parts = [];

            if ($typeInstance instanceof Configurable) {
                foreach ($typeInstance->getUsedProducts($product) as $child) {
                    /** @var Product $child */
                    $weight = $this->formatWeight((float) $child->getWeight());
                    $taille = $child->getAttributeText('taille');

                    if ($weight !== '') {
                        $parts[] = $taille ? sprintf('%s (%s)', $weight, $taille) : $weight;
                    }
                }
            }

            return implode(' · ', array_unique($parts));
        }

        return $this->formatWeight((float) $product->getWeight());
    }

    private function formatWeight(float $kg): string
    {
        if ($kg <= 0) {
            return '';
        }

        if ($kg < 1) {
            return (string) __('%1 g', (int) round($kg * 1000));
        }

        return (string) __('%1 kg', rtrim(rtrim(number_format($kg, 2, ',', ' '), '0'), ','));
    }

    /**
     * @param array<int, array{label: Phrase, value: string, multiline: bool}> $rows
     */
    private function addRow(array &$rows, Phrase $label, string $value, bool $multiline = false): void
    {
        $value = trim($value);

        if ($value === '') {
            return;
        }

        $rows[] = ['label' => $label, 'value' => $value, 'multiline' => $multiline];
    }
}
