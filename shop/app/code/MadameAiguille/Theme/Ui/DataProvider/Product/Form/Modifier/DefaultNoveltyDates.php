<?php
/**
 * Madame Aiguille — période de nouveauté proposée à la création d'un produit
 *
 * Dans le formulaire d'un produit en création, les dates « Définir le produit
 * comme nouveau à partir de / jusqu'au » sont préremplies : aujourd'hui, et un
 * mois plus tard (fuseau du magasin). Céline peut les modifier ou les vider
 * avant d'enregistrer : seul ce qui est enregistré compte. Un produit existant
 * n'est jamais touché, ni une saisie renvoyée après une erreur d'enregistrement
 * (le modifier passe avant celui des attributs, qui la restaure).
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class DefaultNoveltyDates extends AbstractModifier
{
    public const FROM = 'news_from_date';
    public const TO = 'news_to_date';
    public const PERIOD = '+1 month';

    public function __construct(
        private readonly LocatorInterface $locator,
        private readonly TimezoneInterface $timezone
    ) {
    }

    public function modifyData(array $data): array
    {
        $product = $this->locator->getProduct();
        if ($product->getId()) {
            return $data;
        }

        $values = $data[''][self::DATA_SOURCE_DEFAULT] ?? [];
        if (!empty($values[self::FROM]) || !empty($values[self::TO])) {
            return $data;
        }

        $today = $this->timezone->date();
        $data[''][self::DATA_SOURCE_DEFAULT][self::FROM] = $today->format('Y-m-d');
        $data[''][self::DATA_SOURCE_DEFAULT][self::TO] = (clone $today)->modify(self::PERIOD)->format('Y-m-d');

        return $data;
    }

    public function modifyMeta(array $meta): array
    {
        return $meta;
    }
}
