<?php
/**
 * Ligne « Emballage cadeau » du panier : ajoutée au total quand la cliente l'a demandée.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Model\Total\Quote;

use MadameAiguille\Checkout\Model\GiftWrap\Config;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;

class GiftWrap extends AbstractTotal
{
    public function __construct(
        private readonly Config $config,
        private readonly PriceCurrencyInterface $priceCurrency
    ) {
        $this->setCode(Config::TOTAL_CODE);
    }

    /**
     * @return $this
     */
    public function collect(Quote $quote, ShippingAssignmentInterface $shippingAssignment, Total $total)
    {
        parent::collect($quote, $shippingAssignment, $total);

        $total->setData(Config::AMOUNT, 0)->setData(Config::BASE_AMOUNT, 0);
        $address = $shippingAssignment->getShipping()->getAddress();
        if (!count($shippingAssignment->getItems())
            || $address->getAddressType() !== Address::TYPE_SHIPPING
            || !$quote->getData(Config::QUOTE_FLAG)
            || !$this->config->isEnabled((int) $quote->getStoreId())
        ) {
            return $this;
        }

        $base = $this->config->getPrice((int) $quote->getStoreId());
        $amount = $this->priceCurrency->convertAndRound($base, $quote->getStore());

        $total->setTotalAmount($this->getCode(), $amount);
        $total->setBaseTotalAmount($this->getCode(), $base);
        $total->setData(Config::AMOUNT, $amount)->setData(Config::BASE_AMOUNT, $base);

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function fetch(Quote $quote, Total $total)
    {
        $amount = (float) $total->getData(Config::AMOUNT);
        if ($amount <= 0) {
            return [];
        }

        return [
            'code' => $this->getCode(),
            // Le convertisseur des segments de l'API ne rend le titre que si c'est une Phrase
            'title' => new Phrase($this->config->getLabel((int) $quote->getStoreId())),
            'value' => $amount,
        ];
    }

    public function getLabel()
    {
        return __('Gift wrapping');
    }
}
