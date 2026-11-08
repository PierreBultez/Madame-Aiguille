<?php
declare(strict_types=1);

namespace MadameAiguille\Checkout\Test\Unit\Plugin\Checkout;

use MadameAiguille\Checkout\Model\GiftWrap\Config;
use MadameAiguille\Checkout\Plugin\Checkout\AssignGiftWrap;
use Magento\Checkout\Api\Data\ShippingInformationExtensionInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\GiftMessage\Api\Data\MessageInterfaceFactory;
use Magento\GiftMessage\Model\GiftMessageManager;
use Magento\GiftMessage\Model\Message;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class AssignGiftWrapTest extends TestCase
{
    private Quote $quote;
    private GiftMessageManager&MockObject $manager;

    protected function setUp(): void
    {
        $this->quote = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()
            ->onlyMethods(['getStoreId'])->getMock();
        $this->quote->method('getStoreId')->willReturn(1);
        $this->manager = $this->createMock(GiftMessageManager::class);
    }

    public function testSavesTheCardAsNativeGiftMessage(): void
    {
        $this->manager->expects(self::once())->method('setMessage')->with(
            $this->quote,
            'quote',
            self::callback(static fn (Message $message): bool => $message->getSender() === 'Pauline'
                && $message->getRecipient() === 'Marie'
                && $message->getMessage() === 'Bonne fête')
        );

        $this->plugin()->beforeSaveAddressInformation(
            $this->createStub(ShippingInformationManagementInterface::class),
            5,
            $this->information(true, ' Pauline ', 'Marie', 'Bonne fête')
        );

        self::assertSame(1, $this->quote->getData(Config::QUOTE_FLAG));
    }

    public function testRefusesWrappingWithoutCompleteCard(): void
    {
        $this->manager->expects(self::never())->method('setMessage');

        $this->expectException(InputException::class);
        $this->plugin()->beforeSaveAddressInformation(
            $this->createStub(ShippingInformationManagementInterface::class),
            5,
            $this->information(true, 'Pauline', '', 'Bonne fête')
        );
    }

    public function testUncheckedOptionRemovesAnOlderFreeMessage(): void
    {
        $this->quote->setData('gift_message_id', 7);
        $this->manager->expects(self::once())->method('setMessage')->with(
            $this->quote,
            'quote',
            self::callback(static fn (Message $message): bool => $message->getMessage() === null)
        );

        $this->plugin()->beforeSaveAddressInformation(
            $this->createStub(ShippingInformationManagementInterface::class),
            5,
            $this->information(false, 'Pauline', 'Marie', 'Bonne fête')
        );

        self::assertSame(0, $this->quote->getData(Config::QUOTE_FLAG));
    }

    public function testDisabledOptionIsNeverApplied(): void
    {
        $this->manager->expects(self::never())->method('setMessage');

        $this->plugin(false)->beforeSaveAddressInformation(
            $this->createStub(ShippingInformationManagementInterface::class),
            5,
            $this->information(true, 'Pauline', 'Marie', 'Bonne fête')
        );

        self::assertSame(0, $this->quote->getData(Config::QUOTE_FLAG));
    }

    private function plugin(bool $enabled = true): AssignGiftWrap
    {
        $repository = $this->createStub(CartRepositoryInterface::class);
        $repository->method('getActive')->willReturn($this->quote);
        $scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $scopeConfig->method('isSetFlag')->willReturn($enabled);
        $scopeConfig->method('getValue')->willReturn('250');
        $factory = $this->createStub(MessageInterfaceFactory::class);
        $factory->method('create')->willReturnCallback(
            fn () => $this->getMockBuilder(Message::class)->disableOriginalConstructor()->onlyMethods([])->getMock()
        );

        return new AssignGiftWrap($repository, new Config($scopeConfig), $this->manager, $factory);
    }

    private function information(bool $requested, string $sender, string $recipient, string $message): ShippingInformationInterface
    {
        $attributes = $this->createStub(ShippingInformationExtensionInterface::class);
        $attributes->method('getMadameaiguilleGiftWrap')->willReturn($requested);
        $attributes->method('getMadameaiguilleGiftSender')->willReturn($sender);
        $attributes->method('getMadameaiguilleGiftRecipient')->willReturn($recipient);
        $attributes->method('getMadameaiguilleGiftMessage')->willReturn($message);
        $information = $this->createStub(ShippingInformationInterface::class);
        $information->method('getExtensionAttributes')->willReturn($attributes);

        return $information;
    }
}
