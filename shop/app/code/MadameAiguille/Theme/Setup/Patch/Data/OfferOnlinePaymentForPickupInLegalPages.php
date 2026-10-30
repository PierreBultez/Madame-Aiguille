<?php
/**
 * Le retrait à l'atelier se règle en ligne ou sur place (décision de Pierre du 30/10/2026).
 *
 * Remplace, dans la CGV et la page Livraison et retours déjà en place, les seules phrases exactes écrites par
 * FillLegalPages. Une phrase retouchée dans l'administration n'est jamais réécrite : elle reste à corriger à la main.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class OfferOnlinePaymentForPickupInLegalPages implements DataPatchInterface
{
    public const REPLACEMENTS = [
        '<p>Pour les commandes retirées en main propre, le paiement s’effectue <strong>sur place</strong>, par carte'
            . ' bancaire ou en espèces, au moment du retrait.</p>'
            => '<p>Pour les commandes retirées en main propre, le paiement s’effectue au choix <strong>en ligne</strong>'
            . ' lors de la commande, ou <strong>sur place</strong> au moment du retrait, par carte bancaire ou en'
            . ' espèces.</p>',
        'aux créneaux indiqués lors de la commande. Le paiement s’effectue sur place. À défaut'
            => 'aux créneaux indiqués lors de la commande. Le paiement s’effectue en ligne lors de la commande ou sur'
            . ' place au moment du retrait. À défaut',
        'et <strong>le paiement s’effectue sur place</strong>, par carte bancaire ou en espèces.'
            => 'et le paiement s’effectue au choix <strong>en ligne ou sur place</strong>, par carte bancaire ou en'
            . ' espèces.',
    ];

    private const PAGES = ['cgv', 'livraison-retours'];

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
        return [NameStripeInLegalPages::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
