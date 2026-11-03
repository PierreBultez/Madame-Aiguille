<?php
/**
 * Confidentialité (lot 9) : la section « Cookies » décrit la mesure d'audience Matomo, sa finalité, sa durée de
 * conservation et le droit d'opposition, avec le module d'opposition de Matomo (bloc OptOut).
 *
 * Remplace la seule phrase exacte entre crochets laissée par FillLegalPages. Si Céline a retouché ce paragraphe
 * dans l'administration, rien n'est modifié et le journal le signale : le texte est alors à saisir à la main
 * (documentation-theme.md §30). Texte à faire valider par le conseil de Céline, comme le reste de la page.
 */

declare(strict_types=1);

namespace MadameAiguille\Analytics\Setup\Patch\Data;

use MadameAiguille\Theme\Setup\Patch\Data\FillLegalPages;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;

class DescribeAudienceMeasurementInPrivacyPage implements DataPatchInterface
{
    public const PAGE = 'confidentialite';

    /** Phrase exacte de FillLegalPages, apostrophe typographique comprise. */
    public const PLACEHOLDER = '<p>[Compléter cette section si un outil de mesure d’audience ou un service tiers'
        . ' déposant des cookies est ajouté ultérieurement.]</p>';

    public const TEXT = <<<'HTML'
<h3>Mesure d’audience</h3>
<p>Pour savoir quelles pages sont consultées et améliorer le site, nous utilisons <strong>Matomo</strong>, un outil de mesure d’audience installé sur notre propre serveur (stats.madame-aiguille.fr) : les données ne sont ni transmises ni vendues à un tiers.</p>
<p>Matomo est réglé pour ne déposer <strong>aucun cookie</strong>, ne conserver qu’une adresse IP tronquée et ne situer la visite qu’au pays ou à la région. Il produit uniquement des statistiques : pages vues, provenance des visites, type d’appareil et de navigateur, recherches faites sur le site. Les données de visite sont supprimées au bout de <strong>25 mois</strong> ; seuls des totaux anonymes sont gardés au-delà.</p>
<p>Cette mesure, limitée à la production de statistiques pour notre seul usage, est dispensée de consentement selon les recommandations de la CNIL. Vous pouvez néanmoins vous y opposer :</p>
{{block class="MadameAiguille\Analytics\Block\OptOut" template="MadameAiguille_Analytics::opt-out.phtml"}}
<p>Si vous vous opposez, un unique cookie, <code>mtm_consent_removed</code>, retient votre choix sur ce navigateur ; le supprimer annule l’opposition.</p>
HTML;

    public function __construct(
        private readonly PageRepositoryInterface $repository,
        private readonly SearchCriteriaBuilder $criteriaBuilder,
        private readonly LoggerInterface $logger
    ) {
    }

    public function apply(): self
    {
        $criteria = $this->criteriaBuilder
            ->addFilter(PageInterface::IDENTIFIER, self::PAGE)
            ->create();

        $updated = 0;
        foreach ($this->repository->getList($criteria)->getItems() as $page) {
            $content = (string) $page->getContent();
            if (!str_contains($content, self::PLACEHOLDER)) {
                continue;
            }
            $page->setContent(str_replace(self::PLACEHOLDER, self::TEXT, $content));
            $this->repository->save($page);
            $updated++;
        }

        if ($updated === 0) {
            $this->logger->warning(
                'MadameAiguille_Analytics : phrase entre crochets absente de la page « confidentialite », section'
                . ' Cookies non modifiée. Saisir le texte de la mesure d\'audience dans l\'administration'
                . ' (documentation-theme.md §30).'
            );
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
