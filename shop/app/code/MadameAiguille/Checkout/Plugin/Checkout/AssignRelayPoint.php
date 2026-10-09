<?php
/**
 * Au passage de l'étape Livraison : refuse le point relais sans point valide,
 * et mémorise le point sur l'adresse du panier (appliqué à la commande par Observer\ApplyRelayPoint).
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Plugin\Checkout;

use MadameAiguille\Checkout\Model\RelayPoint\Assignment;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;

class AssignRelayPoint
{
    public function __construct(
        private readonly Assignment $assignment
    ) {
    }

    /**
     * @param int $cartId
     * @return array{0: int, 1: ShippingInformationInterface}
     * @throws InputException
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagementInterface $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ): array {
        $address = $addressInformation->getShippingAddress();
        if ($address === null) {
            return [$cartId, $addressInformation];
        }

        if ($addressInformation->getShippingCarrierCode() !== Assignment::CARRIER_CODE) {
            $this->assignment->forget($address);
            return [$cartId, $addressInformation];
        }

        $point = $addressInformation->getExtensionAttributes()?->getMadameaiguilleRelayPoint();
        try {
            $this->assignment->validate($point);
        } catch (LocalizedException $exception) {
            throw new InputException(__($exception->getMessage()));
        }
        $this->assignment->remember($address, $point);

        return [$cartId, $addressInformation];
    }
}
