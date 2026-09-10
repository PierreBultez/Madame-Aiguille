<?php
/**
 * Déclenche les notifications propres aux statuts Madame Aiguille.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Observer;

use MadameAiguille\Theme\Model\Order\Email\StatusEmailSender;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class SendOrderStatusEmail implements ObserverInterface
{
    public function __construct(
        private readonly StatusEmailSender $statusEmailSender
    ) {
    }

    public function execute(Observer $observer): void
    {
        $order = $observer->getEvent()->getOrder();
        if (!$order instanceof Order || !$order->dataHasChangedFor('status')) {
            return;
        }

        $this->statusEmailSender->send($order);
    }
}
