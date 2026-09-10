<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\Data\PageInterfaceFactory;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\Store;

/** Ajoute Nos tissus et remplace uniquement le placeholder initial d'À propos. */
class CreateFabricPageAndPrepareAbout implements DataPatchInterface
{
    private const ABOUT_PLACEHOLDER = '<p>À rédiger — l’histoire de Madame Aiguille, l’atelier, les séries limitées.</p>';

    public function __construct(
        private readonly PageRepositoryInterface $repository,
        private readonly PageInterfaceFactory $factory,
        private readonly SearchCriteriaBuilder $criteriaBuilder
    ) {
    }

    public function apply(): self
    {
        $about = $this->findPage('a-propos');
        if ($about && trim((string) $about->getContent()) === self::ABOUT_PLACEHOLDER) {
            $about->setContent($this->getAboutContent());
            $this->repository->save($about);
        }

        if (!$this->findPage('nos-tissus')) {
            $page = $this->factory->create();
            $page->setIdentifier('nos-tissus')
                ->setTitle('Nos tissus')
                ->setContentHeading('Nos tissus')
                ->setContent($this->getFabricContent())
                ->setPageLayout('1column')
                ->setIsActive(true)
                ->setStores([Store::DEFAULT_STORE_ID]);
            $this->repository->save($page);
        }

        return $this;
    }

    private function findPage(string $identifier): ?PageInterface
    {
        $criteria = $this->criteriaBuilder
            ->addFilter(PageInterface::IDENTIFIER, $identifier)
            ->create();
        $items = $this->repository->getList($criteria)->getItems();

        return $items ? reset($items) : null;
    }

    private function getAboutContent(): string
    {
        return <<<'HTML'
<figure class="cms-media"><img src="{{view url='images/home/atelier.png'}}" alt="Création textile en cours à l’atelier" width="930" height="591"></figure>
<p class="cms-lead">Bienvenue dans l’univers de Madame Aiguille, un atelier de créations textiles imaginées au fil des tissus et des envies.</p>
<h2>Une histoire à raconter</h2>
<p>Cette page accueillera l’histoire de Céline, son parcours et ce qui l’a menée à créer Madame Aiguille. Le texte définitif sera publié après sa validation.</p>
<h2>Des créations en petites séries</h2>
<p>Les collections évoluent avec les motifs et les matières sélectionnés pour l’atelier. Chaque fiche produit précise les caractéristiques de la création et les quantités encore disponibles.</p>
<blockquote>Chaque tissu ouvre une nouvelle histoire.</blockquote>
<div class="cms-actions"><a class="btn btn-primary" href="{{store direct_url=''}}">Découvrir les créations</a><a class="btn btn-secondary" href="{{store url='contact'}}">Poser une question</a></div>
HTML;
    }

    private function getFabricContent(): string
    {
        return <<<'HTML'
<p class="cms-lead">Voici quelques motifs de l’univers Madame Aiguille. Notez leur référence pour en parler dans votre message ; leur disponibilité sera confirmée selon les coupons présents à l’atelier.</p>
<div class="fabric-grid">
<figure class="fabric-card"><img src="{{view url='images/home/ig-1.png'}}" alt="Exemple de motif textile rose clair" width="471" height="471"><figcaption><strong>Motif T01</strong><span>Disponibilité sur demande</span></figcaption></figure>
<figure class="fabric-card"><img src="{{view url='images/home/ig-2.png'}}" alt="Exemple de motif textile bleu" width="414" height="414"><figcaption><strong>Motif T02</strong><span>Disponibilité sur demande</span></figcaption></figure>
<figure class="fabric-card"><img src="{{view url='images/home/ig-3.png'}}" alt="Exemple de motif textile aux tons poudrés" width="408" height="408"><figcaption><strong>Motif T03</strong><span>Disponibilité sur demande</span></figcaption></figure>
<figure class="fabric-card"><img src="{{view url='images/home/ig-4.png'}}" alt="Exemple de motif textile fleuri" width="483" height="483"><figcaption><strong>Motif T04</strong><span>Disponibilité sur demande</span></figcaption></figure>
</div>
<aside class="cms-callout"><h2>Vous avez un motif en tête ?</h2><p>Indiquez la référence du tissu et la création concernée dans le formulaire de contact. Vous pouvez aussi joindre une photo d’inspiration.</p><a class="btn btn-primary" href="{{store url='contact'}}">Échanger autour d’un projet</a></aside>
HTML;
    }

    public static function getDependencies(): array
    {
        return [CreateStaticPages::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
