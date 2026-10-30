<?php
/**
 * Confidentialité : Klarna, l'un des cinq moyens de paiement retenus le 30/10/2026, reçoit les données de la
 * cliente qui le choisit et les traite pour son propre service. Stripe traite les autres paiements en ligne.
 *
 * Remplace les seules phrases exactes écrites par FillLegalPages. Une phrase retouchée dans l'administration
 * n'est jamais réécrite : elle reste à corriger à la main. Texte à faire valider par le conseil de Céline.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class NameKlarnaInPrivacyPage implements DataPatchInterface
{
    public const REPLACEMENTS = [
        'chez notre prestataire Stripe et ne transitent jamais par nos serveurs. Nous ne conservons que la'
            . ' confirmation du paiement.</dd>'
            => 'chez notre prestataire Stripe et ne transitent jamais par nos serveurs. Si vous choisissez Klarna, les'
            . ' informations nécessaires à son service (identité, coordonnées, montant de la commande) lui sont'
            . ' transmises, et Klarna peut vérifier votre éligibilité avant d’accepter le paiement. Nous ne conservons'
            . ' que la confirmation du paiement.</dd>',
        '<li><strong>Stripe</strong>, prestataire de paiement, pour le traitement des paiements par carte bancaire'
            . ' ;</li>'
            => '<li><strong>Stripe</strong>, prestataire de paiement, pour le traitement des paiements en ligne (carte'
            . ' bancaire, Apple Pay, Google Pay, Bancontact) ;</li>' . "\n"
            . '<li><strong>Klarna</strong>, lorsque vous choisissez ce moyen de paiement, pour le paiement en plusieurs'
            . ' fois ou différé : Klarna traite alors vos données pour son propre service, selon sa propre politique'
            . ' de confidentialité ;</li>',
    ];

    public function __construct(
        private readonly PageRepositoryInterface $repository,
        private readonly SearchCriteriaBuilder $criteriaBuilder
    ) {
    }

    public function apply(): self
    {
        $criteria = $this->criteriaBuilder
            ->addFilter(PageInterface::IDENTIFIER, 'confidentialite')
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
        return [ListPaymentMethodsInTerms::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
