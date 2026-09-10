<?php
declare(strict_types=1);

namespace MadameAiguille\Contact\Setup\Patch\Data;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\Store;

class CreateContactBlocks implements DataPatchInterface
{
    public function __construct(
        private readonly BlockRepositoryInterface $repository,
        private readonly BlockInterfaceFactory $factory,
        private readonly SearchCriteriaBuilder $criteriaBuilder
    ) {
    }

    public function apply(): self
    {
        $this->createIfMissing(
            'contact_help',
            'Contact — informations pratiques',
            '<h2>Parlons de votre projet</h2><p>Une question sur une création, un tissu ou une commande ? Décrivez votre besoin avec le plus de détails possible.</p><ul><li>Indiquez la référence du produit ou du tissu.</li><li>Joignez une photo si elle aide à expliquer votre demande.</li><li>Vérifiez votre adresse email avant l’envoi.</li></ul>'
        );
        $this->createIfMissing(
            'contact_locations',
            'Contact — marchés et rendez-vous',
            '<h2>Où retrouver Madame Aiguille ?</h2><p>Les prochains marchés, congés et rendez-vous de l’atelier seront annoncés ici.</p>'
        );

        return $this;
    }

    private function createIfMissing(string $identifier, string $title, string $content): void
    {
        $criteria = $this->criteriaBuilder->addFilter('identifier', $identifier)->create();
        if ($this->repository->getList($criteria)->getTotalCount() > 0) {
            return;
        }

        $block = $this->factory->create();
        $block->setIdentifier($identifier)
            ->setTitle($title)
            ->setContent($content)
            ->setIsActive(true)
            ->setStores([Store::DEFAULT_STORE_ID]);
        $this->repository->save($block);
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
