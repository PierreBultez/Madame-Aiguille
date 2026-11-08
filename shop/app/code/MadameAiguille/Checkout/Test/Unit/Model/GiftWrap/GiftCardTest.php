<?php
declare(strict_types=1);

namespace MadameAiguille\Checkout\Test\Unit\Model\GiftWrap;

use MadameAiguille\Checkout\Model\GiftWrap\GiftCard;
use Magento\Framework\Exception\InputException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class GiftCardTest extends TestCase
{
    public function testCleansTheCardAndKeepsLineBreaks(): void
    {
        $card = GiftCard::fromInput('  Pauline ', "Marie\t ", "Joyeux anniversaire !  \r\n\r\n  Je t'embrasse ", 250);

        self::assertSame('Pauline', $card->sender);
        self::assertSame('Marie', $card->recipient);
        self::assertSame("Joyeux anniversaire !\n\nJe t'embrasse", $card->message);
    }

    #[DataProvider('incompleteCards')]
    public function testRefusesAnIncompleteCard(?string $sender, ?string $recipient, ?string $message): void
    {
        $this->expectException(InputException::class);

        GiftCard::fromInput($sender, $recipient, $message, 250);
    }

    public static function incompleteCards(): array
    {
        return [
            'sans expéditeur' => [null, 'Marie', 'Bonne fête'],
            'sans destinataire' => ['Pauline', '  ', 'Bonne fête'],
            'sans message' => ['Pauline', 'Marie', " \n "],
        ];
    }

    public function testCountsCharactersNotBytes(): void
    {
        self::assertSame(str_repeat('é', 250), GiftCard::fromInput('A', 'B', str_repeat('é', 250), 250)->message);

        $this->expectException(InputException::class);
        GiftCard::fromInput('A', 'B', str_repeat('é', 251), 250);
    }

    public function testTellsWhetherAStoredCardIsComplete(): void
    {
        self::assertTrue(GiftCard::isComplete('Pauline', 'Marie', 'Bonne fête'));
        self::assertFalse(GiftCard::isComplete('Pauline', null, 'Bonne fête'));
        self::assertFalse(GiftCard::isComplete('', '', ''));
    }
}
