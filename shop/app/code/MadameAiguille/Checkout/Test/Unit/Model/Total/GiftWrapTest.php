<?php
declare(strict_types=1);

namespace MadameAiguille\Checkout\Test\Unit\Model\Total;

use MadameAiguille\Checkout\Model\GiftWrap\Config;
use MadameAiguille\Checkout\Model\Total\Creditmemo\GiftWrap as CreditmemoGiftWrap;
use MadameAiguille\Checkout\Model\Total\Invoice\GiftWrap as InvoiceGiftWrap;
use MadameAiguille\Checkout\Model\Total\Quote\GiftWrap as QuoteGiftWrap;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class GiftWrapTest extends TestCase
{
    private function config(bool $enabled = true): Config
    {
        $scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $scopeConfig->method('isSetFlag')->willReturn($enabled);
        $scopeConfig->method('getValue')->willReturnMap([
            ['madameaiguille/gift_wrap/price', 'store', 1, '2.00'],
            ['madameaiguille/gift_wrap/label', 'store', 1, 'Emballage cadeau'],
        ]);

        return new Config($scopeConfig);
    }

    private function collectQuote(bool $requested, bool $enabled = true): Total
    {
        $priceCurrency = $this->createStub(PriceCurrencyInterface::class);
        $priceCurrency->method('convertAndRound')->willReturnArgument(0);

        $quote = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()
            ->onlyMethods(['getStoreId', 'getStore'])->getMock();
        $quote->method('getStoreId')->willReturn(1);
        $quote->setData(Config::QUOTE_FLAG, $requested ? 1 : 0);

        $address = $this->getMockBuilder(Address::class)->disableOriginalConstructor()->onlyMethods([])->getMock();
        $address->setData('address_type', Address::TYPE_SHIPPING);
        $shipping = $this->createStub(ShippingInterface::class);
        $shipping->method('getAddress')->willReturn($address);
        $assignment = $this->createStub(ShippingAssignmentInterface::class);
        $assignment->method('getShipping')->willReturn($shipping);
        $assignment->method('getItems')->willReturn([new DataObject()]);

        $total = new Total([], new \Magento\Framework\Serialize\Serializer\Json());
        (new QuoteGiftWrap($this->config($enabled), $priceCurrency))->collect($quote, $assignment, $total);

        return $total;
    }

    public function testRequestedWrappingIsAddedToTheCartTotal(): void
    {
        $total = $this->collectQuote(true);

        self::assertSame(2.0, $total->getTotalAmount(Config::TOTAL_CODE));
        self::assertSame(2.0, (float) $total->getData(Config::AMOUNT));
    }

    public function testNothingIsAddedWithoutTheRequestOrWhenTheOptionIsOff(): void
    {
        self::assertSame(0.0, (float) $this->collectQuote(false)->getData(Config::AMOUNT));
        self::assertSame(0.0, (float) $this->collectQuote(true, false)->getData(Config::AMOUNT));
    }

    private function order(array $previous = [], string $collection = 'getInvoiceCollection'): Order
    {
        $order = $this->getMockBuilder(Order::class)->disableOriginalConstructor()
            ->onlyMethods([$collection])->getMock();
        $order->method($collection)->willReturn($previous);
        $order->setData([Config::AMOUNT => 2.0, Config::BASE_AMOUNT => 2.0]);

        return $order;
    }

    public function testTheFirstInvoiceBillsTheWrappingOnce(): void
    {
        $invoice = $this->getMockBuilder(Invoice::class)->disableOriginalConstructor()->onlyMethods(['getOrder'])->getMock();
        $invoice->method('getOrder')->willReturn($this->order());
        $invoice->setGrandTotal(34.0)->setBaseGrandTotal(34.0);
        (new InvoiceGiftWrap())->collect($invoice);
        self::assertSame(36.0, (float) $invoice->getGrandTotal());

        $alreadyBilled = $this->getMockBuilder(Invoice::class)->disableOriginalConstructor()->onlyMethods(['getId'])->getMock();
        $alreadyBilled->method('getId')->willReturn(1);
        $alreadyBilled->setData(['state' => Invoice::STATE_PAID, Config::AMOUNT => 2.0]);
        $second = $this->getMockBuilder(Invoice::class)->disableOriginalConstructor()->onlyMethods(['getOrder'])->getMock();
        $second->method('getOrder')->willReturn($this->order([$alreadyBilled]));
        $second->setGrandTotal(10.0);
        (new InvoiceGiftWrap())->collect($second);
        self::assertSame(10.0, (float) $second->getGrandTotal());
    }

    public function testOnlyTheCreditMemoClosingTheOrderRefundsTheWrapping(): void
    {
        $partial = $this->getMockBuilder(Creditmemo::class)->disableOriginalConstructor()->onlyMethods(['getOrder', 'isLast'])->getMock();
        $partial->method('getOrder')->willReturn($this->order([], 'getCreditmemosCollection'));
        $partial->method('isLast')->willReturn(false);
        $partial->setGrandTotal(12.0);
        (new CreditmemoGiftWrap())->collect($partial);
        self::assertSame(12.0, (float) $partial->getGrandTotal());

        $last = $this->getMockBuilder(Creditmemo::class)->disableOriginalConstructor()->onlyMethods(['getOrder', 'isLast'])->getMock();
        $last->method('getOrder')->willReturn($this->order([], 'getCreditmemosCollection'));
        $last->method('isLast')->willReturn(true);
        $last->setGrandTotal(34.0)->setBaseGrandTotal(34.0);
        (new CreditmemoGiftWrap())->collect($last);
        self::assertSame(36.0, (float) $last->getGrandTotal());
    }
}
