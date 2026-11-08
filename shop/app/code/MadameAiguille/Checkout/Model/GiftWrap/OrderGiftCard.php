<?php
/**
 * La carte cadeau d'une commande, pour l'afficher partout où l'atelier ou la cliente la relit : compte, emails,
 * administration, PDF de facture, d'avoir et de bon de livraison. Lue sur le message cadeau natif de la commande.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Model\GiftWrap;

use Magento\GiftMessage\Model\MessageFactory;
use Magento\GiftMessage\Model\ResourceModel\Message as MessageResource;
use Magento\Sales\Model\Order;

class OrderGiftCard
{
    public function __construct(
        private readonly Config $config,
        private readonly MessageFactory $messageFactory,
        private readonly MessageResource $messageResource
    ) {
    }

    /**
     * @return array{title: string, wrapped: bool, sender: string, recipient: string, message: string}|null
     */
    public function forOrder(?Order $order): ?array
    {
        $messageId = (int) $order?->getGiftMessageId();
        if (!$messageId) {
            return null;
        }

        $message = $this->messageFactory->create();
        $this->messageResource->load($message, $messageId);
        if (trim((string) $message->getMessage()) === '') {
            return null;
        }

        $wrapped = (float) $order->getData(Config::AMOUNT) > 0;

        return [
            // Commandes du lot 11 : « Emballage et message cadeau ». Message gratuit d'avant : « Message cadeau ».
            'title' => $wrapped ? $this->config->getLabel((int) $order->getStoreId()) : (string) __('Gift Message'),
            'wrapped' => $wrapped,
            'sender' => trim((string) $message->getSender()),
            'recipient' => trim((string) $message->getRecipient()),
            'message' => trim((string) $message->getMessage()),
        ];
    }
}
