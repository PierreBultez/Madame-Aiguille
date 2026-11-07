<?php

declare(strict_types=1);

namespace MadameAiguille\Theme\Test\Unit\Model\Catalog;

use MadameAiguille\Theme\Model\Catalog\WebpToJpeg;
use PHPUnit\Framework\TestCase;

class WebpToJpegTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        $this->path = (string) tempnam(sys_get_temp_dir(), 'webp');
    }

    protected function tearDown(): void
    {
        @unlink($this->path);
    }

    public function testRewritesWebpAsJpeg(): void
    {
        $this->writeImage('imagewebp', 255, 0, 0, 0);

        $this->assertSame('jpg', (new WebpToJpeg())->convert($this->path));
        $this->assertSame(IMAGETYPE_JPEG, getimagesize($this->path)[2]);
        $this->assertSame([40, 30], array_slice(getimagesize($this->path), 0, 2));
        $this->assertGreaterThan(200, $this->pixel()['red']);
    }

    public function testFillsTransparentAreasWithWhite(): void
    {
        $this->writeImage('imagewebp', 0, 0, 0, 127);

        (new WebpToJpeg())->convert($this->path);

        $this->assertSame(['red' => 255, 'green' => 255, 'blue' => 255], array_intersect_key(
            $this->pixel(),
            ['red' => 1, 'green' => 1, 'blue' => 1]
        ));
    }

    public function testLeavesPngNamedWebpUntouched(): void
    {
        $this->writeImage('imagepng', 0, 0, 255, 0);
        $before = (string) file_get_contents($this->path);

        $this->assertSame('png', (new WebpToJpeg())->convert($this->path));
        $this->assertSame($before, file_get_contents($this->path));
    }

    public function testRejectsUnreadableContent(): void
    {
        file_put_contents($this->path, 'ceci n\'est pas une image');

        $this->assertNull((new WebpToJpeg())->convert($this->path));
    }

    private function writeImage(string $writer, int $red, int $green, int $blue, int $alpha): void
    {
        $image = imagecreatetruecolor(40, 30);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, (int) imagecolorallocatealpha($image, $red, $green, $blue, $alpha));
        $writer($image, $this->path);
    }

    /** @return array<string, int> */
    private function pixel(): array
    {
        $image = imagecreatefromjpeg($this->path);
        $colors = imagecolorsforindex($image, (int) imagecolorat($image, 20, 15));

        return $colors;
    }
}
