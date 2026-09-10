<?php
/**
 * Envoi des emails associés à un changement de statut de commande.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Model\Order\Email;

use MadameAiguille\Theme\Model\Order\StatusConfig;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class StatusEmailSender
{
    private const CONFIGURATION = [
        StatusConfig::PAYMENT_RECEIVED => 'madameaiguille_payment_received',
        StatusConfig::READY_FOR_PICKUP => 'madameaiguille_ready_for_pickup',
    ];

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly TransportBuilder $transportBuilder,
        private readonly StateInterface $inlineTranslation,
        private readonly LoggerInterface $logger
    ) {
    }

    public function send(Order $order): bool
    {
        $configurationCode = self::CONFIGURATION[(string) $order->getStatus()] ?? null;
        $storeId = (int) $order->getStoreId();
        if ($configurationCode === null || !$this->isEnabled($configurationCode, $storeId)) {
            return false;
        }

        $email = trim((string) $order->getCustomerEmail());
        if ($email === '') {
            return false;
        }

        $configurationPath = 'sales_email/' . $configurationCode . '/';
        $templateId = (string) $this->scopeConfig->getValue(
            $configurationPath . 'template',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $sender = (string) $this->scopeConfig->getValue(
            $configurationPath . 'identity',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $this->inlineTranslation->suspend();
        try {
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $storeId])
                ->setTemplateVars($this->getTemplateVariables($order))
                ->setFromByScope($sender, $storeId)
                ->addTo($email, (string) $order->getCustomerName())
                ->getTransport();
            $transport->sendMessage();

            return true;
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Échec de la notification de statut de commande Madame Aiguille.',
                ['exception' => $exception, 'order_id' => $order->getEntityId()]
            );

            return false;
        } finally {
            $this->inlineTranslation->resume();
        }
    }

    private function isEnabled(string $configurationCode, int $storeId): bool
    {
        return $this->scopeConfig->isSetFlag(
            'sales_email/' . $configurationCode . '/enabled',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function getTemplateVariables(Order $order): array
    {
        $orderUrl = null;
        if ((int) $order->getCustomerId() > 0 && $order->getStore()) {
            $orderUrl = $order->getStore()->getUrl(
                'sales/order/view',
                ['order_id' => $order->getEntityId(), '_nosid' => true]
            );
        }

        return [
            'order' => $order,
            'store' => $order->getStore(),
            'customer_name' => $order->getCustomerName(),
            'order_url' => $orderUrl,
            'pickup_details' => $this->getPickupDetails($order),
        ];
    }

    private function getPickupDetails(Order $order): string
    {
        foreach ($order->getStatusHistories() ?? [] as $history) {
            if ($history->getStatus() !== StatusConfig::READY_FOR_PICKUP
                || !$history->getIsVisibleOnFront()
            ) {
                continue;
            }

            $comment = trim((string) $history->getComment());
            if ($comment !== '') {
                return $comment;
            }
        }

        return '';
    }
}
