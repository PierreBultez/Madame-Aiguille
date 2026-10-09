<?php
declare(strict_types=1);

namespace MadameAiguille\Checkout\Test\Unit\Observer;

use MadameAiguille\Checkout\Model\Carrier\Pickup;
use MadameAiguille\Checkout\Observer\RestrictPaymentToDelivery;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class RestrictPaymentToDeliveryTest extends TestCase
{
    private function isAvailable(string $paymentCode, string $shippingMethod, bool $initially = true): bool
    {
        $address = $this->createStub(Address::class);
        $address->method('getShippingMethod')->willReturn($shippingMethod);
        $quote = $this->createStub(Quote::class);
        $quote->method('isVirtual')->willReturn(false);
        $quote->method('getShippingAddress')->willReturn($address);
        $method = $this->createStub(MethodInterface::class);
        $method->method('getCode')->willReturn($paymentCode);
        $result = new DataObject(['is_available' => $initially]);

        (new RestrictPaymentToDelivery())->execute(new Observer(['event' => new Event([
            'result' => $result,
            'quote' => $quote,
            'method_instance' => $method,
        ])]));

        return (bool) $result->getData('is_available');
    }

    public static function cases(): array
    {
        return [
            'retrait : paiement sur place' => ['cashondelivery', Pickup::SHIPPING_METHOD, true],
            'retrait : pas de carte en ligne' => ['mollie_methods_creditcard', Pickup::SHIPPING_METHOD, false],
            'point relais : carte en ligne' => ['mollie_methods_creditcard', 'tablerate_bestway', true],
            'point relais : pas de paiement sur place' => ['cashondelivery', 'tablerate_bestway', false],
            'commande à 0 € : toujours possible' => ['free', Pickup::SHIPPING_METHOD, true],
        ];
    }

    #[DataProvider('cases')]
    public function testOnePaymentFamilyPerDeliveryMode(string $payment, string $shipping, bool $expected): void
    {
        self::assertSame($expected, $this->isAvailable($payment, $shipping));
    }

    public function testNeverReEnablesAMethodSwitchedOffElsewhere(): void
    {
        self::assertFalse($this->isAvailable('cashondelivery', Pickup::SHIPPING_METHOD, false));
    }
}
