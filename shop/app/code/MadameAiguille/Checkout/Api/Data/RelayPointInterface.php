<?php
/**
 * Point relais choisi dans le widget Mondial Relay, transmis avec les informations de livraison.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Api\Data;

interface RelayPointInterface
{
    /**
     * @return string identifiant « pays-numéro », ex. FR-087807
     */
    public function getId(): string;

    /**
     * @param string $id
     * @return $this
     */
    public function setId(string $id): self;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self;

    /**
     * @return string[]
     */
    public function getStreet(): array;

    /**
     * @param string[] $street
     * @return $this
     */
    public function setStreet(array $street): self;

    /**
     * @return string
     */
    public function getPostcode(): string;

    /**
     * @param string $postcode
     * @return $this
     */
    public function setPostcode(string $postcode): self;

    /**
     * @return string
     */
    public function getCity(): string;

    /**
     * @param string $city
     * @return $this
     */
    public function setCity(string $city): self;

    /**
     * @return string code pays ISO 2
     */
    public function getCountryId(): string;

    /**
     * @param string $countryId
     * @return $this
     */
    public function setCountryId(string $countryId): self;
}
