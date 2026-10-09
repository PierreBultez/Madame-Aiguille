<?php
/**
 * Quand l'expédition fait passer une commande à l'état « complete », Magento lui donne le statut natif
 * « complete ». On le remplace par le statut du parcours Madame Aiguille :
 * « Expédiée » pour un colis, « Livrée » pour un retrait à l'atelier (l'expédition y vaut remise en main propre).
 *
 * Un statut posé à la main par Céline n'est jamais touché.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Plugin\Sales;

use MadameAiguille\Checkout\Model\Carrier\Pickup;
use MadameAiguille\Theme\Model\Order\StatusConfig;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Handler\State;

class StatusOnCompletion
{
    public function afterCheck(State $subject, State $result, Order $order): State
    {
        if ($order->getState() === Order::STATE_COMPLETE && $order->getStatus() === Order::STATE_COMPLETE) {
            $order->setStatus(
                $order->getShippingMethod() === Pickup::SHIPPING_METHOD
                    ? StatusConfig::DELIVERED
                    : StatusConfig::SHIPPED
            );
        }

        return $result;
    }
}
