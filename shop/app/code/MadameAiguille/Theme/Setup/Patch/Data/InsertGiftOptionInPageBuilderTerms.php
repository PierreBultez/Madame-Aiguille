<?php
/**
 * CGV : insère le paragraphe « Emballage et message cadeau » dans une page enregistrée depuis Page Builder.
 *
 * En production, la CGV a été réenregistrée depuis Page Builder le 07/11/2026 : tout l'ancien texte est rangé dans un
 * bloc « Code HTML », balises encodées. DescribeGiftOptionInTerms et RestoreGiftOptionInTerms n'y trouvaient pas
 * l'ancre et n'ont rien écrit. withClause reconnaît désormais cette forme ; ce patch rejoue l'insertion.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

class InsertGiftOptionInPageBuilderTerms extends DescribeGiftOptionInTerms
{
    /**
     * @return array<int, class-string>
     */
    public static function getDependencies(): array
    {
        return [RestoreGiftOptionInTerms::class];
    }
}
