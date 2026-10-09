<?php
declare(strict_types=1);

namespace MadameAiguille\Checkout\Test\Unit\Model\Newsletter;

use MadameAiguille\Checkout\Model\Newsletter\ConfigProvider;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    public function testDisabledNewsletterIsHiddenEvenForLoggedInCustomer(): void
    {
        self::assertFalse($this->enabled(false, true, true));
    }

    public function testGuestBoxRequiresGuestSubscriptionsToBeAllowed(): void
    {
        self::assertFalse($this->enabled(true, false, false));
        self::assertTrue($this->enabled(true, true, false));
    }

    public function testCustomerBoxRemainsAvailableWhenGuestSubscriptionsAreDisabled(): void
    {
        self::assertTrue($this->enabled(true, false, true));
    }

    private function enabled(bool $active, bool $guestAllowed, bool $loggedIn): bool
    {
        $config = $this->createStub(ScopeConfigInterface::class);
        $config->method('isSetFlag')->willReturnCallback(
            static fn (string $path): bool => $path === 'newsletter/general/active' ? $active : $guestAllowed
        );
        $session = $this->createStub(Session::class);
        $session->method('isLoggedIn')->willReturn($loggedIn);

        return (new ConfigProvider($config, $session))->getConfig()['madameaiguilleNewsletterEnabled'];
    }
}
