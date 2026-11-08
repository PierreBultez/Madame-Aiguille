<?php
/**
 * Ne plus présélectionner la région d'une nouvelle adresse (lot 11, A5).
 *
 * `tax/defaults/region` (219, Indre-et-Loire, posé par ConfigureSalesFoundations) est exposé au tunnel par
 * `Tax\Model\TaxConfigProvider` sous `defaultRegionId`, que le tunnel Luma et l'estimateur du panier appliquent à
 * toute nouvelle adresse, même si le champ région est masqué en France : une adresse à Pertuis (84350) partait en
 * « Indre-et-Loire ». La boutique n'a aucune règle de taxe ; « * » (0) suffit. Une autre valeur choisie dans
 * l'administration n'est pas touchée.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Tax\Model\Config as TaxConfig;

class StopPresettingTaxRegion implements DataPatchInterface
{
    private const PRESET_REGION_ID = '219';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly WriterInterface $configWriter
    ) {
    }

    public function apply(): self
    {
        $current = (string) $this->scopeConfig->getValue(TaxConfig::CONFIG_XML_PATH_DEFAULT_REGION);
        if ($current === self::PRESET_REGION_ID) {
            $this->configWriter->save(TaxConfig::CONFIG_XML_PATH_DEFAULT_REGION, '0');
        }

        return $this;
    }

    public static function getDependencies(): array
    {
        return [ConfigureSalesFoundations::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
