<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Test\Unit\Model\Order\Email;

use MadameAiguille\Theme\Model\Order\Email\StatusEmailSender;
use MadameAiguille\Theme\Model\Order\StatusConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[AllowMockObjectsWithoutExpectations]
class StatusEmailSenderTest extends TestCase
{
    public function testPaymentReceivedUsesTheConfiguredTemplate(): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects(self::once())->method('isSetFlag')->with(
            'sales_email/madameaiguille_payment_received/enabled',
            ScopeInterface::SCOPE_STORE,
            1
        )->willReturn(true);
        $scopeConfig->method('getValue')->willReturnCallback(
            static fn(string $path): string => str_ends_with($path, '/template')
                ? 'madameaiguille_payment_received'
                : 'sales'
        );

        $transport = $this->createMock(TransportInterface::class);
        $transport->expects(self::once())->method('sendMessage');
        $builder = $this->createMock(TransportBuilder::class);
        $builder->expects(self::once())->method('setTemplateIdentifier')
            ->with('madameaiguille_payment_received')->willReturnSelf();
        $builder->method('setTemplateOptions')->willReturnSelf();
        $builder->method('setTemplateVars')->willReturnSelf();
        $builder->method('setFromByScope')->with('sales', 1)->willReturnSelf();
        $builder->method('addTo')->with('cliente@example.test', 'Cliente Test')->willReturnSelf();
        $builder->method('getTransport')->willReturn($transport);

        $inlineTranslation = $this->createMock(StateInterface::class);
        $inlineTranslation->expects(self::once())->method('suspend');
        $inlineTranslation->expects(self::once())->method('resume');

        $store = $this->createStub(Store::class);
        $store->method('getUrl')->willReturn('https://example.test/sales/order/view/order_id/10');
        $order = $this->createStub(Order::class);
        $order->method('getStatus')->willReturn(StatusConfig::PAYMENT_RECEIVED);
        $order->method('getStoreId')->willReturn(1);
        $order->method('getCustomerEmail')->willReturn('cliente@example.test');
        $order->method('getCustomerName')->willReturn('Cliente Test');
        $order->method('getCustomerId')->willReturn(2);
        $order->method('getEntityId')->willReturn(10);
        $order->method('getStore')->willReturn($store);
        $order->method('getStatusHistories')->willReturn([]);

        $sender = new StatusEmailSender(
            $scopeConfig,
            $builder,
            $inlineTranslation,
            $this->createStub(LoggerInterface::class)
        );

        self::assertTrue($sender->send($order));
    }

    public function testUnrelatedStatusIsIgnored(): void
    {
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects(self::never())->method('isSetFlag');
        $builder = $this->createMock(TransportBuilder::class);
        $builder->expects(self::never())->method('getTransport');
        $order = $this->createStub(Order::class);
        $order->method('getStatus')->willReturn(StatusConfig::PREPARING);

        $sender = new StatusEmailSender(
            $scopeConfig,
            $builder,
            $this->createStub(StateInterface::class),
            $this->createStub(LoggerInterface::class)
        );

        self::assertFalse($sender->send($order));
    }
}
