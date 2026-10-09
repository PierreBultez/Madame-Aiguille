<?php
/**
 * Reporte le montant de l'emballage cadeau du panier sur la commande.
 *
 * Ni un fieldset ni un plugin sur ToOrder ne suffisent : QuoteManagement fusionne le résultat de la
 * conversion dans une commande neuve avec mergeDataObjects(), qui ne garde que les champs de l'interface.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Observer;

use MadameAiguille\Checkout\Model\GiftWrap\Config;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CopyGiftWrapToOrder implements ObserverInterface
{
    public function execute(Observer $observer): void
    {
        $quote = $observer->getEvent()->getQuote();
        $order = $observer->getEvent()->getOrder();
        $address = $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
        if ((float) $address->getData(Config::AMOUNT) <= 0) {
            return;
        }

        $order->setData(Config::AMOUNT, $address->getData(Config::AMOUNT));
        $order->setData(Config::BASE_AMOUNT, $address->getData(Config::BASE_AMOUNT));
    }
}
