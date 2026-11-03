<?php
/**
 * Réglages de la mesure d'audience (Stores › Configuration › Mesure d'audience).
 *
 * La mesure n'est « prête » que si elle est activée avec une adresse https et un identifiant de site : sans cela,
 * rien n'est rendu et la CSP reste inchangée. Le contrôle d'environnement (env:check) signale les réglages incomplets.
 */

declare(strict_types=1);

namespace MadameAiguille\Analytics\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    public const XML_PATH_ENABLED = 'madameaiguille_analytics/matomo/enabled';
    public const XML_PATH_URL = 'madameaiguille_analytics/matomo/url';
    public const XML_PATH_SITE_ID = 'madameaiguille_analytics/matomo/site_id';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isReady(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE)
            && $this->getUrl() !== null
            && $this->getSiteId() !== null;
    }

    /** Adresse https de Matomo, terminée par « / » ; null si vide ou invalide. */
    public function getUrl(): ?string
    {
        $url = (string) $this->scopeConfig->getValue(self::XML_PATH_URL, ScopeInterface::SCOPE_STORE);

        return self::normalizeUrl($url);
    }

    /** « https://stats.madame-aiguille.fr » pour la CSP ; null si l'adresse est invalide. */
    public function getOrigin(): ?string
    {
        $url = $this->getUrl();

        return $url === null ? null : (string) preg_replace('#^(https://[^/]+)/.*$#', '$1', $url);
    }

    public function getSiteId(): ?int
    {
        $siteId = trim((string) $this->scopeConfig->getValue(self::XML_PATH_SITE_ID, ScopeInterface::SCOPE_STORE));

        return ctype_digit($siteId) && (int) $siteId > 0 ? (int) $siteId : null;
    }

    public static function normalizeUrl(string $url): ?string
    {
        $url = trim($url);
        if (!preg_match('#^https://[a-z0-9.-]+(:\d+)?(/[^\s?\#]*)?$#i', $url)) {
            return null;
        }

        return rtrim($url, '/') . '/';
    }
}
