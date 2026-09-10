<?php
/**
 * Installe les statuts du parcours de commande Madame Aiguille.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use MadameAiguille\Theme\Model\Order\StatusConfig;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CreateOrderStatuses implements DataPatchInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly StatusConfig $statusConfig
    ) {
    }

    public function apply(): self
    {
        $connection = $this->moduleDataSetup->getConnection();
        $statusRows = [];
        $stateRows = [];

        foreach ($this->statusConfig->getAll() as $code => $configuration) {
            $statusRows[] = [
                'status' => $code,
                'label' => $configuration['label'],
            ];
            $stateRows[] = [
                'status' => $code,
                'state' => $configuration['state'],
                'is_default' => 0,
                'visible_on_front' => 1,
            ];
        }

        $connection->startSetup();
        try {
            $connection->insertOnDuplicate(
                $this->moduleDataSetup->getTable('sales_order_status'),
                $statusRows,
                ['label']
            );
            $connection->insertOnDuplicate(
                $this->moduleDataSetup->getTable('sales_order_status_state'),
                $stateRows,
                ['is_default', 'visible_on_front']
            );
        } finally {
            $connection->endSetup();
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
