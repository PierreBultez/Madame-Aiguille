<?php
/**
 * Mondial Relay en point relais, transporteur unique (call Céline du 11/09/2026) :
 * le carrier natif tablerate porte la grille au poids, le forfait provisoire de 5 € par article est coupé.
 *
 * La grille elle-même s'importe avec bin/magento madameaiguille:shipping:import-rates.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use MadameAiguille\Theme\Model\Shipping\TableRateImporter;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class ConfigureRelayShipping implements DataPatchInterface
{
    private const VALUES = [
        'carriers/tablerate/active' => '1',
        'carriers/tablerate/title' => 'Mondial Relay',
        'carriers/tablerate/name' => 'Livraison en point relais',
        'carriers/tablerate/condition_name' => TableRateImporter::CONDITION_NAME,
        'carriers/tablerate/specificerrmsg' => 'La livraison en point relais n\'est pas disponible pour cette adresse.',
        'carriers/tablerate/sort_order' => '10',
        'carriers/flatrate/active' => '0',
    ];

    public function __construct(
        private readonly WriterInterface $configWriter
    ) {
    }

    public function apply(): self
    {
        foreach (self::VALUES as $path => $value) {
            $this->configWriter->save($path, $value);
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
