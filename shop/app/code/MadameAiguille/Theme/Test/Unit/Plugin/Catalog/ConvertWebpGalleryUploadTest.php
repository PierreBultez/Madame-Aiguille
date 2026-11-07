<?php

declare(strict_types=1);

namespace MadameAiguille\Theme\Test\Unit\Plugin\Catalog;

use MadameAiguille\Theme\Model\Catalog\WebpToJpeg;
use MadameAiguille\Theme\Plugin\Catalog\ConvertWebpGalleryUpload;
use Magento\Catalog\Controller\Adminhtml\Product\Gallery\Upload;
use PHPUnit\Framework\TestCase;

class ConvertWebpGalleryUploadTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        $this->path = (string) tempnam(sys_get_temp_dir(), 'webp');
        $image = imagecreatetruecolor(20, 20);
        imagewebp($image, $this->path);
    }

    protected function tearDown(): void
    {
        @unlink($this->path);
        unset($_FILES[ConvertWebpGalleryUpload::FILE_ID]);
    }

    public function testRenamesConvertedWebpAsJpeg(): void
    {
        $_FILES['image'] = $this->upload('Pochette lin.WEBP');

        $this->plugin()->beforeExecute($this->createMock(Upload::class));

        $this->assertSame('Pochette lin.jpg', $_FILES['image']['name']);
        $this->assertSame('image/jpeg', $_FILES['image']['type']);
        $this->assertSame(filesize($this->path), $_FILES['image']['size']);
        $this->assertSame(IMAGETYPE_JPEG, getimagesize($this->path)[2]);
    }

    public function testLeavesOtherFormatsAlone(): void
    {
        $_FILES['image'] = $this->upload('pochette.png');
        $converter = $this->createMock(WebpToJpeg::class);
        $converter->expects($this->never())->method('convert');

        (new ConvertWebpGalleryUpload($converter))->beforeExecute($this->createMock(Upload::class));

        $this->assertSame('pochette.png', $_FILES['image']['name']);
    }

    public function testKeepsWebpNameWhenConversionFails(): void
    {
        $_FILES['image'] = $this->upload('animee.webp');
        $converter = $this->createMock(WebpToJpeg::class);
        $converter->method('convert')->willReturn(null);

        (new ConvertWebpGalleryUpload($converter))->beforeExecute($this->createMock(Upload::class));

        $this->assertSame('animee.webp', $_FILES['image']['name']);
    }

    /** @return array<string, mixed> */
    private function upload(string $name): array
    {
        return ['name' => $name, 'type' => 'image/webp', 'tmp_name' => $this->path, 'error' => UPLOAD_ERR_OK,
            'size' => filesize($this->path)];
    }

    private function plugin(): ConvertWebpGalleryUpload
    {
        return new ConvertWebpGalleryUpload(new WebpToJpeg());
    }
}
