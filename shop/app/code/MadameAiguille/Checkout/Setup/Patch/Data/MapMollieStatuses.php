<?php
/**
 * Mollie fait vivre les statuts du lot 7 : une commande créée attend son paiement
 * (« En attente de paiement »), un paiement capté la fait passer à « Paiement reçu »,
 * ce qui déclenche la notification du module Theme (Observer\SendOrderStatusEmail).
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Setup\Patch\Data;

use MadameAiguille\Theme\Model\Order\StatusConfig;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class MapMollieStatuses implements DataPatchInterface
{
    public function __construct(
        private readonly WriterInterface $configWriter
    ) {
    }

    public function apply(): self
    {
        $this->configWriter->save('payment/mollie_general/order_status_pending', StatusConfig::PENDING_PAYMENT);
        $this->configWriter->save('payment/mollie_general/order_status_processing', StatusConfig::PAYMENT_RECEIVED);

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
