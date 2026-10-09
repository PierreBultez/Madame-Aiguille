<?php
/**
 * Reporte l'identifiant du point relais de l'adresse du panier sur l'adresse de la commande.
 *
 * Un fieldset ne suffit pas : ToOrderAddress repasse par populateWithArray(), qui ignore
 * toute clé sans setter dans OrderAddressInterface.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Plugin\Sales;

use MadameAiguille\Checkout\Model\RelayPoint\Assignment;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\ToOrderAddress;
use Magento\Sales\Api\Data\OrderAddressInterface;

class CopyRelayPointToOrder
{
    /**
     * @param array<string, mixed> $data
     */
    public function afterConvert(
        ToOrderAddress $subject,
        OrderAddressInterface $result,
        Address $object,
        $data = []
    ): OrderAddressInterface {
        if ($object->getData(Assignment::ADDRESS_FIELD) && $result instanceof \Magento\Framework\DataObject) {
            $result->setData(Assignment::ADDRESS_FIELD, $object->getData(Assignment::ADDRESS_FIELD));
        }

        return $result;
    }
}
