<?php
/**
 * Identité de la boutique pour une installation neuve (lot 8a) : jusqu'ici saisie dans l'administration du poste de
 * développement, elle n'était portée par aucun patch. Langue, fuseau, devise, informations de la boutique,
 * expéditeurs, thème Hyvä de la vue, logo, copyright avec la mention de TVA, réseaux sociaux, URLs propres,
 * télémétrie Adobe coupée.
 *
 * N'écrit qu'une valeur **absente de la base** : rien de ce que Céline ou Pierre ont déjà saisi n'est réécrit.
 * Le téléphone de la boutique n'est pas versionné (dépôt public) : il se saisit dans l'administration.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Model\ResourceModel\Theme\CollectionFactory as ThemeCollectionFactory;

class ConfigureStoreIdentity implements DataPatchInterface
{
    public const THEME = 'frontend/MadameAiguille/default';
    public const SENDER_EMAIL = 'contact@madame-aiguille.fr';
    public const SENDER_NAME = 'Madame Aiguille';

    private const VALUES = [
        'general/locale/code' => 'fr_FR',
        'general/locale/timezone' => 'Europe/Paris',
        'general/locale/firstday' => '1',
        'general/country/default' => 'FR',
        'currency/options/base' => 'EUR',
        'currency/options/default' => 'EUR',
        'currency/options/allow' => 'EUR',
        'general/store_information/name' => self::SENDER_NAME,
        'general/store_information/street_line1' => '35 Grande Rue',
        'general/store_information/postcode' => '37800',
        'general/store_information/city' => 'Saint-Épain',
        'general/store_information/region_id' => '219',
        'general/store_information/country_id' => 'FR',
        'contact/email/recipient_email' => self::SENDER_EMAIL,
        'contact/email/sender_email_identity' => 'general',
        'design/header/logo_alt' => 'Madame Aiguille — L’élégance cousue main',
        'madameaiguille/social/instagram_url' => 'https://www.instagram.com/madame.aiguille',
        'madameaiguille/social/facebook_url' => 'https://www.facebook.com/profile.php?id=61576468922630',
        'web/seo/use_rewrites' => '1',
        'admin/usage/enabled' => '0',
        'analytics/subscription/enabled' => '0',
    ];

    private const SENDER_IDENTITIES = ['general', 'sales', 'support', 'custom1', 'custom2'];

    public function __construct(
        private readonly WriterInterface $configWriter,
        private readonly ResourceConnection $resource,
        private readonly StoreManagerInterface $storeManager,
        private readonly ThemeCollectionFactory $themeCollectionFactory
    ) {
    }

    public function apply(): self
    {
        $values = self::VALUES + [
            'design/footer/copyright' => '© 2026 Madame Aiguille — Cousu main en France. '
                . ConfigureSalesFoundations::VAT_MENTION,
        ];
        foreach (self::SENDER_IDENTITIES as $identity) {
            $values['trans_email/ident_' . $identity . '/email'] = self::SENDER_EMAIL;
            $values['trans_email/ident_' . $identity . '/name'] = self::SENDER_NAME;
        }
        foreach ($values as $path => $value) {
            $this->saveIfAbsent($path, $value);
        }

        $theme = $this->themeCollectionFactory->create()->getThemeByFullPath(self::THEME);
        if ($theme->getId()) {
            $storeId = (int) $this->storeManager->getDefaultStoreView()?->getId();
            $this->saveIfAbsent(
                'design/theme/theme_id',
                (string) $theme->getId(),
                ScopeInterface::SCOPE_STORES,
                $storeId
            );
        }

        return $this;
    }

    private function saveIfAbsent(string $path, string $value, string $scope = 'default', int $scopeId = 0): void
    {
        $connection = $this->resource->getConnection();
        $exists = $connection->fetchOne(
            $connection->select()
                ->from($this->resource->getTableName('core_config_data'), ['config_id'])
                ->where('path = ?', $path)
                ->where('scope = ?', $scope)
                ->where('scope_id = ?', $scopeId)
        );
        if (!$exists) {
            $this->configWriter->save($path, $value, $scope, $scopeId);
        }
    }

    public static function getDependencies(): array
    {
        return [
            ConfigureSalesFoundations::class,
            \Magento\Theme\Setup\Patch\Data\RegisterThemes::class,
        ];
    }

    public function getAliases(): array
    {
        return [];
    }
}
