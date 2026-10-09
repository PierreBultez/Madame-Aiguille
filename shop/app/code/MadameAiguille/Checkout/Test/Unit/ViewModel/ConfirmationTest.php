<?php
declare(strict_types=1);

namespace MadameAiguille\Checkout\Test\Unit\ViewModel;

use MadameAiguille\Checkout\Model\Pickup\EmailVariables;
use MadameAiguille\Checkout\Model\RelayPoint\Assignment;
use MadameAiguille\Checkout\ViewModel\Confirmation;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use PHPUnit\Framework\TestCase;

class ConfirmationTest extends TestCase
{
    public function testPickupUsesExistingFormatterAndConfiguredLocation(): void
    {
        $order = $this->createStub(Order::class);
        $variables = $this->createMock(EmailVariables::class);
        $variables->expects(self::once())->method('getVariables')->with($order)
            ->willReturn(['pickup_slot' => 'jeudi 15 octobre à 10 h 00', 'pickup_location_name' => 'Atelier']);
        $viewModel = new Confirmation($this->createStub(Session::class), $variables);

        self::assertSame([
            'type' => 'pickup',
            'pickup_slot' => 'jeudi 15 octobre à 10 h 00',
            'pickup_location_name' => 'Atelier',
        ], $viewModel->getDeliveryForOrder($order));
    }

    public function testRelayUsesOrderAddressRatherThanBillingAddress(): void
    {
        $address = $this->createStub(Address::class);
        $address->method('getData')->willReturnCallback(
            static fn ($key) => $key === Assignment::ADDRESS_FIELD ? 'FR-087807' : null
        );
        $address->method('getCompany')->willReturn('TABAC PRESSE — Point relais FR-087807');
        $address->method('getStreet')->willReturn(['66 R.N. 10']);
        $address->method('getPostcode')->willReturn('86220');
        $address->method('getCity')->willReturn('LES ORMES');
        $order = $this->createStub(Order::class);
        $order->method('getShippingAddress')->willReturn($address);
        $variables = $this->createStub(EmailVariables::class);
        $variables->method('getVariables')->willReturn([]);
        $viewModel = new Confirmation($this->createStub(Session::class), $variables);

        self::assertSame([
            'type' => 'relay',
            'name' => 'TABAC PRESSE — Point relais FR-087807',
            'street' => ['66 R.N. 10'],
            'city' => '86220 LES ORMES',
        ], $viewModel->getDeliveryForOrder($order));
    }

    public function testNoDeliveryDoesNotInventARelayOrAppointment(): void
    {
        $variables = $this->createStub(EmailVariables::class);
        $variables->method('getVariables')->willReturn([]);
        $order = $this->createStub(Order::class);
        $order->method('getShippingAddress')->willReturn(null);
        $viewModel = new Confirmation($this->createStub(Session::class), $variables);

        self::assertSame([], $viewModel->getDeliveryForOrder($order));
    }
}
