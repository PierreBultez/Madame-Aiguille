<?php
/**
 * Adresse enregistrée (compte, carnet alimenté par le tunnel, administration) : une région facultative et masquée
 * pour le pays — la France, `general/region/display_all` à « Non » — est effacée (lot 11, A5).
 *
 * Sans cela, une région posée autrefois (Indre-et-Loire pour une adresse à Courthézon, 84350, présélectionnée par
 * `tax/defaults/region` jusqu'au lot 11) restait collée à l'adresse : le formulaire du compte la renvoie dans un champ
 * masqué que la cliente ne peut ni voir ni vider. Enregistrer l'adresse suffit désormais à la corriger.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Plugin\Customer;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Directory\Helper\Data as DirectoryHelper;

class DropHiddenOptionalRegion
{
    public function __construct(
        private readonly DirectoryHelper $directoryHelper,
        private readonly RegionInterfaceFactory $regionFactory
    ) {
    }

    /**
     * @param AddressRepositoryInterface $subject
     * @param AddressInterface $address
     * @return array{0: AddressInterface}
     */
    public function beforeSave(AddressRepositoryInterface $subject, AddressInterface $address): array
    {
        $country = (string) $address->getCountryId();
        if ($country === ''
            || $this->directoryHelper->isRegionRequired($country)
            || $this->directoryHelper->isShowNonRequiredState()
        ) {
            return [$address];
        }

        $address->setRegionId(null);
        $address->setRegion($this->regionFactory->create());

        return [$address];
    }
}
