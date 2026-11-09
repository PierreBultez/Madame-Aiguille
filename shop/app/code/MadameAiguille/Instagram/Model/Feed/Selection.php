<?php
/**
 * Choix des publications à afficher, à partir de la réponse de l'API : les plus récentes d'abord, une image par
 * publication (la première d'un carrousel, la vignette d'une vidéo), un lien vers la publication et un texte
 * alternatif tiré de la légende.
 */

declare(strict_types=1);

namespace MadameAiguille\Instagram\Model\Feed;

class Selection
{
    public const FALLBACK_ALT = 'Publication Instagram de Madame Aiguille';
    private const ALT_LENGTH = 120;

    /**
     * @param list<array<string, mixed>> $media
     * @return list<array{id: string, image_url: string, permalink: string, alt: string, timestamp: string}>
     */
    public static function pick(array $media, int $count): array
    {
        $picked = [];
        foreach ($media as $item) {
            $id = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($item['id'] ?? ''));
            $type = (string) ($item['media_type'] ?? '');
            $imageUrl = (string) ($type === 'VIDEO' ? ($item['thumbnail_url'] ?? '') : ($item['media_url'] ?? ''));
            $permalink = (string) ($item['permalink'] ?? '');
            if ($id === '' || !self::isHttpUrl($imageUrl) || !self::isHttpUrl($permalink)) {
                continue;
            }
            $picked[] = [
                'id' => $id,
                'image_url' => $imageUrl,
                'permalink' => $permalink,
                'alt' => self::alt((string) ($item['caption'] ?? '')),
                'timestamp' => (string) ($item['timestamp'] ?? ''),
            ];
        }

        usort($picked, static fn (array $a, array $b): int => strcmp($b['timestamp'], $a['timestamp']));

        return array_slice($picked, 0, $count);
    }

    /**
     * Première phrase de la légende, sans mots-dièse ni mentions ; texte générique si elle est vide.
     */
    public static function alt(string $caption): string
    {
        $text = (string) preg_replace('/[#@][\p{L}\p{N}_.]+/u', '', $caption);
        $text = trim((string) preg_replace('/\s+/u', ' ', $text));
        $sentence = preg_split('/(?<=[.!?…])\s/u', $text)[0] ?? '';
        if (mb_strlen($sentence) > self::ALT_LENGTH) {
            $sentence = rtrim(mb_substr($sentence, 0, self::ALT_LENGTH - 1)) . '…';
        }

        return $sentence !== '' ? $sentence : self::FALLBACK_ALT;
    }

    private static function isHttpUrl(string $url): bool
    {
        return (bool) preg_match('#^https?://#', $url);
    }
}
