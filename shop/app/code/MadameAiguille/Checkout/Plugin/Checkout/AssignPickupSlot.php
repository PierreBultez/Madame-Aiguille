<?php
/**
 * Au passage de l'étape Livraison : un retrait à l'atelier exige un créneau encore libre.
 * La place n'est prise qu'à la validation de la commande (Observer\ReservePickupSlot).
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Plugin\Checkout;

use MadameAiguille\Checkout\Model\Carrier\Pickup;
use MadameAiguille\Checkout\Model\Pickup\Availability;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Framework\Exception\InputException;

class AssignPickupSlot
{
    public const ADDRESS_FIELD = 'madameaiguille_pickup_slot';

    public function __construct(
        private readonly Availability $availability
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

        if ($addressInformation->getShippingCarrierCode() !== Pickup::CODE) {
            $address->setData(self::ADDRESS_FIELD, null);
            return [$cartId, $addressInformation];
        }

        $slot = (string) $addressInformation->getExtensionAttributes()?->getMadameaiguillePickupSlot();
        if ($slot === '') {
            throw new InputException(__('Please choose your pickup time.'));
        }
        if (!$this->availability->isAvailable($slot)) {
            throw new InputException(__('This pickup time is no longer available. Please choose another one.'));
        }
        $address->setData(self::ADDRESS_FIELD, $slot);

        return [$cartId, $addressInformation];
    }
}
