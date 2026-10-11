<?php
/**
 * CAPTCHA image natif de la vitrine coupé (11/10/2026) : Magento 2.4.9 l'active par défaut sur « mot de passe
 * oublié » et, après un échec, sur la connexion client. La boutique est protégée par reCAPTCHA v2 invisible ; les
 * deux ensemble imposeraient en plus une image à recopier. Coupé à la main sur le poste et sur le serveur, ce
 * patch le reproduit sur une installation neuve.
 *
 * N'écrit la valeur que si elle est absente de la base : un réglage fait dans l'administration n'est pas réécrit.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class DisableNativeStorefrontCaptcha implements DataPatchInterface
{
    private const PATH = 'customer/captcha/enable';

    public function __construct(
        private readonly WriterInterface $configWriter,
        private readonly ResourceConnection $resource
    ) {
    }

    public function apply(): self
    {
        $connection = $this->resource->getConnection();
        $exists = $connection->fetchOne(
            $connection->select()
                ->from($this->resource->getTableName('core_config_data'), ['config_id'])
                ->where('path = ?', self::PATH)
                ->where('scope = ?', 'default')
                ->where('scope_id = ?', 0)
        );
        if (!$exists) {
            $this->configWriter->save(self::PATH, '0');
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
