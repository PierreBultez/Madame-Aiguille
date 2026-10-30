<?php
/**
 * Une commande en retrait payée sur place reste à l'état « new » jusqu'à sa facture, et l'administration ne
 * propose que les statuts rattachés à l'état courant : « Prête pour retrait », rattaché à « processing », était
 * donc introuvable avant le paiement. Il est rattaché aussi à « new », sans en devenir le statut par défaut.
 *
 * Facturer ensuite fait passer la commande à « processing » / « Paiement reçu », puis l'expédition à « Livrée ».
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Setup\Patch\Data;

use MadameAiguille\Theme\Model\Order\StatusConfig;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sales\Model\Order;

class AllowReadyForPickupBeforePayment implements DataPatchInterface
{
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    public function apply(): self
    {
        $this->moduleDataSetup->getConnection()->insertOnDuplicate(
            $this->moduleDataSetup->getTable('sales_order_status_state'),
            [[
                'status' => StatusConfig::READY_FOR_PICKUP,
                'state' => Order::STATE_NEW,
                'is_default' => 0,
                'visible_on_front' => 1,
            ]],
            ['visible_on_front']
        );

        return $this;
    }

    public static function getDependencies(): array
    {
        return [\MadameAiguille\Theme\Setup\Patch\Data\CreateOrderStatuses::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
