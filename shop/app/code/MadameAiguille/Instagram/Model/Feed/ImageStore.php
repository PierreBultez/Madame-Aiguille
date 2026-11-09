<?php
/**
 * Copie locale des images de la galerie, dans pub/media/madameaiguille/instagram : téléchargées côté serveur,
 * recadrées au carré et réduites à 640 px. L'accueil ne charge donc jamais rien depuis Instagram.
 */

declare(strict_types=1);

namespace MadameAiguille\Instagram\Model\Feed;

use MadameAiguille\Instagram\Model\Api\InstagramException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Image\AdapterFactory;

class ImageStore
{
    public const DIRECTORY = 'madameaiguille/instagram';
    public const SIZE = 640;
    private const EXTENSIONS = ['image/jpeg' => 'jpg', 'image/png' => 'png'];

    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly CurlFactory $curlFactory,
        private readonly AdapterFactory $imageAdapterFactory
    ) {
    }

    /**
     * Chemin relatif au dossier media de l'image de la publication, téléchargée si elle n'existe pas encore.
     *
     * @throws InstagramException
     */
    public function store(string $id, string $url): string
    {
        $media = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        foreach (self::EXTENSIONS as $extension) {
            $existing = self::DIRECTORY . '/' . $id . '.' . $extension;
            if ($media->isFile($existing)) {
                return $existing;
            }
        }

        $curl = $this->curlFactory->create();
        $curl->setTimeout(20);
        try {
            $curl->get($url);
        } catch (\Exception) {
            throw new InstagramException(sprintf('Image de la publication %s injoignable.', $id));
        }
        if ($curl->getStatus() !== 200 || $curl->getBody() === '') {
            throw new InstagramException(sprintf('Image de la publication %s refusée (HTTP %d).', $id, $curl->getStatus()));
        }

        $tmp = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $tmpPath = 'tmp/instagram-' . $id;
        $tmp->writeFile($tmpPath, $this->toReadableImage($curl->getBody()));
        try {
            $image = $this->imageAdapterFactory->create();
            $image->open($tmp->getAbsolutePath($tmpPath));
            $extension = self::EXTENSIONS[$image->getMimeType()] ?? null;
            if ($extension === null) {
                throw new InstagramException(sprintf('Format d\'image inattendu pour la publication %s.', $id));
            }
            [$width, $height] = [$image->getOriginalWidth(), $image->getOriginalHeight()];
            $side = min($width, $height);
            $left = intdiv($width - $side, 2);
            $top = intdiv($height - $side, 2);
            $image->crop($top, $left, $width - $side - $left, $height - $side - $top);
            $image->resize(self::SIZE, self::SIZE);
            $media->create(self::DIRECTORY);
            $file = $id . '.' . $extension;
            $image->save($media->getAbsolutePath(self::DIRECTORY), $file);
        } catch (InstagramException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            throw new InstagramException(
                sprintf('Image de la publication %s illisible : %s', $id, $exception->getMessage())
            );
        } finally {
            $tmp->delete($tmpPath);
        }

        return self::DIRECTORY . '/' . $file;
    }

    /**
     * L'adaptateur d'images de Magento (GD) ne lit pas le WebP : une image WebP est d'abord convertie en JPEG.
     */
    private function toReadableImage(string $bytes): string
    {
        if (!str_starts_with($bytes, 'RIFF') || substr($bytes, 8, 4) !== 'WEBP' || !function_exists('imagecreatefromwebp')) {
            return $bytes;
        }
        $image = imagecreatefromstring($bytes);
        if ($image === false) {
            return $bytes;
        }
        ob_start();
        imagejpeg($image, null, 90);

        return (string) ob_get_clean();
    }

    /**
     * Supprime les images qui ne sont plus affichées.
     *
     * @param list<string> $keep chemins relatifs au dossier media
     */
    public function prune(array $keep): void
    {
        $media = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        if (!$media->isDirectory(self::DIRECTORY)) {
            return;
        }
        foreach ($media->read(self::DIRECTORY) as $path) {
            if ($media->isFile($path) && !in_array($path, $keep, true)) {
                $media->delete($path);
            }
        }
    }
}
