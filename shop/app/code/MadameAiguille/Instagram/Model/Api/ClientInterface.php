<?php
declare(strict_types=1);

namespace MadameAiguille\Instagram\Model\Api;

interface ClientInterface
{
    /**
     * Dernières publications du compte : id, media_type, media_url, thumbnail_url, permalink, caption, timestamp.
     *
     * @return list<array<string, string>>
     * @throws InstagramException
     */
    public function fetchMedia(string $token, int $limit, ?string $accountId = null): array;

    /**
     * Le jeton se renouvelle-t-il ? Un jeton « IG… » vaut 60 jours ; celui de la connexion unifiée (utilisateur
     * système) n'expire pas.
     */
    public function isRenewable(string $token): bool;

    /**
     * Jeton renouvelé pour 60 jours.
     *
     * @return array{access_token: string, expires_in: int}
     * @throws InstagramException
     */
    public function refreshToken(string $token): array;
}
