<?php
/**
 * Doublure locale de l'API Instagram, en mode développeur seulement, avec le jeton sentinelle « doublure-locale » :
 * quatre publications qui pointent vers les visuels d'origine du thème, servis par le site local. Jamais en
 * production (ClientRouter).
 */

declare(strict_types=1);

namespace MadameAiguille\Instagram\Model\Api;

use Magento\Framework\View\Asset\Repository;

class StubClient implements ClientInterface
{
    public const TOKEN = 'doublure-locale';

    public function __construct(
        private readonly Repository $assetRepository
    ) {
    }

    public function fetchMedia(string $token, int $limit, ?string $accountId = null): array
    {
        $captions = ['Trousse et panier de linge', 'Pochette à livre bleue', 'Pochettes nomades et café', 'Petit sac fleuri'];
        $media = [];
        foreach ($captions as $index => $caption) {
            $media[] = [
                'id' => 'doublure-' . ($index + 1),
                'media_type' => 'IMAGE',
                'media_url' => $this->assetRepository->getUrlWithParams(
                    sprintf('images/home/ig-%d.png', $index + 1),
                    ['area' => 'frontend', 'theme' => 'MadameAiguille/default']
                ),
                'permalink' => 'https://www.instagram.com/madame.aiguille/',
                'caption' => $caption . ' #madameaiguille',
                'timestamp' => '2026-11-0' . (8 - $index) . 'T10:00:00+0000',
            ];
        }

        return array_slice($media, 0, $limit);
    }

    public function isRenewable(string $token): bool
    {
        return true;
    }

    public function refreshToken(string $token): array
    {
        return ['access_token' => self::TOKEN, 'expires_in' => 5184000];
    }
}
