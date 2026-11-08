<?php
declare(strict_types=1);

namespace MadameAiguille\Checkout\Test\Unit\Plugin\Sales;

use MadameAiguille\Checkout\Model\GiftWrap\OrderGiftCard;
use MadameAiguille\Checkout\Plugin\Sales\GiftCardOnPdf;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Stdlib\StringUtils;
use PHPUnit\Framework\TestCase;

class GiftCardOnPdfTest extends TestCase
{
    public function testPrintsTitleNamesAndWrappedMessage(): void
    {
        $plugin = new GiftCardOnPdf(
            $this->createStub(OrderGiftCard::class),
            $this->createStub(DirectoryList::class),
            new StringUtils()
        );

        $lines = $plugin->lines([
            'title' => 'Emballage et message cadeau',
            'sender' => 'Pauline',
            'recipient' => 'Marie',
            'message' => str_repeat('mot ', 40) . "\nBises",
        ]);

        self::assertSame(['Emballage et message cadeau', true], $lines[0]);
        self::assertSame(['Gift from Pauline', false], $lines[1]);
        self::assertSame(['Gift for Marie', false], $lines[2]);
        self::assertSame(['Gift message', false], $lines[3]);
        self::assertCount(7, $lines, 'Message de 160 caractères coupé en deux lignes, puis « Bises ».');
        self::assertSame('    Bises', end($lines)[0]);
    }
}
