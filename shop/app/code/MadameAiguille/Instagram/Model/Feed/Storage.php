<?php
/**
 * Mémoire de la galerie (table `flag`, sans table propre) : publications affichées et date de la dernière
 * synchronisation réussie ; date du dernier renouvellement du jeton. Lu par l'accueil et par env:check.
 */

declare(strict_types=1);

namespace MadameAiguille\Instagram\Model\Feed;

use Magento\Framework\FlagManager;

class Storage
{
    public const FEED_FLAG = 'madameaiguille_instagram_feed';
    public const TOKEN_FLAG = 'madameaiguille_instagram_token';

    public function __construct(
        private readonly FlagManager $flagManager
    ) {
    }

    /**
     * @return list<array{id: string, file: string, permalink: string, alt: string, width: int, height: int}>
     */
    public function getItems(): array
    {
        $feed = $this->flagManager->getFlagData(self::FEED_FLAG);

        return is_array($feed) && is_array($feed['items'] ?? null) ? array_values($feed['items']) : [];
    }

    public function getSyncedAt(): ?int
    {
        $feed = $this->flagManager->getFlagData(self::FEED_FLAG);

        return is_array($feed) && isset($feed['synced_at']) ? (int) $feed['synced_at'] : null;
    }

    /**
     * @param list<array{id: string, file: string, permalink: string, alt: string, width: int, height: int}> $items
     */
    public function saveFeed(array $items, int $syncedAt): void
    {
        $this->flagManager->saveFlag(self::FEED_FLAG, ['synced_at' => $syncedAt, 'items' => $items]);
    }

    public function getTokenRefreshedAt(): ?int
    {
        $token = $this->flagManager->getFlagData(self::TOKEN_FLAG);

        return is_array($token) && isset($token['refreshed_at']) ? (int) $token['refreshed_at'] : null;
    }

    public function saveTokenRefresh(int $refreshedAt, int $expiresIn): void
    {
        $this->flagManager->saveFlag(
            self::TOKEN_FLAG,
            ['refreshed_at' => $refreshedAt, 'expires_at' => $expiresIn > 0 ? $refreshedAt + $expiresIn : null]
        );
    }
}
