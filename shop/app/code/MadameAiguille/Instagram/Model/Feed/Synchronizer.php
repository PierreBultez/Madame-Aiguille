<?php
/**
 * Synchronisation de la galerie : lit les dernières publications, copie leurs images sur le serveur, mémorise la
 * liste et purge l'accueil si elle a changé. En cas d'échec, la galerie précédente reste affichée.
 */

declare(strict_types=1);

namespace MadameAiguille\Instagram\Model\Feed;

use MadameAiguille\Instagram\Model\Api\ClientInterface;
use MadameAiguille\Instagram\Model\Api\InstagramException;
use MadameAiguille\Instagram\Model\Config;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Synchronizer
{
    /** Publications demandées : de quoi en écarter quelques-unes sans image (réels sans vignette…). */
    private const FETCH = 20;

    public function __construct(
        private readonly Config $config,
        private readonly ClientInterface $client,
        private readonly ImageStore $imageStore,
        private readonly Storage $storage,
        private readonly FlushGalleryCache $flushGalleryCache,
        private readonly DateTime $dateTime
    ) {
    }

    /**
     * @return int nombre de publications affichées ; 0 si la synchronisation est coupée
     * @throws InstagramException
     */
    public function execute(): int
    {
        $token = $this->config->getAccessToken();
        if (!$this->config->isEnabled() || $token === null) {
            return 0;
        }

        $items = [];
        $media = $this->client->fetchMedia($token, self::FETCH, $this->config->getAccountId());
        foreach (Selection::pick($media, $this->config->getCount()) as $post) {
            $file = $this->imageStore->store($post['id'], $post['image_url']);
            $items[] = [
                'id' => $post['id'],
                'file' => $file,
                'permalink' => $post['permalink'],
                'alt' => $post['alt'],
                'width' => ImageStore::SIZE,
                'height' => ImageStore::SIZE,
            ];
        }
        if ($items === []) {
            throw new InstagramException('Aucune publication avec image sur le compte : galerie précédente conservée.');
        }

        $changed = $items !== $this->storage->getItems();
        $this->storage->saveFeed($items, $this->dateTime->gmtTimestamp());
        $this->imageStore->prune(array_column($items, 'file'));
        if ($changed) {
            $this->flushGalleryCache->execute();
        }

        return count($items);
    }
}
