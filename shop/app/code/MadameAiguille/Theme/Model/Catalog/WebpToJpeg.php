<?php
/**
 * Madame Aiguille — Magento 2.4.9 ne sait pas traiter le WebP : le contrôleur d'envoi de la galerie le refuse et
 * l'adaptateur GD qui redimensionne les photos de la vitrine ne le lit pas. Plutôt que de remplacer ces classes du
 * cœur, une photo WebP est réécrite en JPEG dès son arrivée ; Magento la traite ensuite comme n'importe quelle photo.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Model\Catalog;

class WebpToJpeg
{
    public const JPEG_QUALITY = 90;

    /** Extension que porte réellement le contenu, selon son type détecté */
    private const EXTENSIONS = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_GIF => 'gif',
    ];

    /**
     * Réécrit en place un fichier WebP en JPEG et rend l'extension qui correspond à son contenu.
     *
     * Un fichier nommé .webp qui contient déjà un JPEG, un PNG ou un GIF (cas d'un navigateur qui recompresse
     * l'image avant l'envoi) est laissé tel quel. Rend null pour un contenu illisible ou non converti, par exemple
     * un WebP animé : Magento le refuse alors lui-même.
     *
     * @param string $path
     * @return string|null
     */
    public function convert(string $path): ?string
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction -- lecture du type réel d'un fichier temporaire d'envoi
        $info = @getimagesize($path);
        $type = is_array($info) ? $info[2] : null;

        if ($type !== IMAGETYPE_WEBP) {
            return $type === null ? null : (self::EXTENSIONS[$type] ?? null);
        }

        $webp = @imagecreatefromwebp($path);
        if ($webp === false) {
            return null;
        }

        // Le JPEG n'a pas de transparence : un fond blanc remplace les zones transparentes
        $width = imagesx($webp);
        $height = imagesy($webp);
        $jpeg = imagecreatetruecolor($width, $height);
        imagefill($jpeg, 0, 0, (int) imagecolorallocate($jpeg, 255, 255, 255));
        imagecopy($jpeg, $webp, 0, 0, 0, 0, $width, $height);

        // Les images GD sont libérées en sortie de méthode (imagedestroy est sans effet depuis PHP 8)
        return imagejpeg($jpeg, $path, self::JPEG_QUALITY) ? 'jpg' : null;
    }
}
