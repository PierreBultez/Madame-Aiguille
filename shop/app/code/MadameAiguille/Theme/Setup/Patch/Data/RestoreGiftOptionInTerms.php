<?php
/**
 * CGV : réinsère le paragraphe « Emballage et message cadeau » de DescribeGiftOptionInTerms.
 *
 * Sans effet en production : la page y est enregistrée depuis Page Builder, voir InsertGiftOptionInPageBuilderTerms.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

class RestoreGiftOptionInTerms extends DescribeGiftOptionInTerms
{
    /**
     * @return array<int, class-string>
     */
    public static function getDependencies(): array
    {
        return [DescribeGiftOptionInTerms::class];
    }
}
