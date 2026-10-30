<?php
/**
 * Le retrait à l'atelier se règle en ligne ou sur place (décision de Pierre du 30/10/2026) : côte à côte,
 * « Carte bancaire » et « Paiement sur place » se confondraient, le TPE prenant aussi la carte.
 *
 * Ne remplace qu'un titre resté à sa valeur d'origine : un libellé changé dans l'administration est conservé.
 */

declare(strict_types=1);

namespace MadameAiguille\Checkout\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class RelabelPickupPaymentChoices implements DataPatchInterface
{
    /** @var array<string, array{0: string, 1: string}> chemin => [ancien titre, nouveau titre] */
    public const TITLES = [
        'payment/stripe_payments/title' => ['Carte bancaire', 'Carte bancaire en ligne'],
        'payment/cashondelivery/title' => ['Paiement sur place', 'Paiement sur place (carte ou espèces)'],
    ];

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    public function apply(): self
    {
        $connection = $this->moduleDataSetup->getConnection();
        $table = $this->moduleDataSetup->getTable('core_config_data');

        foreach (self::TITLES as $path => [$old, $new]) {
            $connection->update($table, ['value' => $new], ['path = ?' => $path, 'value = ?' => $old]);
        }

        return $this;
    }

    public static function getDependencies(): array
    {
        return [EnablePayOnSite::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
