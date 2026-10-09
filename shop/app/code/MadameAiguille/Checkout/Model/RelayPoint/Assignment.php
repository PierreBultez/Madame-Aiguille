<?php
/**
 * Livraison en point relais : l'adresse du point choisi devient l'adresse de livraison.
 *
 * Le nom et le téléphone de la cliente sont conservés (Mondial Relay en a besoin sur l'étiquette) ;
 * la société porte le nom et l'identifiant du point, ce qui le rend lisible partout où Magento
 * affiche l'adresse de livraison : administration, emails, facture, compte client.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Model\RelayPoint;

use MadameAiguille\Checkout\Api\Data\RelayPointInterface;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\AddressInterface;

class Assignment
{
    public const CARRIER_CODE = 'tablerate';
    public const SHIPPING_METHOD = 'tablerate_bestway';
    public const ADDRESS_FIELD = 'madameaiguille_relay_point_id';

    private const ID_PATTERN = '/^([A-Z]{2})-[0-9A-Z]{5,6}$/';
    private const MAX_LENGTH = 128;

    public function __construct(
        private readonly AllowedCountries $allowedCountries
    ) {
    }

    /**
     * @throws LocalizedException
     */
    public function validate(?RelayPointInterface $point): void
    {
        if ($point === null || $point->getId() === '') {
            throw new LocalizedException(__('Please choose your relay point.'));
        }

        $street = array_filter(array_map('trim', $point->getStreet()));
        $fields = [$point->getName(), $point->getPostcode(), $point->getCity(), ...$street];
        $tooLong = array_filter($fields, static fn (string $value): bool => mb_strlen($value) > self::MAX_LENGTH);

        if (!preg_match(self::ID_PATTERN, $point->getId(), $matches)
            || $matches[1] !== $point->getCountryId()
            || !in_array($point->getCountryId(), $this->allowedCountries->getAllowedCountries(), true)
            || trim($point->getName()) === '' || $street === []
            || trim($point->getPostcode()) === '' || trim($point->getCity()) === ''
            || $tooLong !== []
        ) {
            throw new LocalizedException(__('This relay point cannot be used. Please choose another one.'));
        }
    }

    /**
     * @param AddressInterface&\Magento\Framework\DataObject $address
     */
    public function apply(AddressInterface $address, RelayPointInterface $point): void
    {
        $address->setCompany((string) __('%1 — Relay point %2', trim($point->getName()), $point->getId()));
        $address->setStreet(array_values(array_filter(array_map('trim', $point->getStreet()))));
        $address->setPostcode(trim($point->getPostcode()));
        $address->setCity(trim($point->getCity()));
        $address->setCountryId($point->getCountryId());
        $address->setRegionId(null);
        $address->setRegion(null);
        $address->setRegionCode(null);
        // Ce n'est plus une adresse de la cliente : ni rattachée à son carnet, ni enregistrée dedans
        $address->setCustomerAddressId(null);
        $address->setSaveInAddressBook(0);
        $address->setData(self::ADDRESS_FIELD, $point->getId());
    }

    /**
     * @param AddressInterface&\Magento\Framework\DataObject $address
     */
    public function clear(AddressInterface $address): void
    {
        $address->setData(self::ADDRESS_FIELD, null);
    }
}
