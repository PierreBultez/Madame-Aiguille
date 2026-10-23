<?php
/**
 * Stripe ne connaît pas les statuts du lot 7 : il pose le statut par défaut de l'état Magento
 * (Order\Config::getStateDefaultStatus), parfois par une requête directe qui ne déclenche aucun évènement.
 * « En attente de paiement » et « Paiement reçu » deviennent donc les statuts par défaut de leurs états :
 * une commande payée passe à « Paiement reçu », ce qui déclenche la notification du module Theme.
 *
 * Effet voulu sur le retrait payé sur place : une commande encore « nouvelle » facturée par Céline
 * passe aussi à « Paiement reçu » au lieu du « processing » natif.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Setup\Patch\Data;

use MadameAiguille\Theme\Model\Order\StatusConfig;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Sales\Model\Order;

class UseOwnStatusesAsStateDefaults implements DataPatchInterface
{
    public const DEFAULTS = [
        Order::STATE_PENDING_PAYMENT => StatusConfig::PENDING_PAYMENT,
        Order::STATE_PROCESSING => StatusConfig::PAYMENT_RECEIVED,
    ];

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    public function apply(): self
    {
        $connection = $this->moduleDataSetup->getConnection();
        $table = $this->moduleDataSetup->getTable('sales_order_status_state');

        $connection->startSetup();
        try {
            foreach (self::DEFAULTS as $state => $status) {
                $connection->update($table, ['is_default' => 0], ['state = ?' => $state]);
                $connection->update($table, ['is_default' => 1], ['state = ?' => $state, 'status = ?' => $status]);
            }
        } finally {
            $connection->endSetup();
        }

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
