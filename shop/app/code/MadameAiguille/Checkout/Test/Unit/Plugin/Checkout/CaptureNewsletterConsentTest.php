<?php
declare(strict_types=1);

namespace MadameAiguille\Checkout\Test\Unit\Plugin\Checkout;

use MadameAiguille\Checkout\Plugin\Checkout\CaptureNewsletterConsent;
use Magento\Quote\Model\Quote\Payment;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CaptureNewsletterConsentTest extends TestCase
{
    #[DataProvider('consentValues')]
    public function testOnlyExplicitOptInIsStored(mixed $input, bool $expected): void
    {
        $payment = $this->createMock(Payment::class);
        $payment->expects(self::once())->method('setAdditionalInformation')
            ->with(CaptureNewsletterConsent::CONSENT_KEY, $expected)->willReturnSelf();

        self::assertSame($payment, (new CaptureNewsletterConsent())->afterImportData(
            $payment,
            $payment,
            ['additional_data' => [CaptureNewsletterConsent::CONSENT_KEY => $input]]
        ));
    }

    public static function consentValues(): array
    {
        return [
            'checked' => [true, true],
            'api integer' => [1, true],
            'api string' => ['1', true],
            'unchecked' => [false, false],
            'missing' => [null, false],
            'false string must not subscribe' => ['false', false],
            'arbitrary truthy value must not subscribe' => ['yes', false],
            'malformed input' => [[], false],
        ];
    }

    public function testOmittedConsentClearsAnEarlierChoice(): void
    {
        $payment = $this->createMock(Payment::class);
        $payment->expects(self::once())->method('setAdditionalInformation')
            ->with(CaptureNewsletterConsent::CONSENT_KEY, false)->willReturnSelf();

        (new CaptureNewsletterConsent())->afterImportData($payment, $payment, []);
    }
}
