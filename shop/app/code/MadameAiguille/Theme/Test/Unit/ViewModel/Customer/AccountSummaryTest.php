<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Test\Unit\ViewModel\Customer;

use MadameAiguille\Theme\ViewModel\Customer\AccountSummary;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class AccountSummaryTest extends TestCase
{
    public function testItExposesTheCurrentCustomerIdentity(): void
    {
        $customer = $this->createStub(CustomerInterface::class);
        $customer->method('getFirstname')->willReturn('Céline');
        $customer->method('getLastname')->willReturn('Martin');
        $customer->method('getEmail')->willReturn('celine@example.fr');

        $currentCustomer = $this->createStub(CurrentCustomer::class);
        $currentCustomer->method('getCustomer')->willReturn($customer);

        $summary = new AccountSummary($currentCustomer);

        self::assertSame('Céline Martin', $summary->getName());
        self::assertSame('celine@example.fr', $summary->getEmail());
    }
}
