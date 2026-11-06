<?php
/**
 * Réglages de l'assistant IA (Stores › Configuration › Général › Assistant IA).
 *
 * L'assistant n'est « prêt » qu'activé avec une clé : sans cela, le bouton n'est pas rendu et les contrôleurs
 * refusent toute demande. Le modèle n'est pas un réglage (Claude\SdkClient::MODEL). Le contrôle d'environnement
 * (env:check) signale un assistant activé sans clé.
 */

declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class Config
{
    public const XML_PATH_ENABLED = 'madameaiguille_ai/assistant/enabled';
    public const XML_PATH_API_KEY = 'madameaiguille_ai/assistant/api_key';
    public const XML_PATH_DAILY_LIMIT = 'madameaiguille_ai/assistant/daily_limit';

    /** Plafond retenu par Pierre le 06/11/2026, appliqué si le réglage est vide ou illisible. */
    public const DEFAULT_DAILY_LIMIT = 50;

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly EncryptorInterface $encryptor
    ) {
    }

    public function isReady(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED) && $this->getApiKey() !== null;
    }

    /** Clé déchiffrée ; null si absente. Jamais journalisée ni renvoyée au navigateur. */
    public function getApiKey(): ?string
    {
        $stored = trim((string) $this->scopeConfig->getValue(self::XML_PATH_API_KEY));
        if ($stored === '') {
            return null;
        }
        $key = trim($this->encryptor->decrypt($stored));

        return $key === '' ? null : $key;
    }

    public function getDailyLimit(): int
    {
        $limit = trim((string) $this->scopeConfig->getValue(self::XML_PATH_DAILY_LIMIT));

        return ctype_digit($limit) && (int) $limit > 0 ? (int) $limit : self::DEFAULT_DAILY_LIMIT;
    }
}
