<?php
/**
 * Publications synchronisées prêtes à afficher : image locale, texte alternatif, lien vers la publication.
 * Vide si la synchronisation est coupée ou n'a encore rien rapporté : l'accueil garde alors le bloc CMS.
 */

declare(strict_types=1);

namespace MadameAiguille\Instagram\ViewModel;

use MadameAiguille\Instagram\Model\Config;
use MadameAiguille\Instagram\Model\Feed\Storage;
use Magento\Cms\Api\GetBlockByIdentifierInterface;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class Gallery implements ArgumentInterface
{
    public function __construct(
        private readonly Config $config,
        private readonly Storage $storage,
        private readonly StoreManagerInterface $storeManager,
        private readonly GetBlockByIdentifierInterface $getBlock,
        private readonly FilterProvider $filterProvider
    ) {
    }

    /**
     * @return list<array{src: string, alt: string, permalink: string, width: int, height: int}>
     */
    public function getItems(): array
    {
        if (!$this->config->isEnabled()) {
            return [];
        }

        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $items = [];
        foreach (array_slice($this->storage->getItems(), 0, $this->config->getCount()) as $item) {
            $items[] = [
                'src' => $mediaUrl . $item['file'],
                'alt' => $item['alt'],
                'permalink' => $item['permalink'],
                'width' => (int) $item['width'],
                'height' => (int) $item['height'],
            ];
        }

        return $items;
    }

    /**
     * Phrase d'introduction du bloc `home_instagram` (paragraphe `home-intro`), gardée au-dessus des publications
     * synchronisées : elle reste modifiable dans le bloc. Le contenu passe d'abord par le filtre des blocs CMS, comme
     * à l'affichage : Page Builder enregistre son élément « HTML Code » encodé (`&lt;p class=&quot;…`).
     */
    public function getIntro(): string
    {
        $storeId = (int) $this->storeManager->getStore()->getId();
        try {
            $content = $this->getBlock->execute('home_instagram', $storeId)->getContent();
        } catch (NoSuchEntityException) {
            return '';
        }

        $html = $this->filterProvider->getBlockFilter()->setStoreId($storeId)->filter((string) $content);

        return preg_match('#<p\b[^>]*\bclass="[^"]*\bhome-intro\b[^"]*"[^>]*>(.*?)</p>#s', $html, $match)
            ? trim(html_entity_decode(strip_tags($match[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'))
            : '';
    }
}
