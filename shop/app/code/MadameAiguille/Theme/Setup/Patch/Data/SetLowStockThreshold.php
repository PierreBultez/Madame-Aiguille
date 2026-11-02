<?php
/**
 * Alerte de stock bas native (lot 8) : Magento Open Source n'envoie pas d'email, il liste dans *Rapports › Produits ›
 * Stock faible* les produits sous le seuil de notification. Au défaut de 1, seules les créations épuisées y
 * paraissent ; à 2, la dernière pièce d'une série est signalée. Réglable ensuite produit par produit.
 *
 * Ne remplace pas un seuil déjà choisi dans l'administration.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class SetLowStockThreshold implements DataPatchInterface
{
    public const PATH = 'cataloginventory/item_options/notify_stock_qty';
    public const THRESHOLD = '2';

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly WriterInterface $configWriter
    ) {
    }

    public function apply(): self
    {
        $connection = $this->moduleDataSetup->getConnection();
        $select = $connection->select()
            ->from($this->moduleDataSetup->getTable('core_config_data'), ['config_id'])
            ->where('path = ?', self::PATH);
        if (!$connection->fetchOne($select)) {
            $this->configWriter->save(self::PATH, self::THRESHOLD);
        }

        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
