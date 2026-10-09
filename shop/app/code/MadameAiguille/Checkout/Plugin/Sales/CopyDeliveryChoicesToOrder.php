<?php
/**
 * Reporte les choix de livraison du tunnel (point relais, créneau de retrait) de l'adresse du panier
 * sur l'adresse de la commande.
 *
 * Un fieldset ne suffit pas : ToOrderAddress repasse par populateWithArray(), qui ignore
 * toute clé sans setter dans OrderAddressInterface.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Plugin\Sales;

use MadameAiguille\Checkout\Model\RelayPoint\Assignment;
use MadameAiguille\Checkout\Plugin\Checkout\AssignPickupSlot;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\ToOrderAddress;
use Magento\Sales\Api\Data\OrderAddressInterface;

class CopyDeliveryChoicesToOrder
{
    private const FIELDS = [Assignment::ADDRESS_FIELD, AssignPickupSlot::ADDRESS_FIELD];

    /**
     * @param array<string, mixed> $data
     */
    public function afterConvert(
        ToOrderAddress $subject,
        OrderAddressInterface $result,
        Address $object,
        $data = []
    ): OrderAddressInterface {
        if (!$result instanceof DataObject) {
            return $result;
        }

        foreach (self::FIELDS as $field) {
            if ($object->getData($field)) {
                $result->setData($field, $object->getData($field));
            }
        }

        return $result;
    }
}
