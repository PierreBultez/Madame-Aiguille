<?php
/**
 * Livraison en point relais.
 *
 * Pendant le tunnel, l'adresse du panier reste celle de la cliente : le point validé est seulement
 * mémorisé à côté (identifiant + coordonnées). Sinon, au rechargement de la page, Luma préremplirait
 * le formulaire avec l'adresse du point, et elle se retrouverait en facturation ou sur un retrait.
 *
 * À la validation de la commande, l'adresse du point devient l'adresse de livraison de la commande.
 * Le nom et le téléphone de la cliente sont conservés (Mondial Relay en a besoin sur l'étiquette) ;
 * la société porte le nom et l'identifiant du point, ce qui le rend lisible partout où Magento
 * affiche l'adresse de livraison : administration, emails, facture, compte client.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Model\RelayPoint;

use MadameAiguille\Checkout\Api\Data\RelayPointInterface;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\OrderAddressInterface;

class Assignment
{
    public const CARRIER_CODE = 'tablerate';
    public const SHIPPING_METHOD = 'tablerate_bestway';
    public const ADDRESS_FIELD = 'madameaiguille_relay_point_id';
    public const DETAILS_FIELD = 'madameaiguille_relay_point';

    private const ID_PATTERN = '/^([A-Z]{2})-[0-9A-Z]{5,6}$/';
    private const MAX_LENGTH = 128;

    public function __construct(
        private readonly AllowedCountries $allowedCountries,
        private readonly Json $json
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
     * Mémorise le point validé sur l'adresse du panier, sans la modifier.
     */
    public function remember(DataObject $quoteAddress, RelayPointInterface $point): void
    {
        $quoteAddress->setData(self::ADDRESS_FIELD, $point->getId());
        $quoteAddress->setData(self::DETAILS_FIELD, $this->json->serialize([
            'id' => $point->getId(),
            'name' => trim($point->getName()),
            'street' => array_values(array_filter(array_map('trim', $point->getStreet()))),
            'postcode' => trim($point->getPostcode()),
            'city' => trim($point->getCity()),
            'country_id' => $point->getCountryId(),
        ]));
    }

    public function forget(DataObject $quoteAddress): void
    {
        $quoteAddress->setData(self::ADDRESS_FIELD, null);
        $quoteAddress->setData(self::DETAILS_FIELD, null);
    }

    /**
     * Point mémorisé sur l'adresse du panier, ou null.
     *
     * @return array<string, mixed>|null id, name, street, postcode, city, country_id
     */
    public function recall(DataObject $quoteAddress): ?array
    {
        $details = (string) $quoteAddress->getData(self::DETAILS_FIELD);
        if ($details === '' || !$quoteAddress->getData(self::ADDRESS_FIELD)) {
            return null;
        }
        $point = $this->json->unserialize($details);

        $isCurrent = is_array($point) && ($point['id'] ?? null) === $quoteAddress->getData(self::ADDRESS_FIELD);

        return $isCurrent ? $point : null;
    }

    /**
     * L'adresse du point devient l'adresse de livraison de la commande.
     *
     * @param OrderAddressInterface&DataObject $orderAddress
     * @param array<string, mixed> $point tel que rendu par recall()
     */
    public function applyToOrderAddress(OrderAddressInterface $orderAddress, array $point): void
    {
        $orderAddress->setCompany((string) __('%1 — Relay point %2', $point['name'], $point['id']));
        $orderAddress->setStreet($point['street']);
        $orderAddress->setPostcode($point['postcode']);
        $orderAddress->setCity($point['city']);
        $orderAddress->setCountryId($point['country_id']);
        $orderAddress->setRegionId(null);
        $orderAddress->setRegion(null);
        $orderAddress->setRegionCode(null);
        // Ce n'est pas une adresse du carnet de la cliente
        $orderAddress->setCustomerAddressId(null);
        $orderAddress->setData(self::ADDRESS_FIELD, $point['id']);
    }
}
