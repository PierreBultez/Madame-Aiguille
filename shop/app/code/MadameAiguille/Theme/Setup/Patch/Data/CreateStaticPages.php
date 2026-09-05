<?php
/**
 * Madame Aiguille — pages CMS statiques ciblées par le footer
 *
 * Crée les pages vides (contenu « À rédiger ») pour qu'aucun lien du pied de
 * page ne renvoie une 404 avant le lot de rédaction. Idempotent : une page
 * déjà présente (même identifiant) n'est jamais modifiée.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\Data\PageInterfaceFactory;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\Store;

class CreateStaticPages implements DataPatchInterface
{
    /**
     * identifiant => [titre, contenu]
     */
    private const PAGES = [
        'a-propos' => [
            'title' => 'L’histoire de Madame Aiguille',
            'content' => '<p>À rédiger — l’histoire de Madame Aiguille, l’atelier, les séries limitées.</p>',
        ],
        'livraison-retours' => [
            'title' => 'Livraison et retours',
            'content' => '<p>À rédiger — transporteurs, délais, frais de port calculés au poids, remise en main propre, retours.</p>',
        ],
        'cgv' => [
            'title' => 'Conditions générales de vente',
            'content' => '<p>À rédiger — conditions générales de vente.</p>',
        ],
        'mentions-legales' => [
            'title' => 'Mentions légales',
            'content' => '<p>À rédiger — éditeur, hébergeur, statut, contact.</p>',
        ],
        'confidentialite' => [
            'title' => 'Politique de confidentialité',
            'content' => '<p>À rédiger — données collectées, cookies, droits RGPD.</p>',
        ],
    ];

    public function __construct(
        private readonly PageRepositoryInterface $pageRepository,
        private readonly PageInterfaceFactory $pageFactory,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    public function apply(): self
    {
        foreach (self::PAGES as $identifier => $data) {
            if ($this->pageExists($identifier)) {
                continue;
            }

            /** @var PageInterface $page */
            $page = $this->pageFactory->create();
            $page->setIdentifier($identifier)
                ->setTitle($data['title'])
                ->setContentHeading($data['title'])
                ->setContent($data['content'])
                ->setPageLayout('1column')
                ->setIsActive(true)
                ->setStores([Store::DEFAULT_STORE_ID]);

            $this->pageRepository->save($page);
        }

        return $this;
    }

    private function pageExists(string $identifier): bool
    {
        $criteria = $this->searchCriteriaBuilder
            ->addFilter(PageInterface::IDENTIFIER, $identifier)
            ->create();

        return $this->pageRepository->getList($criteria)->getTotalCount() > 0;
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
