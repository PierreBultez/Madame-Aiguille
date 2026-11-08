<?php
/**
 * Lot 11 : l'emballage cadeau et le message cadeau ne font plus qu'une option, « Emballage et message cadeau ».
 * Les nouvelles valeurs par défaut sont dans etc/config.xml ; ce patch ne remplace en base qu'un libellé ou un texte
 * restés à leur valeur d'origine (section enregistrée telle quelle dans l'administration). Un texte changé par
 * l'atelier est conservé.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class RenameGiftWrapOption implements DataPatchInterface
{
    /** @var array<string, array{0: string, 1: string}> chemin => [ancienne valeur, nouvelle valeur] */
    public const VALUES = [
        'madameaiguille/gift_wrap/label' => ['Emballage cadeau', 'Emballage et message cadeau'],
        'madameaiguille/gift_wrap/description' => [
            "Votre commande est emballée avec soin pour être offerte. Ajoutez un message cadeau : je l'écris à la main sur une petite carte.",
            "J'emballe votre commande et j'écris votre message à la main sur une carte.",
        ],
    ];

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    public function apply(): self
    {
        $connection = $this->moduleDataSetup->getConnection();
        $table = $this->moduleDataSetup->getTable('core_config_data');

        foreach (self::VALUES as $path => [$old, $new]) {
            $connection->update($table, ['value' => $new], ['path = ?' => $path, 'value = ?' => $old]);
        }

        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
