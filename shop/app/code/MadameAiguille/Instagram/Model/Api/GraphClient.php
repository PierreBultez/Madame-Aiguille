<?php
/**
 * API Instagram (connexion Instagram / unifiée, jeton « IG… » d'un compte professionnel) : lecture des publications
 * et renouvellement du jeton, côté serveur uniquement. Appels sans version : l'API répond dans la version de
 * l'application. Le jeton ne figure dans aucun message d'erreur.
 */

declare(strict_types=1);

namespace MadameAiguille\Instagram\Model\Api;

use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;

class GraphClient implements ClientInterface
{
    public const BASE_URL = 'https://graph.instagram.com/';
    private const FIELDS = 'id,media_type,media_url,thumbnail_url,permalink,caption,timestamp';
    private const TIMEOUT = 15;

    public function __construct(
        private readonly CurlFactory $curlFactory,
        private readonly Json $json
    ) {
    }

    public function fetchMedia(string $token, int $limit): array
    {
        $data = $this->get('me/media', ['fields' => self::FIELDS, 'limit' => (string) $limit], $token);
        $media = $data['data'] ?? null;
        if (!is_array($media)) {
            throw new InstagramException('Réponse Instagram inattendue : liste de publications absente.');
        }

        return array_values(array_filter($media, 'is_array'));
    }

    public function refreshToken(string $token): array
    {
        $data = $this->get('refresh_access_token', ['grant_type' => 'ig_refresh_token'], $token);
        if (!is_string($data['access_token'] ?? null) || $data['access_token'] === '') {
            throw new InstagramException('Réponse Instagram inattendue : jeton renouvelé absent.');
        }

        return ['access_token' => $data['access_token'], 'expires_in' => (int) ($data['expires_in'] ?? 0)];
    }

    /**
     * @param array<string, string> $query
     * @return array<string, mixed>
     */
    private function get(string $path, array $query, string $token): array
    {
        $curl = $this->curlFactory->create();
        $curl->setTimeout(self::TIMEOUT);
        try {
            $curl->get(self::BASE_URL . $path . '?' . http_build_query($query + ['access_token' => $token]));
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
