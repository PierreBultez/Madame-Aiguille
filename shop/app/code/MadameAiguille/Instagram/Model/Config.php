<?php
/**
 * Réglages de la galerie Instagram (Configuration › Général › Galerie Instagram). Le jeton est chiffré en base ;
 * la configuration le rend déchiffré. Son renouvellement le réécrit chiffré, sans jamais le journaliser.
 */

declare(strict_types=1);

namespace MadameAiguille\Instagram\Model;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class Config
{
    public const XML_PATH_ENABLED = 'madameaiguille_instagram/feed/enabled';
    public const XML_PATH_TOKEN = 'madameaiguille_instagram/feed/access_token';
    public const XML_PATH_COUNT = 'madameaiguille_instagram/feed/count';
    public const XML_PATH_ACCOUNT_ID = 'madameaiguille_instagram/feed/account_id';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly WriterInterface $writer,
        private readonly EncryptorInterface $encryptor,
        private readonly ReinitableConfigInterface $reinitableConfig,
        private readonly TypeListInterface $cacheTypeList
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED);
    }

    public function getAccessToken(): ?string
    {
        $token = trim((string) $this->scopeConfig->getValue(self::XML_PATH_TOKEN));

        return $token === '' ? null : $token;
    }

    public function getAccountId(): ?string
    {
        $id = preg_replace('/\D/', '', (string) $this->scopeConfig->getValue(self::XML_PATH_ACCOUNT_ID));

        return $id === '' ? null : $id;
    }

    public function getCount(): int
    {
        return min(8, max(1, (int) $this->scopeConfig->getValue(self::XML_PATH_COUNT) ?: 4));
    }

    /**
     * Remplace le jeton par sa version renouvelée, chiffrée comme à la saisie dans l'administration.
     */
    public function saveAccessToken(string $token): void
    {
        $this->writer->save(self::XML_PATH_TOKEN, $this->encryptor->encrypt($token));
        $this->cacheTypeList->cleanType('config');
        $this->reinitableConfig->reinit();
    }
}
