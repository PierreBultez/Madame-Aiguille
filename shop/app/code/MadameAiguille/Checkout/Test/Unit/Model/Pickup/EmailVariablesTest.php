<?php
declare(strict_types=1);

namespace MadameAiguille\Checkout\Test\Unit\Model\Pickup;

use MadameAiguille\Checkout\Model\Pickup\Config;
use MadameAiguille\Checkout\Model\Pickup\EmailVariables;
use MadameAiguille\Checkout\Model\Pickup\SlotFormatter;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class EmailVariablesTest extends TestCase
{
    private function variables(string $slot, string $paymentMethod): array
    {
        $address = $this->createStub(Address::class);
        $address->method('getData')->willReturn($slot);
        $payment = $this->createStub(Payment::class);
        $payment->method('getMethod')->willReturn($paymentMethod);
        $order = $this->createStub(Order::class);
        $order->method('getShippingAddress')->willReturn($address);
        $order->method('getPayment')->willReturn($payment);
        $formatter = $this->createStub(SlotFormatter::class);
        $formatter->method('format')->willReturn('jeudi 5 novembre, 10 h');

        return (new EmailVariables($this->createStub(Config::class), $formatter))->getVariables($order);
    }

    public function testAnOrderPaidOnSiteAsksForPaymentAtPickup(): void
    {
        self::assertSame('1', $this->variables('2026-11-05 10:00', 'cashondelivery')['pickup_pay_on_site']);
    }

    public function testAnOrderPaidOnlineIsAnnouncedAsAlreadyPaid(): void
    {
        self::assertSame('', $this->variables('2026-11-05 10:00', 'stripe_payments')['pickup_pay_on_site']);
    }

    public function testAnOrderWithoutAppointmentHasNoPickupVariables(): void
    {
        self::assertSame([], $this->variables('', 'cashondelivery'));
    }
}
