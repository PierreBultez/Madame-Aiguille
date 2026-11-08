<?php
declare(strict_types=1);

namespace MadameAiguille\Checkout\Test\Unit\Plugin\Customer;

use MadameAiguille\Checkout\Plugin\Customer\TolerateMissingCaptchaFormId;
use Magento\Captcha\Helper\Data;
use PHPUnit\Framework\TestCase;

class TolerateMissingCaptchaFormIdTest extends TestCase
{
    public function testMissingFormIdBecomesEmpty(): void
    {
        $helper = $this->createStub(Data::class);

        self::assertSame([''], (new TolerateMissingCaptchaFormId())->beforeGetCaptcha($helper, null));
    }

    public function testExistingFormIdIsKept(): void
    {
        $helper = $this->createStub(Data::class);

        self::assertSame(['user_login'], (new TolerateMissingCaptchaFormId())->beforeGetCaptcha($helper, 'user_login'));
    }
}
