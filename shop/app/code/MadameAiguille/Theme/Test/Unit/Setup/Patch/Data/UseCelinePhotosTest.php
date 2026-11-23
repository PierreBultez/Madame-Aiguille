<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Test\Unit\Setup\Patch\Data;

use MadameAiguille\Theme\Setup\Patch\Data\UseCelinePhotos;
use PHPUnit\Framework\TestCase;

class UseCelinePhotosTest extends TestCase
{
    public function testReplacesTheOriginalImageTagOnly(): void
    {
        $content = '<section class="home-story">' . UseCelinePhotos::STORY_OLD . '<div><h2>L’histoire</h2></div></section>';

        $updated = UseCelinePhotos::replaced($content, UseCelinePhotos::STORY_OLD, UseCelinePhotos::STORY_NEW);

        self::assertSame(
            '<section class="home-story">' . UseCelinePhotos::STORY_NEW . '<div><h2>L’histoire</h2></div></section>',
            $updated
        );
        self::assertSame(
            $updated,
            UseCelinePhotos::replaced($updated, UseCelinePhotos::STORY_OLD, UseCelinePhotos::STORY_NEW),
            'Rejouer ne change rien.'
        );
    }

    public function testLeavesAnImageChangedInTheAdminUntouched(): void
    {
        $content = '<img src="{{media url=\'wysiwyg/ma-photo.jpg\'}}" alt="Ma photo">';

        self::assertSame(
            $content,
            UseCelinePhotos::replaced($content, UseCelinePhotos::HERO_OLD, UseCelinePhotos::HERO_NEW)
        );
    }

    public function testReplacesTheEncodedTagInAPageBuilderHtmlCodeBlock(): void
    {
        $content = '<div data-content-type="html">&lt;figure class="cms-media"&gt;'
            . UseCelinePhotos::encoded(UseCelinePhotos::ABOUT_OLD) . '&lt;/figure&gt;</div>';

        $updated = UseCelinePhotos::replaced($content, UseCelinePhotos::ABOUT_OLD, UseCelinePhotos::ABOUT_NEW);

        self::assertStringContainsString(UseCelinePhotos::encoded(UseCelinePhotos::ABOUT_NEW), $updated);
        self::assertStringNotContainsString('<img', $updated, 'Aucune balise brute dans le bloc Code HTML.');
    }

    public function testTheNewImagesExistInTheTheme(): void
    {
        $dir = dirname(__DIR__, 8) . '/design/frontend/MadameAiguille/default/web/images/home/';
        foreach (['trousse-velours-ecru.webp' => [1086, 760], 'celine.webp' => [1206, 860]] as $file => $size) {
            self::assertFileExists($dir . $file);
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            self::assertSame($size, array_slice(getimagesize($dir . $file), 0, 2), $file);
        }
    }
}
