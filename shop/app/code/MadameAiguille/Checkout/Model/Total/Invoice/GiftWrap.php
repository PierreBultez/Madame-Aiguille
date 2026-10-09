<?php
/**
 * L'emballage cadeau est facturé en entier sur la première facture de la commande.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Model\Total\Invoice;

use MadameAiguille\Checkout\Model\GiftWrap\Config;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

class GiftWrap extends AbstractTotal
{
    /**
     * @return $this
     */
    public function collect(Invoice $invoice)
    {
        $order = $invoice->getOrder();
        $amount = (float) $order->getData(Config::AMOUNT);
        $base = (float) $order->getData(Config::BASE_AMOUNT);
        if ($amount <= 0) {
            return $this;
        }

        foreach ($order->getInvoiceCollection() as $previous) {
            if ($previous->getId() && (int) $previous->getState() !== Invoice::STATE_CANCELED
                && (float) $previous->getData(Config::AMOUNT) > 0
            ) {
                return $this;
            }
        }

        $invoice->setData(Config::AMOUNT, $amount)->setData(Config::BASE_AMOUNT, $base);
        $invoice->setGrandTotal($invoice->getGrandTotal() + $amount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $base);

        return $this;
    }
}
