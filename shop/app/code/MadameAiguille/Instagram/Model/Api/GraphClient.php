<?php
/**
 * API Instagram, côté serveur uniquement : lecture des publications et renouvellement du jeton. Le jeton ne figure
 * dans aucun message d'erreur.
 *
 * Deux sortes de jetons :
 * - « IG… » (connexion Instagram) : `graph.instagram.com/me/media`, valable 60 jours, renouvelable ;
 * - jeton de la **connexion unifiée** (utilisateur système), seule offerte aux applications créées depuis
 *   l'automne 2026 : refusé par graph.instagram.com (« Cannot parse access token »), il passe par
 *   `graph.facebook.com/{version}/{identifiant du compte}/media` et n'expire pas (constaté le 09/11/2026).
 */

declare(strict_types=1);

namespace MadameAiguille\Instagram\Model\Api;

use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;

class GraphClient implements ClientInterface
{
    public const BASE_URL = 'https://graph.instagram.com/';
    public const FACEBOOK_BASE_URL = 'https://graph.facebook.com/v26.0/';
    private const FIELDS = 'id,media_type,media_url,thumbnail_url,permalink,caption,timestamp';
    private const TIMEOUT = 15;

    public function __construct(
        private readonly CurlFactory $curlFactory,
        private readonly Json $json
    ) {
    }

    public function fetchMedia(string $token, int $limit, ?string $accountId = null): array
    {
        $query = ['fields' => self::FIELDS, 'limit' => (string) $limit];
        if ($this->isRenewable($token)) {
            $data = $this->get(self::BASE_URL, 'me/media', $query, $token);
        } elseif ($accountId === null) {
            throw new InstagramException(
                'Jeton de la connexion unifiée : renseigner l\'identifiant du compte Instagram dans la configuration.'
            );
        } else {
            $data = $this->get(self::FACEBOOK_BASE_URL, $accountId . '/media', $query, $token);
        }
        $media = $data['data'] ?? null;
        if (!is_array($media)) {
            throw new InstagramException('Réponse Instagram inattendue : liste de publications absente.');
        }

        return array_values(array_filter($media, 'is_array'));
    }

    public function isRenewable(string $token): bool
    {
        return str_starts_with($token, 'IG');
    }

    public function refreshToken(string $token): array
    {
        if (!$this->isRenewable($token)) {
            throw new InstagramException('Jeton de la connexion unifiée : il n\'expire pas et ne se renouvelle pas.');
        }
        $data = $this->get(self::BASE_URL, 'refresh_access_token', ['grant_type' => 'ig_refresh_token'], $token);
        if (!is_string($data['access_token'] ?? null) || $data['access_token'] === '') {
            throw new InstagramException('Réponse Instagram inattendue : jeton renouvelé absent.');
        }

        return ['access_token' => $data['access_token'], 'expires_in' => (int) ($data['expires_in'] ?? 0)];
    }

    /**
     * @param string $baseUrl
     * @param string $path
     * @param array<string, string> $query
     * @return array<string, mixed>
     */
    private function get(string $baseUrl, string $path, array $query, string $token): array
    {
        $curl = $this->curlFactory->create();
        $curl->setTimeout(self::TIMEOUT);
        try {
            $curl->get($baseUrl . $path . '?' . http_build_query($query + ['access_token' => $token]));
        } catch (\Exception) {
            throw new InstagramException(sprintf('Instagram injoignable (%s).', $path));
        }

        try {
            $data = $this->json->unserialize((string) $curl->getBody());
        } catch (\InvalidArgumentException) {
            $data = null;
        }
        if ($curl->getStatus() !== 200 || !is_array($data)) {
            $error = is_array($data) ? ($data['error']['message'] ?? '') : '';
            throw new InstagramException(sprintf(
                'Instagram a refusé l\'appel %s (HTTP %d)%s',
                $path,
                $curl->getStatus(),
                $error !== '' ? ' : ' . str_replace($token, '…', (string) $error) : '.'
            ));
        }

        return $data;
    }
}
