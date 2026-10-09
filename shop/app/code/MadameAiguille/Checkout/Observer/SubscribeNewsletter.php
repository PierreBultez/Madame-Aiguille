<?php
/** Une commande réussie avec opt-in déclenche l'inscription native, avec sa confirmation. */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Observer;

use MadameAiguille\Checkout\Plugin\Checkout\CaptureNewsletterConsent;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class SubscribeNewsletter implements ObserverInterface
{
    public function __construct(
        private readonly SubscriptionManagerInterface $subscriptionManager,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(Observer $observer): void
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        if (!$order->getPayment()->getAdditionalInformation(CaptureNewsletterConsent::CONSENT_KEY)
            || !$this->scopeConfig->isSetFlag('newsletter/general/active', ScopeInterface::SCOPE_STORE, $order->getStoreId())
        ) {
            return;
        }

        $customerId = (int) $order->getCustomerId();
        if (!$customerId && !$this->scopeConfig->isSetFlag(
            'newsletter/subscription/allow_guest_subscribe',
            ScopeInterface::SCOPE_STORE,
            $order->getStoreId()
        )) {
            return;
        }

        try {
            if ($customerId) {
                $this->subscriptionManager->subscribeCustomer($customerId, (int) $order->getStoreId());
            } else {
                $this->subscriptionManager->subscribe((string) $order->getCustomerEmail(), (int) $order->getStoreId());
            }
        } catch (\Throwable $exception) {
            // Une panne email/newsletter ne doit jamais transformer une commande créée en erreur de paiement.
            $this->logger->error('Inscription newsletter du tunnel échouée.', [
                'order_id' => $order->getId(),
                'exception' => $exception,
            ]);
        }
    }
}
