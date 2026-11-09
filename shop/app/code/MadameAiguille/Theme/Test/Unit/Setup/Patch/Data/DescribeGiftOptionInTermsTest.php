<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Test\Unit\Setup\Patch\Data;

use MadameAiguille\Theme\Setup\Patch\Data\DescribeGiftOptionInTerms;
use MadameAiguille\Theme\Setup\Patch\Data\RestoreGiftOptionInTerms;
use PHPUnit\Framework\TestCase;

class DescribeGiftOptionInTermsTest extends TestCase
{
    public function testInsertsTheClauseAfterTheOriginalSentenceOnce(): void
    {
        $content = "<h2>4. Commande</h2>\n" . DescribeGiftOptionInTerms::ANCHOR . "\n<h2>5. Paiement</h2>";

        $updated = DescribeGiftOptionInTerms::withClause($content);

        self::assertSame(
            "<h2>4. Commande</h2>\n" . DescribeGiftOptionInTerms::ANCHOR . "\n" . DescribeGiftOptionInTerms::CLAUSE
            . "\n<h2>5. Paiement</h2>",
            $updated
        );
        self::assertSame($updated, DescribeGiftOptionInTerms::withClause($updated), 'Rejouer ne double rien.');
    }

    public function testLeavesARetouchedPageUntouched(): void
    {
        $content = '<h2>4. Commande</h2><p>Phrase réécrite par l’atelier.</p>';

        self::assertSame($content, DescribeGiftOptionInTerms::withClause($content));
    }

    public function testTheRestoreRunsAfterTheFirstInsertionWithTheSameRule(): void
    {
        self::assertSame([DescribeGiftOptionInTerms::class], RestoreGiftOptionInTerms::getDependencies());
        self::assertTrue(is_subclass_of(RestoreGiftOptionInTerms::class, DescribeGiftOptionInTerms::class));
    }
}
