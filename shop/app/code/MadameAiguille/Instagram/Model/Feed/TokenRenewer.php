<?php
/**
 * Renouvellement du jeton Instagram (valable 60 jours, renouvelable dès qu'il a 24 heures) : une fois par semaine,
 * le jeton est échangé contre un neuf et réécrit chiffré. Tant que le cron tourne, il n'expire donc jamais.
 */

declare(strict_types=1);

namespace MadameAiguille\Instagram\Model\Feed;

use MadameAiguille\Instagram\Model\Api\ClientInterface;
use MadameAiguille\Instagram\Model\Api\InstagramException;
use MadameAiguille\Instagram\Model\Config;
use Magento\Framework\Stdlib\DateTime\DateTime;

class TokenRenewer
{
    public const INTERVAL = 7 * 86400;

    public function __construct(
        private readonly Config $config,
        private readonly ClientInterface $client,
        private readonly Storage $storage,
        private readonly DateTime $dateTime
    ) {
    }

    /**
     * @return bool vrai si le jeton a été renouvelé
     * @throws InstagramException
     */
    public function execute(bool $force = false): bool
    {
        $token = $this->config->getAccessToken();
        if (!$this->config->isEnabled() || $token === null) {
            return false;
        }

        $now = $this->dateTime->gmtTimestamp();
        if (!$force && !self::isDue($this->storage->getTokenRefreshedAt(), $now)) {
            return false;
        }

        $renewed = $this->client->refreshToken($token);
        $this->config->saveAccessToken($renewed['access_token']);
        $this->storage->saveTokenRefresh($now, $renewed['expires_in']);

        return true;
    }

    /**
     * Un jeton jamais renouvelé par le site (tout juste saisi) l'est au premier passage du cron.
     */
    public static function isDue(?int $refreshedAt, int $now): bool
    {
        return $refreshedAt === null || $now - $refreshedAt >= self::INTERVAL;
    }
}
