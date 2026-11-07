<?php
/**
 * Madame Aiguille — une photo WebP envoyée dans la galerie d'une fiche produit (Catalogue › Produits › Images et
 * vidéos) est réécrite en JPEG avant que le contrôleur de Magento ne la lise. Son nom passe de .webp à .jpg ; le
 * contrôleur, qui n'accepte que jpg, jpeg, gif et png, la contrôle et l'enregistre ensuite comme d'habitude.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Plugin\Catalog;

use Magento\Catalog\Controller\Adminhtml\Product\Gallery\Upload;
use MadameAiguille\Theme\Model\Catalog\WebpToJpeg;

class ConvertWebpGalleryUpload
{
    /** Champ d'envoi lu par le contrôleur (Uploader, fileId « image ») */
    public const FILE_ID = 'image';

    private const MIME_TYPES = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif'];

    public function __construct(
        private readonly WebpToJpeg $converter
    ) {
    }

    /**
     * Convertit le fichier envoyé s'il porte l'extension .webp.
     *
     * @param Upload $subject
     * @return null
     */
    public function beforeExecute(Upload $subject)
    {
        // phpcs:disable Magento2.Security.Superglobal -- l'Uploader de Magento lit $_FILES directement : c'est là
        // que le nom du fichier converti doit être corrigé
        $file = $_FILES[self::FILE_ID] ?? null;
        if (!is_array($file) || !is_string($file['name'] ?? null) || !is_string($file['tmp_name'] ?? null)
            || strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'webp'
            || ($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK
        ) {
            return null;
        }

        $extension = $this->converter->convert($file['tmp_name']);
        if ($extension === null) {
            return null;
        }

        $_FILES[self::FILE_ID]['name'] = pathinfo($file['name'], PATHINFO_FILENAME) . '.' . $extension;
        $_FILES[self::FILE_ID]['type'] = self::MIME_TYPES[$extension];
        clearstatcache(true, $file['tmp_name']);
        $_FILES[self::FILE_ID]['size'] = (int) filesize($file['tmp_name']);
        // phpcs:enable Magento2.Security.Superglobal

        return null;
    }
}
