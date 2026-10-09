<?php
declare(strict_types=1);

namespace MadameAiguille\Checkout\Test\Unit\Model\RelayPoint;

use MadameAiguille\Checkout\Model\RelayPoint;
use MadameAiguille\Checkout\Model\RelayPoint\Assignment;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Address;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class AssignmentTest extends TestCase
{
    private function assignment(): Assignment
    {
        $allowed = $this->createStub(AllowedCountries::class);
        $allowed->method('getAllowedCountries')->willReturn(['FR', 'BE', 'LU', 'MC']);

        return new Assignment($allowed);
    }

    private function point(array $data = []): RelayPoint
    {
        return new RelayPoint($data + [
            'id' => 'FR-087807',
            'name' => 'TABAC PRESSE',
            'street' => ['66 R.N. 10', ''],
            'postcode' => '86220',
            'city' => 'LES ORMES',
            'country_id' => 'FR',
        ]);
    }

    public function testAcceptsAPointOfTheSalesArea(): void
    {
        $this->assignment()->validate($this->point());
        $this->assignment()->validate($this->point(['id' => 'BE-123456', 'country_id' => 'BE']));
        $this->addToAssertionCount(2);
    }

    public function testRefusesAMissingPoint(): void
    {
        $this->expectException(LocalizedException::class);
        $this->assignment()->validate(null);
    }

    public static function invalidPoints(): array
    {
        return [
            'identifiant mal formé' => [['id' => '087807']],
            'pays de l\'identifiant différent' => [['id' => 'BE-087807']],
            'pays hors zone' => [['id' => 'CH-087807', 'country_id' => 'CH']],
            'sans nom' => [['name' => '  ']],
            'sans rue' => [['street' => ['', ' ']]],
            'sans ville' => [['city' => '']],
            'champ démesuré' => [['name' => str_repeat('A', 129)]],
        ];
    }

    #[DataProvider('invalidPoints')]
    public function testRefusesAnInvalidPoint(array $data): void
    {
        $this->expectException(LocalizedException::class);
        $this->assignment()->validate($this->point($data));
    }

    public function testThePointReplacesTheDeliveryAddressButNotTheRecipient(): void
    {
        $address = $this->getMockBuilder(Address::class)->disableOriginalConstructor()->onlyMethods([])->getMock();
        $address->setData([
            'firstname' => 'Lou', 'lastname' => 'Martin', 'telephone' => '0600000000',
            'street' => '1 rue des Lilas', 'postcode' => '37000', 'city' => 'Tours', 'country_id' => 'FR',
            'region_id' => 219, 'customer_address_id' => 12, 'save_in_address_book' => 1,
        ]);

        $this->assignment()->apply($address, $this->point());

        self::assertSame('Lou', $address->getFirstname());
        self::assertSame('0600000000', $address->getTelephone());
        self::assertSame(['66 R.N. 10'], $address->getStreet());
        self::assertSame('86220', $address->getPostcode());
        self::assertSame('LES ORMES', $address->getCity());
        self::assertStringContainsString('FR-087807', (string) $address->getCompany());
        self::assertNull($address->getData('region_id'));
        self::assertNull($address->getCustomerAddressId());
        self::assertSame(0, $address->getSaveInAddressBook());
        self::assertSame('FR-087807', $address->getData(Assignment::ADDRESS_FIELD));
    }
}
