<?php
/**
 * CGV : les cinq moyens de paiement en ligne retenus le 30/10/2026 (carte bancaire, Apple Pay, Google Pay,
 * Bancontact, Klarna), et la commande en retrait payée sur place, confirmée avant tout paiement.
 *
 * Remplace les seules phrases exactes écrites par FillLegalPages et ses correctifs. Une phrase retouchée dans
 * l'administration n'est jamais réécrite : elle reste à corriger à la main. Texte à faire valider par le conseil
 * de Céline, comme le reste de la CGV.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class ListPaymentMethodsInTerms implements DataPatchInterface
{
    public const REPLACEMENTS = [
        'Elle n’est définitive qu’après validation du paiement.'
            => 'Elle n’est définitive qu’après validation du paiement en ligne ou, pour un retrait payé sur place,'
            . ' dès son enregistrement.',
        '<p>Le paiement en ligne s’effectue <strong>par carte bancaire</strong>, par l’intermédiaire du prestataire de'
            . ' paiement Stripe. Les données bancaires sont transmises directement au prestataire et ne sont jamais'
            . ' conservées par l’atelier.</p>'
            => self::PAYMENT_METHODS,
    ];

    public const PAYMENT_METHODS = '<p>Le paiement en ligne s’effectue par l’intermédiaire du prestataire de paiement'
        . ' <strong>Stripe</strong>, au choix :</p>' . "\n"
        . '<ul>' . "\n"
        . '<li>par <strong>carte bancaire</strong> (CB, Visa, Mastercard notamment) ;</li>' . "\n"
        . '<li>avec <strong>Apple Pay</strong> ou <strong>Google Pay</strong>, depuis un appareil compatible ;</li>'
        . "\n"
        . '<li>avec <strong>Bancontact</strong> ;</li>' . "\n"
        . '<li>avec <strong>Klarna</strong>, qui permet, selon les offres proposées au moment du paiement et sous'
        . ' réserve de son accord, de régler en plusieurs fois ou plus tard. Ce service est fourni par Klarna, selon'
        . ' ses propres conditions, présentées avant toute validation ; l’atelier reçoit le paiement de la commande en'
        . ' une fois.</li>' . "\n"
        . '</ul>' . "\n"
        . '<p>Les moyens proposés peuvent varier selon l’appareil, le pays et le montant de la commande. Le paiement'
        . ' en ligne est encaissé à la validation de la commande. Les données bancaires sont transmises directement au'
        . ' prestataire et ne sont jamais conservées par l’atelier.</p>';

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
        return [OfferOnlinePaymentForPickupInLegalPages::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
