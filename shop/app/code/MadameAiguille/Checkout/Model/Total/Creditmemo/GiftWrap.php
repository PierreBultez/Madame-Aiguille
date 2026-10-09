<?php
/**
 * L'emballage cadeau n'est remboursé qu'avec le dernier avoir, celui qui solde toute la commande :
 * un retour partiel (une création sur trois) ne rembourse pas l'emballage.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Model\Total\Creditmemo;

use MadameAiguille\Checkout\Model\GiftWrap\Config;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

class GiftWrap extends AbstractTotal
{
    /**
     * @return $this
     */
    public function collect(Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        $amount = (float) $order->getData(Config::AMOUNT);
        $base = (float) $order->getData(Config::BASE_AMOUNT);
        if ($amount <= 0 || !$creditmemo->isLast()) {
            return $this;
        }

        foreach ($order->getCreditmemosCollection() ?: [] as $previous) {
            if ($previous->getId() && (int) $previous->getState() !== Creditmemo::STATE_CANCELED
                && (float) $previous->getData(Config::AMOUNT) > 0
            ) {
                return $this;
            }
        }

        $creditmemo->setData(Config::AMOUNT, $amount)->setData(Config::BASE_AMOUNT, $base);
        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $amount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $base);

        return $this;
    }
}
