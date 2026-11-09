<?php
declare(strict_types=1);

namespace MadameAiguille\Instagram\Block;

use MadameAiguille\Instagram\Model\Feed\FlushGalleryCache;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;

/** Galerie de l'accueil : la page entière porte le tag, purgé quand une synchronisation change les publications. */
class Gallery extends Template implements IdentityInterface
{
    public function getIdentities(): array
    {
        return [FlushGalleryCache::GALLERY_TAG];
    }
}
