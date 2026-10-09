<?php
/**
 * Au passage de l'étape Livraison : refuse le point relais sans point choisi,
 * et remplace l'adresse de livraison par celle du point.
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
            $this->assignment->clear($address);
            return [$cartId, $addressInformation];
        }

        $point = $addressInformation->getExtensionAttributes()?->getMadameaiguilleRelayPoint();
        try {
            $this->assignment->validate($point);
        } catch (LocalizedException $exception) {
            throw new InputException(__($exception->getMessage()));
        }
        $this->assignment->apply($address, $point);

        return [$cartId, $addressInformation];
    }
}
