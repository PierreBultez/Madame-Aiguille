<?php

declare(strict_types=1);

namespace MadameAiguille\Theme\Test\Unit\View;

use PHPUnit\Framework\TestCase;

/**
 * La copie du téléverseur de la galerie (view/adminhtml/web/js/media-uploader.js) doit rester identique à
 * l'original de Magento, hors en-tête et liste des extensions : après une mise à jour qui le modifie, ce test
 * échoue et la copie est à refaire depuis le nouvel original.
 */
class MediaUploaderCopyTest extends TestCase
{
    private const ALLOWED_EXT = "                allowedExt = ['jpeg', 'jpg', 'png', 'gif'],\n";

    public function testCopyFollowsMagentoOriginal(): void
    {
        $root = dirname(__DIR__, 7);
        $original = (string) file_get_contents($root . '/vendor/magento/module-backend/view/adminhtml/web/js/media-uploader.js');
        $copy = (string) file_get_contents(dirname(__DIR__, 3) . '/view/adminhtml/web/js/media-uploader.js');

        $this->assertSame(1, substr_count($original, self::ALLOWED_EXT), 'La liste allowedExt de Magento a changé.');

        $copy = preg_replace('~\A/\*\*\n \* Madame Aiguille.*?\*/\n\n~s', '', $copy, 1);
        $copy = preg_replace(
            "~                // Madame Aiguille.*?\n                \\),\n~s",
            self::ALLOWED_EXT,
            (string) $copy,
            1
        );

        $this->assertSame($original, $copy);
    }
}
