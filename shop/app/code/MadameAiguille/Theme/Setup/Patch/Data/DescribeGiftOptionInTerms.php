<?php
/**
 * CGV : l'option « Emballage et message cadeau » du lot 11 (libellé validé par Céline le 08/11/2026), décrite dans
 * l'article « Commande » : ce qu'elle comprend, le message recopié à la main, son remboursement.
 *
 * Le paragraphe s'insère après la phrase exacte écrite par FillLegalPages sur les créations épuisées, une seule fois.
 * Si cette phrase a été retouchée dans l'administration, rien n'est écrit : le paragraphe reste à ajouter à la main.
 * Texte à faire valider par le conseil de Céline, comme le reste de la CGV.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class DescribeGiftOptionInTerms implements DataPatchInterface
{
    public const ANCHOR = '<p>Une création peut être épuisée entre sa mise au panier et la validation de la commande :'
        . ' dans ce cas, la commande n’est pas enregistrée pour cet article et aucun montant n’est prélevé à ce'
        . ' titre.</p>';

    public const CLAUSE = '<p><strong>Emballage et message cadeau.</strong> Au moment de choisir la livraison, la'
        . ' cliente peut ajouter l’option « Emballage et message cadeau », au prix indiqué à côté de l’option. Elle'
        . ' comprend l’emballage de la commande et une carte sur laquelle l’atelier recopie à la main le message saisi'
        . ' par la cliente : expéditeur, destinataire et message, dans la limite de caractères indiquée. Le message est'
        . ' recopié tel qu’il a été saisi. Le prix de l’option est remboursé lorsque la commande est annulée ou'
        . ' retournée en totalité ; il ne l’est pas en cas de retour partiel.</p>';

    public function __construct(
        private readonly PageRepositoryInterface $repository,
        private readonly SearchCriteriaBuilder $criteriaBuilder
    ) {
    }

    public function apply(): self
    {
        $criteria = $this->criteriaBuilder
            ->addFilter(PageInterface::IDENTIFIER, 'cgv')
            ->create();

        foreach ($this->repository->getList($criteria)->getItems() as $page) {
            $content = (string) $page->getContent();
            $updated = self::withClause($content);
            if ($updated !== $content) {
                $page->setContent($updated);
                $this->repository->save($page);
            }
        }

        return $this;
    }

    /**
     * Le contenu de la CGV avec le paragraphe de l'option, ou inchangé si l'ancre manque ou si l'option y figure déjà.
     */
    public static function withClause(string $content): string
    {
        $position = strpos($content, self::ANCHOR);
        if ($position === false || str_contains($content, 'Emballage et message cadeau')) {
            return $content;
        }
        $end = $position + strlen(self::ANCHOR);

        return substr($content, 0, $end) . "\n" . self::CLAUSE . substr($content, $end);
    }

    public static function getDependencies(): array
    {
        return [ListPaymentMethodsInTerms::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
