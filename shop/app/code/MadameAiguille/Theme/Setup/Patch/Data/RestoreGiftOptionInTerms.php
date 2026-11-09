<?php
/**
 * CGV : réinsère le paragraphe « Emballage et message cadeau » de DescribeGiftOptionInTerms.
 *
 * En production, ce patch a bien été appliqué au déploiement du 08/11/2026, mais le 09/11 le paragraphe n'apparaissait
 * plus sur /cgv alors que la phrase d'ancrage y était intacte. Cause la plus probable : la page réenregistrée dans
 * l'administration depuis un formulaire ouvert avant le déploiement. Même règle : une seule insertion, rien si la phrase d'origine a été retouchée.
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
