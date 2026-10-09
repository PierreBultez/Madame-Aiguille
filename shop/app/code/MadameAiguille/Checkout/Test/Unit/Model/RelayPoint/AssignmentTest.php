<?php
declare(strict_types=1);

namespace MadameAiguille\Checkout\Test\Unit\Model\RelayPoint;

use MadameAiguille\Checkout\Model\RelayPoint;
use MadameAiguille\Checkout\Model\RelayPoint\Assignment;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order\Address as OrderAddress;
use Magento\Framework\Exception\LocalizedException;
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

        return new Assignment($allowed, new Json());
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

    public function testTheCartAddressIsKeptAndThePointRemembered(): void
    {
        $quoteAddress = new DataObject(['street' => '1 rue des Lilas', 'city' => 'Tours']);

        $this->assignment()->remember($quoteAddress, $this->point());
        $point = $this->assignment()->recall($quoteAddress);

        self::assertSame('1 rue des Lilas', $quoteAddress->getData('street'));
        self::assertSame('FR-087807', $point['id']);
        self::assertSame(['66 R.N. 10'], $point['street']);

        $this->assignment()->forget($quoteAddress);
        self::assertNull($this->assignment()->recall($quoteAddress));
    }

    public function testThePointBecomesTheOrderDeliveryAddressButNotTheRecipient(): void
    {
        $quoteAddress = new DataObject();
        $this->assignment()->remember($quoteAddress, $this->point());
        $orderAddress = $this->getMockBuilder(OrderAddress::class)->disableOriginalConstructor()->onlyMethods([])->getMock();
        $orderAddress->setData([
            'firstname' => 'Lou', 'lastname' => 'Martin', 'telephone' => '0600000000',
            'street' => '1 rue des Lilas', 'postcode' => '37000', 'city' => 'Tours', 'country_id' => 'FR',
            'region_id' => 219, 'customer_address_id' => 12,
        ]);

        $this->assignment()->applyToOrderAddress($orderAddress, $this->assignment()->recall($quoteAddress));

        self::assertSame('Lou', $orderAddress->getFirstname());
        self::assertSame('0600000000', $orderAddress->getTelephone());
        self::assertSame(['66 R.N. 10'], $orderAddress->getStreet());
        self::assertSame('86220', $orderAddress->getPostcode());
        self::assertSame('LES ORMES', $orderAddress->getCity());
        self::assertStringContainsString('FR-087807', (string) $orderAddress->getCompany());
        self::assertNull($orderAddress->getData('region_id'));
        self::assertNull($orderAddress->getCustomerAddressId());
        self::assertSame('FR-087807', $orderAddress->getData(Assignment::ADDRESS_FIELD));
    }
}
