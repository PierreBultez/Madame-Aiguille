<?php
/**
 * Ligne « Emballage cadeau » transmise à Mollie, sur le modèle du générateur Gift Wrapping d'Adobe Commerce
 * livré par Mollie : sans elle, la somme des lignes ne correspondrait pas au total de la commande.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Model\Mollie;

use MadameAiguille\Checkout\Model\GiftWrap\Config;
use Magento\Sales\Api\Data\OrderInterface;
use Mollie\Api\Types\OrderLineType;
use Mollie\Payment\Helper\General;
use Mollie\Payment\Service\Order\Lines\Generator\GeneratorInterface;

class GiftWrapLine implements GeneratorInterface
{
    public function __construct(
        private readonly General $mollieHelper,
        private readonly Config $config
    ) {
    }

    public function process(OrderInterface $order, array $orderLines): array
    {
        $useBase = (bool) $this->mollieHelper->useBaseCurrency(storeId($order->getStoreId()));
        $amount = (float) $order->getData($useBase ? Config::BASE_AMOUNT : Config::AMOUNT);
        if ($amount < 0.01) {
            return $orderLines;
        }

        $currency = $useBase ? $order->getBaseCurrencyCode() : $order->getOrderCurrencyCode();
        $orderLines[] = [
            'type' => OrderLineType::SURCHARGE,
            'description' => $this->config->getLabel((int) $order->getStoreId()),
            'quantity' => 1,
            'unitPrice' => $this->mollieHelper->getAmountArray($currency, $amount),
            'totalAmount' => $this->mollieHelper->getAmountArray($currency, $amount),
            'vatRate' => '0.00',
            'vatAmount' => $this->mollieHelper->getAmountArray($currency, '0.00'),
        ];

        return $orderLines;
    }
}
