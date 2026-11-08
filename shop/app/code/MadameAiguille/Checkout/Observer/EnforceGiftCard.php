<?php
/**
 * Dernier contrôle avant l'enregistrement de la commande : l'emballage et le message vont ensemble.
 *
 * - Emballage facturé : la carte (De la part de, Pour, message) doit être complète, sinon la commande est refusée.
 * - Message sans emballage (saisi dans le panier avant le lot 11, ou par l'API) : il est retiré de la commande,
 *   qui passe normalement.
 *
 * L'étape Livraison (Plugin\Checkout\AssignGiftWrap) garde déjà cette règle ; ce contrôle couvre les autres chemins.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Observer;

use MadameAiguille\Checkout\Model\GiftWrap\Config;
use MadameAiguille\Checkout\Model\GiftWrap\GiftCard;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\GiftMessage\Model\MessageFactory;
use Magento\GiftMessage\Model\ResourceModel\Message as MessageResource;

class EnforceGiftCard implements ObserverInterface
{
    public function __construct(
        private readonly MessageFactory $messageFactory,
        private readonly MessageResource $messageResource
    ) {
    }

    /**
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        $quote = $observer->getEvent()->getQuote();
        $order = $observer->getEvent()->getOrder();
        $address = $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
        $wrapped = (float) $address->getData(Config::AMOUNT) > 0;
        // Lu sur le panier : Magento_GiftMessage le recopie sur la commande dans ce même événement.
        $messageId = (int) $quote->getGiftMessageId();

        if (!$wrapped) {
            if ($messageId) {
                $quote->setGiftMessageId(null);
                $order->setGiftMessageId(null);
            }
            return;
        }

        $message = $this->messageFactory->create();
        if ($messageId) {
            $this->messageResource->load($message, $messageId);
        }
        if (!GiftCard::isComplete($message->getSender(), $message->getRecipient(), $message->getMessage())) {
            throw new LocalizedException(
                __('To add gift wrapping, please fill in who it is from, who it is for and your message.')
            );
        }
    }
}
