<?php
/**
 * Coupe le carrier natif freeshipping : le franco est désormais porté par le point relais lui-même
 * (Plugin\Shipping\FreeRelayAboveThreshold), au seuil de Général › Madame Aiguille › Panier.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class DisableNativeFreeShipping implements DataPatchInterface
{
    public function __construct(
        private readonly WriterInterface $configWriter
    ) {
    }

    public function apply(): self
    {
        $this->configWriter->save('carriers/freeshipping/active', '0');

        return $this;
    }

    public static function getDependencies(): array
    {
        return [ConfigureRelayShipping::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
