<?php
declare(strict_types=1);

namespace MadameAiguille\Checkout\Model;

use MadameAiguille\Checkout\Api\Data\RelayPointInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class RelayPoint extends AbstractSimpleObject implements RelayPointInterface
{
    public function getId(): string
    {
        return (string) $this->_get('id');
    }

    public function setId(string $id): self
    {
        return $this->setData('id', $id);
    }

    public function getName(): string
    {
        return (string) $this->_get('name');
    }

    public function setName(string $name): self
    {
        return $this->setData('name', $name);
    }

    public function getStreet(): array
    {
        return (array) $this->_get('street');
    }

    public function setStreet(array $street): self
    {
        return $this->setData('street', $street);
    }

    public function getPostcode(): string
    {
        return (string) $this->_get('postcode');
    }

    public function setPostcode(string $postcode): self
    {
        return $this->setData('postcode', $postcode);
    }

    public function getCity(): string
    {
        return (string) $this->_get('city');
    }

    public function setCity(string $city): self
    {
        return $this->setData('city', $city);
    }

    public function getCountryId(): string
    {
        return (string) $this->_get('country_id');
    }

    public function setCountryId(string $countryId): self
    {
        return $this->setData('country_id', $countryId);
    }
}
