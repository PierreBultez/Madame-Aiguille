<?php
/**
 * Photos de la galerie réduites côté serveur avant envoi (environ 1 100 px sur le grand côté, comme la fiche en
 * vitrine), puis encodées en base64 pour l'API. Les photos ne sont lues que dans `pub/media/catalog/product` et
 * `pub/media/tmp/catalog/product` (photo chargée mais pas encore enregistrée), d'après le chemin que la galerie
 * de Magento leur donne (`/c/t/coffret.jpg`) : aucun autre fichier du serveur n'est accessible.
 */

declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Psr\Log\LoggerInterface;

class GalleryImages
{
    public const MAX_IMAGES = 6;
    public const MAX_SIDE = 1100;

    private const MEDIA_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private const FILE_PATTERN = '#^/[A-Za-z0-9_-]/[A-Za-z0-9_-]/[A-Za-z0-9_.-]+$#';

    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly AdapterFactory $adapterFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param list<mixed> $files Chemins de la galerie, dans l'ordre de la fiche
     * @return list<array{file: string, mediaType: string, data: string}>
     */
    public function prepare(array $files): array
    {
        $media = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $images = [];
        foreach (array_slice(array_values(array_unique(array_filter($files, 'is_string'))), 0, self::MAX_IMAGES) as $file) {
            if (!preg_match(self::FILE_PATTERN, $file) || str_contains($file, '..')) {
                continue;
            }
            // Photo chargée mais pas encore enregistrée : « .tmp » ajouté par la galerie, fichier dans tmp/
            $candidates = str_ends_with($file, '.tmp')
                ? ['tmp/catalog/product' . substr($file, 0, -4)]
                : ['catalog/product' . $file];
            $path = null;
            foreach ($candidates as $candidate) {
                if ($media->isFile($candidate)) {
                    $path = $media->getAbsolutePath($candidate);
                    break;
                }
            }
            $image = $path === null ? null : $this->resize($path);
            if ($image !== null) {
                $images[] = ['file' => $file] + $image;
            }
        }

        return $images;
    }

    /**
     * @return array{mediaType: string, data: string}|null
     */
    private function resize(string $path): ?array
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $target = 'tmp/madameaiguille_ai/' . bin2hex(random_bytes(8)) . '-' . basename($path);
        try {
            $adapter = $this->adapterFactory->create();
            $adapter->open($path);
            $adapter->keepAspectRatio(true);
            $adapter->constrainOnly(true);
            $adapter->keepTransparency(true);
            $adapter->resize(self::MAX_SIDE, self::MAX_SIDE);
            $directory->create('tmp/madameaiguille_ai');
            $adapter->save($directory->getAbsolutePath($target));
            $mediaType = (string) $adapter->getMimeType();
            $data = $directory->readFile($target);
        } catch (\Exception $exception) {
            $this->logger->warning('Assistant IA : photo illisible ignorée (' . basename($path) . ') : '
                . $exception->getMessage());
            return null;
        } finally {
            if ($directory->isFile($target)) {
                $directory->delete($target);
            }
        }

        return in_array($mediaType, self::MEDIA_TYPES, true)
            ? ['mediaType' => $mediaType, 'data' => base64_encode($data)]
            : null;
    }
}
