<?php
declare(strict_types=1);

namespace MadameAiguille\Checkout\Test\Unit\Observer;

use MadameAiguille\Checkout\Observer\SubscribeNewsletter;
use MadameAiguille\Checkout\Plugin\Checkout\CaptureNewsletterConsent;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[AllowMockObjectsWithoutExpectations]
class SubscribeNewsletterTest extends TestCase
{
    public function testUncheckedBoxLeavesExistingSubscriptionAlone(): void
    {
        $manager = $this->createMock(SubscriptionManagerInterface::class);
        $manager->expects(self::never())->method('subscribe');
        $manager->expects(self::never())->method('unsubscribe');
        $this->observer($manager)->execute($this->orderEvent(false));
    }

    public function testDisabledNewsletterDoesNotSubscribe(): void
    {
        $manager = $this->createMock(SubscriptionManagerInterface::class);
        $manager->expects(self::never())->method('subscribe');
        $this->observer($manager, false)->execute($this->orderEvent(true));
    }

    public function testOptInUsesNativeSubscriptionAndOrderStore(): void
    {
        $manager = $this->createMock(SubscriptionManagerInterface::class);
        $manager->expects(self::once())->method('subscribe')
            ->with('recette@example.invalid', 1)->willReturn($this->createStub(Subscriber::class));
        $this->observer($manager)->execute($this->orderEvent(true));
    }

    public function testNewsletterFailureDoesNotBreakCreatedOrder(): void
    {
        $manager = $this->createMock(SubscriptionManagerInterface::class);
        $manager->method('subscribe')->willThrowException(new \RuntimeException('SMTP unavailable'));
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');
        $this->observer($manager, true, $logger)->execute($this->orderEvent(true));
    }

    public function testGuestSubscriptionDisabledIsRespectedOnServer(): void
    {
        $manager = $this->createMock(SubscriptionManagerInterface::class);
        $manager->expects(self::never())->method('subscribe');
        $this->observer($manager, guestAllowed: false)->execute($this->orderEvent(true));
    }

    public function testCustomerOptInIsAttachedToCustomerAccount(): void
    {
        $manager = $this->createMock(SubscriptionManagerInterface::class);
        $manager->expects(self::never())->method('subscribe');
        $manager->expects(self::once())->method('subscribeCustomer')
            ->with(42, 1)->willReturn($this->createStub(Subscriber::class));
        $this->observer($manager, guestAllowed: false)->execute($this->orderEvent(true, 42));
    }

    private function observer(
        SubscriptionManagerInterface $manager,
        bool $enabled = true,
        ?LoggerInterface $logger = null,
        bool $guestAllowed = true
    ): SubscribeNewsletter {
        $config = $this->createStub(ScopeConfigInterface::class);
        $config->method('isSetFlag')->willReturnCallback(
            static fn (string $path): bool => $path === 'newsletter/general/active' ? $enabled : $guestAllowed
        );

        return new SubscribeNewsletter($manager, $config, $logger ?? $this->createStub(LoggerInterface::class));
    }

    private function orderEvent(bool $consent, ?int $customerId = null): Observer
    {
        $payment = $this->createStub(Payment::class);
        $payment->method('getAdditionalInformation')->willReturn($consent);
        $order = $this->createStub(Order::class);
        $order->method('getPayment')->willReturn($payment);
        $order->method('getCustomerEmail')->willReturn('recette@example.invalid');
        $order->method('getStoreId')->willReturn(1);
        $order->method('getCustomerId')->willReturn($customerId);

        return new Observer(['event' => new Event(['order' => $order])]);
    }
}
