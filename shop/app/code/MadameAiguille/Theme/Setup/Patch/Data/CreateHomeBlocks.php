<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\BlockInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\Store;

/** Contenus initiaux génériques : jamais de réécriture des blocs existants. */
class CreateHomeBlocks implements DataPatchInterface
{
    public function __construct(
        private readonly BlockRepositoryInterface $repository,
        private readonly BlockInterfaceFactory $factory,
        private readonly SearchCriteriaBuilder $criteriaBuilder
    ) {
    }

    public function apply(): self
    {
        foreach ($this->getBlocks() as $identifier => [$title, $content]) {
            $criteria = $this->criteriaBuilder->addFilter(BlockInterface::IDENTIFIER, $identifier)->create();
            if ($this->repository->getList($criteria)->getTotalCount() > 0) {
                continue;
            }
            $block = $this->factory->create();
            $block->setIdentifier($identifier)->setTitle($title)->setContent($content)
                ->setIsActive(true)->setStores([Store::DEFAULT_STORE_ID]);
            $this->repository->save($block);
        }
        return $this;
    }

    private function getBlocks(): array
    {
        return [
            'home_hero' => [
                'Accueil — visuel principal',
                <<<'HTML'
<section class="home-hero" aria-labelledby="home-hero-title">
<div class="home-hero-image"><img src="{{view url='images/home/hero.png'}}" alt="Trousse fleurie posée sur du linge clair" width="1024" height="1024" fetchpriority="high"></div>
<div class="home-hero-copy"><p class="home-kicker">Des tissus, du fil &amp; de la douceur</p><h1 id="home-hero-title">De petites créations,<br>de grands bonheurs</h1><p>Des accessoires en tissu pour embellir les petits instants du quotidien.</p><a class="btn btn-primary" href="{{store direct_url='trousses-de-toilette.html'}}">Découvrir la collection</a></div>
</section>
HTML
            ],
            'home_new' => [
                'Nouveautés',
                <<<'HTML'
<p class="home-intro">Les dernières créations à découvrir, au fil des envies.</p>
HTML
            ],
            'home_featured' => [
                'Les Incontournables',
                <<<'HTML'
<p class="home-intro">Une sélection de petits bonheurs à emporter partout.</p>
HTML
            ],
            'home_actualities' => [
                'Les nouvelles de l’atelier',
                <<<'HTML'
<div class="home-news"><h3>Gardons le fil</h3><p>Retrouvez ici les prochains rendez-vous, les nouvelles de l’atelier et les informations pratiques de la boutique.</p><a href="{{store url='contact'}}">Une question ? Écrivez-nous</a></div>
HTML
            ],
            'home_story' => [
                'Accueil — histoire',
                <<<'HTML'
<section class="home-story" aria-labelledby="home-story-title"><img src="{{view url='images/home/atelier.png'}}" alt="Tissus et accessoires de couture à l’atelier" width="1024" height="1024" loading="lazy"><div><p class="home-kicker">Derrière les coutures</p><h2 id="home-story-title">L’histoire de Madame Aiguille</h2><p>Tout commence avec un tissu, une couleur, une envie de créer.</p><p>Madame Aiguille vous invite dans un univers de créations textiles pensées pour accompagner votre quotidien.</p><a href="{{store url='a-propos'}}">Découvrir l’univers de l’atelier</a></div></section>
HTML
            ],
            'home_why' => [
                'Pourquoi choisir Madame Aiguille',
                <<<'HTML'
<div class="home-reasons"><div><span aria-hidden="true">✂</span><h3>Le goût du détail</h3><p>Des finitions à découvrir de près.</p></div><div><span aria-hidden="true">♡</span><h3>De petites séries</h3><p>Des créations au rythme des tissus.</p></div><div><span aria-hidden="true">❀</span><h3>Des motifs choisis</h3><p>Des couleurs pour chaque envie.</p></div><div><span aria-hidden="true">✉</span><h3>Un échange, tout simplement</h3><p>Une question sur une création ? Écrivez-nous.</p></div></div>
HTML
            ],
            'home_instagram' => [
                'Instants d’atelier',
                <<<'HTML'
<p class="home-intro">Un peu de douceur et de couleurs en images.</p><div class="home-gallery"><img src="{{view url='images/home/ig-1.png'}}" alt="Trousse et panier de linge" width="1024" height="1024" loading="lazy"><img src="{{view url='images/home/ig-2.png'}}" alt="Pochette à livre bleue" width="1024" height="1024" loading="lazy"><img src="{{view url='images/home/ig-3.png'}}" alt="Pochettes nomades et café" width="1024" height="1024" loading="lazy"><img src="{{view url='images/home/ig-4.png'}}" alt="Petit sac fleuri" width="1024" height="1024" loading="lazy"></div>
HTML
            ],
            'home_newsletter' => [
                'Rejoignez l’univers Madame Aiguille',
                <<<'HTML'
<p class="home-intro">Nouvelles créations et nouvelles de l’atelier, de temps en temps dans votre boîte mail.</p>
HTML
            ],
        ];
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
