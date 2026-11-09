<?php
declare(strict_types=1);

namespace MadameAiguille\Instagram\Test\Unit\ViewModel;

use MadameAiguille\Instagram\Model\Config;
use MadameAiguille\Instagram\Model\Feed\Storage;
use MadameAiguille\Instagram\ViewModel\Gallery;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\GetBlockByIdentifierInterface;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Email\Model\Template\Filter;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Math\Random;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\ConfigInterface;
use Magento\PageBuilder\Model\Filter\Template as PageBuilderFilter;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class GalleryTest extends TestCase
{
    private const GALLERY = '<div class="home-gallery"><img src="ig-1.webp" alt="Trousse"></div>';

    public function testReadsTheIntroOfABlockSavedByPageBuilder(): void
    {
        // Élément « HTML Code » tel que Page Builder l'enregistre : le HTML saisi est encodé.
        $saved = '<div data-content-type="html" data-appearance="default" data-element="main">'
            . htmlspecialchars('<p class="home-intro">Les dernières nouvelles de l’atelier.</p>' . self::GALLERY)
            . '</div>';

        self::assertSame('Les dernières nouvelles de l’atelier.', $this->gallery($saved)->getIntro());
    }

    public function testReadsTheIntroOfARawBlock(): void
    {
        $saved = '<p class="home-intro">Un peu de douceur &amp; de couleurs.</p>' . self::GALLERY;

        self::assertSame('Un peu de douceur & de couleurs.', $this->gallery($saved)->getIntro());
    }

    public function testAcceptsOtherAttributesAndClassesOnTheParagraph(): void
    {
        $saved = '<p id="intro" class="text-center home-intro">Instants <strong>d’atelier</strong></p>';

        self::assertSame('Instants d’atelier', $this->gallery($saved)->getIntro());
    }

    public function testIsEmptyWithoutParagraphOrBlock(): void
    {
        self::assertSame('', $this->gallery(self::GALLERY)->getIntro());
        self::assertSame('', $this->gallery(null)->getIntro());
    }

    private function gallery(?string $content): Gallery
    {
        $store = $this->createStub(StoreInterface::class);
        $store->method('getId')->willReturn(1);
        $storeManager = $this->createStub(StoreManagerInterface::class);
        $storeManager->method('getStore')->willReturn($store);

        $getBlock = $this->createStub(GetBlockByIdentifierInterface::class);
        if ($content === null) {
            $getBlock->method('execute')->willThrowException(new NoSuchEntityException());
        } else {
            $block = $this->createStub(BlockInterface::class);
            $block->method('getContent')->willReturn($content);
            $getBlock->method('execute')->willReturnMap([['home_instagram', 1, $block]]);
        }

        // Filtre des blocs réduit au décodage de Page Builder, que le module branche sur tous les filtres de gabarit.
        $pageBuilder = new PageBuilderFilter(
            $this->createStub(LoggerInterface::class),
            $this->createStub(ConfigInterface::class),
            new Random(),
            new Json()
        );
        $filter = $this->createMock(Filter::class);
        $filter->method('setStoreId')->with(1)->willReturnSelf();
        $filter->method('filter')->willReturnCallback(fn (string $html): string => $pageBuilder->filter($html));
        $filterProvider = $this->createStub(FilterProvider::class);
        $filterProvider->method('getBlockFilter')->willReturn($filter);

        return new Gallery(
            $this->createStub(Config::class),
            $this->createStub(Storage::class),
            $storeManager,
            $getBlock,
            $filterProvider
        );
    }
}
