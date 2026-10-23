<?php
/**
 * Le prestataire de paiement passe de Mollie à Stripe (lot 8, compte Mollie fermé).
 *
 * Remplace, dans les pages juridiques déjà en place, les seules phrases exactes écrites par FillLegalPages.
 * Une phrase retouchée dans l'administration n'est jamais réécrite : elle reste à corriger à la main.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class NameStripeInLegalPages implements DataPatchInterface
{
    public const REPLACEMENTS = [
        'par l’intermédiaire du prestataire de paiement Mollie.'
            => 'par l’intermédiaire du prestataire de paiement Stripe.',
        'saisies directement chez notre prestataire Mollie'
            => 'saisies directement chez notre prestataire Stripe',
        '<li><strong>Mollie</strong>, prestataire de paiement,'
            => '<li><strong>Stripe</strong>, prestataire de paiement,',
    ];

    private const PAGES = ['cgv', 'confidentialite'];

    public function __construct(
        private readonly PageRepositoryInterface $repository,
        private readonly SearchCriteriaBuilder $criteriaBuilder
    ) {
    }

    public function apply(): self
    {
        $criteria = $this->criteriaBuilder
            ->addFilter(PageInterface::IDENTIFIER, self::PAGES, 'in')
            ->create();

        foreach ($this->repository->getList($criteria)->getItems() as $page) {
            $content = (string) $page->getContent();
            $updated = strtr($content, self::REPLACEMENTS);
            if ($updated !== $content) {
                $page->setContent($updated);
                $this->repository->save($page);
            }
        }

        return $this;
    }

    public static function getDependencies(): array
    {
        return [FillLegalPages::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
