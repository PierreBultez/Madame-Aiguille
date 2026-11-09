<?php
declare(strict_types=1);

namespace MadameAiguille\Instagram\Test\Unit\Model\Feed;

use MadameAiguille\Instagram\Model\Feed\Selection;
use PHPUnit\Framework\TestCase;

class SelectionTest extends TestCase
{
    public function testPicksOneImagePerPostNewestFirst(): void
    {
        $picked = Selection::pick([
            ['id' => '1', 'media_type' => 'IMAGE', 'media_url' => 'https://cdn/1.jpg', 'permalink' => 'https://ig/p/1',
                'caption' => 'Vieille', 'timestamp' => '2026-11-01T10:00:00+0000'],
            ['id' => '2', 'media_type' => 'VIDEO', 'media_url' => 'https://cdn/2.mp4', 'thumbnail_url' => 'https://cdn/2.jpg',
                'permalink' => 'https://ig/p/2', 'timestamp' => '2026-11-08T10:00:00+0000'],
            ['id' => '3', 'media_type' => 'CAROUSEL_ALBUM', 'media_url' => 'https://cdn/3.jpg',
                'permalink' => 'https://ig/p/3', 'timestamp' => '2026-11-05T10:00:00+0000'],
            ['id' => '4', 'media_type' => 'VIDEO', 'media_url' => 'https://cdn/4.mp4', 'permalink' => 'https://ig/p/4',
                'timestamp' => '2026-11-09T10:00:00+0000'],
        ], 2);

        self::assertSame(['2', '3'], array_column($picked, 'id'), 'Une vidéo sans vignette est écartée.');
        self::assertSame('https://cdn/2.jpg', $picked[0]['image_url']);
    }

    public function testRejectsUnsafeIdsAndLinks(): void
    {
        self::assertSame([], Selection::pick([
            ['id' => '../../etc', 'media_type' => 'IMAGE', 'media_url' => 'javascript:alert(1)', 'permalink' => 'https://ig/p/x'],
            ['id' => '5', 'media_type' => 'IMAGE', 'media_url' => 'https://cdn/5.jpg', 'permalink' => 'javascript:alert(1)'],
        ], 4));
    }

    public function testAltIsTheFirstSentenceWithoutHashtags(): void
    {
        self::assertSame(
            'Nouvelle trousse en lin fleuri !',
            Selection::alt("Nouvelle trousse en lin fleuri ! Elle arrive vendredi.\n#madameaiguille @celine")
        );
        self::assertSame(Selection::FALLBACK_ALT, Selection::alt('#couture #faitmain'));
        self::assertSame(120, mb_strlen(Selection::alt(str_repeat('é', 200))));
    }
}
