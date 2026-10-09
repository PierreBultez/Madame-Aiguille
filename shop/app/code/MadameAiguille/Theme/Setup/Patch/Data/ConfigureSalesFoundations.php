<?php
/**
 * Pose le socle de vente arrêté au call Céline du 11/09/2026 et tranché par Pierre le 09/10/2026 :
 * zone France / Belgique / Luxembourg (Monaco suit la France), expédition depuis Saint-Épain,
 * franchise en base de TVA, carte bancaire Mollie seule, message cadeau natif.
 *
 * Ne touche ni aux clés ni à l'activation Mollie (payment/mollie_general/*), saisies dans l'administration.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class ConfigureSalesFoundations implements DataPatchInterface
{
    /** Mention de la franchise en base, ajoutée au copyright tant que Céline ne l'a pas réécrit. */
    public const VAT_MENTION = 'TVA non applicable, art. 293 B du CGI.';

    private const COPYRIGHT_PATH = 'design/footer/copyright';
    private const COPYRIGHT_LOT1 = '© 2026 Madame Aiguille — Cousu main en France.';
    private const MOLLIE_CARD_METHOD = 'mollie_methods_creditcard';

    private const VALUES = [
        'general/country/allow' => 'FR,BE,LU,MC',
        'shipping/origin/country_id' => 'FR',
        'shipping/origin/region_id' => '219',
        'shipping/origin/postcode' => '37800',
        'shipping/origin/city' => 'Saint-Épain',
        'shipping/origin/street_line1' => '35 Grande Rue',
        'tax/defaults/country' => 'FR',
        'tax/defaults/region' => '219',
        'tax/defaults/postcode' => '37800',
        'payment/checkmo/active' => '0',
        'payment/' . self::MOLLIE_CARD_METHOD . '/active' => '1',
        'payment/' . self::MOLLIE_CARD_METHOD . '/title' => 'Carte bancaire',
        'sales/gift_options/allow_order' => '1',
        'sales/gift_options/allow_items' => '0',
    ];

    public function __construct(
        private readonly WriterInterface $configWriter,
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function apply(): self
    {
        foreach (self::VALUES as $path => $value) {
            $this->configWriter->save($path, $value);
        }

        foreach (array_keys((array) $this->scopeConfig->getValue('payment')) as $method) {
            if (str_starts_with((string) $method, 'mollie_methods_') && $method !== self::MOLLIE_CARD_METHOD) {
                $this->configWriter->save('payment/' . $method . '/active', '0');
            }
        }

        if ($this->scopeConfig->getValue(self::COPYRIGHT_PATH) === self::COPYRIGHT_LOT1) {
            $this->configWriter->save(self::COPYRIGHT_PATH, self::COPYRIGHT_LOT1 . ' ' . self::VAT_MENTION);
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
