<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Test\Unit\Setup\Patch\Data;

use MadameAiguille\Theme\Setup\Patch\Data\DescribeGiftOptionInTerms;
use MadameAiguille\Theme\Setup\Patch\Data\InsertGiftOptionInPageBuilderTerms;
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

    public function testInsertsTheEncodedClauseInAPageBuilderHtmlCodeBlock(): void
    {
        $content = '<div data-content-type="html" data-appearance="default" data-element="main">'
            . "&lt;h2&gt;4. Commande&lt;/h2&gt;\r\n" . DescribeGiftOptionInTerms::encoded(DescribeGiftOptionInTerms::ANCHOR)
            . "\r\n&lt;h2&gt;5. Paiement&lt;/h2&gt;</div>";

        $updated = DescribeGiftOptionInTerms::withClause($content);

        self::assertStringContainsString(
            DescribeGiftOptionInTerms::encoded(DescribeGiftOptionInTerms::ANCHOR) . "\n"
            . '&lt;p&gt;&lt;strong&gt;Emballage et message cadeau.&lt;/strong&gt;',
            $updated
        );
        self::assertStringNotContainsString('<p>', $updated, 'Aucune balise brute dans le bloc Code HTML.');
        self::assertSame($updated, DescribeGiftOptionInTerms::withClause($updated), 'Rejouer ne double rien.');
    }

    public function testThePageBuilderInsertionRunsLast(): void
    {
        self::assertSame([RestoreGiftOptionInTerms::class], InsertGiftOptionInPageBuilderTerms::getDependencies());
    }
}
