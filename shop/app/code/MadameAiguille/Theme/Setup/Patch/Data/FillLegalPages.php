<?php
/**
 * Madame Aiguille — contenus génériques des pages juridiques
 *
 * Remplace les placeholders « À rédiger » posés par CreateStaticPages par des
 * contenus génériques, construits à partir des décisions du call Céline du
 * 11/09/2026 et des arbitrages de Pierre du 09/10/2026.
 *
 * Ce ne sont PAS des documents juridiques validés : chaque page porte un
 * bandeau de brouillon, et les valeurs encore inconnues sont laissées en
 * évidence entre crochets. À faire relire avant la première vente.
 *
 * Idempotent et non destructif : une page dont le contenu n'est plus
 * exactement le placeholder initial n'est jamais touchée.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class FillLegalPages implements DataPatchInterface
{
    /**
     * Placeholders exacts écrits par CreateStaticPages : seule leur présence
     * autorise le remplacement.
     */
    private const PLACEHOLDERS = [
        'mentions-legales' => '<p>À rédiger — éditeur, hébergeur, statut, contact.</p>',
        'cgv' => '<p>À rédiger — conditions générales de vente.</p>',
        'livraison-retours' => '<p>À rédiger — transporteurs, délais, frais de port calculés au poids,'
            . ' remise en main propre, retours.</p>',
        'confidentialite' => '<p>À rédiger — données collectées, cookies, droits RGPD.</p>',
    ];

    public function __construct(
        private readonly PageRepositoryInterface $repository,
        private readonly SearchCriteriaBuilder $criteriaBuilder
    ) {
    }

    public function apply(): self
    {
        foreach (self::PLACEHOLDERS as $identifier => $placeholder) {
            $page = $this->findPage($identifier);
            if ($page === null || trim((string) $page->getContent()) !== $placeholder) {
                continue;
            }

            $page->setContent($this->getContent($identifier));
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

    private function getContent(string $identifier): string
    {
        return match ($identifier) {
            'mentions-legales' => $this->getLegalNotice(),
            'cgv' => $this->getTerms(),
            'livraison-retours' => $this->getShipping(),
            'confidentialite' => $this->getPrivacy(),
        };
    }

    private function getDraftBanner(): string
    {
        return '<p class="cms-draft"><strong>Brouillon à faire relire.</strong> Ce texte est un contenu'
            . ' générique proposé par défaut. Il doit être relu et complété avant la première vente ;'
            . ' les éléments entre crochets restent à renseigner.</p>';
    }

    private function getLegalNotice(): string
    {
        return $this->getDraftBanner() . <<<'HTML'
<h2>Éditeur du site</h2>
<dl>
<dt>Dénomination</dt><dd>Céline Bultez, entreprise individuelle, exerçant sous le nom commercial <strong>Madame Aiguille</strong></dd>
<dt>Adresse</dt><dd>35 Grande Rue, 37800 Saint-Épain, France</dd>
<dt>SIREN</dt><dd>940 760 911</dd>
<dt>SIRET</dt><dd>940 760 911 00013</dd>
<dt>Immatriculation</dt><dd>Entreprise non inscrite au Registre du commerce et des sociétés</dd>
<dt>Activité</dt><dd>Fabrication d’autres vêtements et accessoires</dd>
<dt>TVA</dt><dd>TVA non applicable, article 293 B du Code général des impôts</dd>
<dt>Contact</dt><dd>[adresse e-mail de contact] — formulaire disponible sur la page <a href="{{store url='contact'}}">Contact</a></dd>
<dt>Directrice de la publication</dt><dd>Céline Bultez</dd>
</dl>
<h2>Hébergement</h2>
<dl>
<dt>Hébergeur</dt><dd>[raison sociale de l’hébergeur]</dd>
<dt>Adresse</dt><dd>[adresse postale de l’hébergeur]</dd>
<dt>Contact</dt><dd>[téléphone ou e-mail de l’hébergeur]</dd>
</dl>
<h2>Médiation de la consommation</h2>
<p>Conformément à l’article L. 612-1 du Code de la consommation, toute cliente a le droit de recourir gratuitement à un médiateur de la consommation en vue de la résolution amiable d’un litige.</p>
<dl>
<dt>Médiateur désigné</dt><dd>[nom du médiateur à désigner]</dd>
<dt>Coordonnées</dt><dd>[adresse postale et site du médiateur]</dd>
</dl>
<p>La plateforme européenne de règlement en ligne des litiges est accessible à l’adresse <a href="https://ec.europa.eu/consumers/odr" rel="noopener noreferrer" target="_blank">ec.europa.eu/consumers/odr</a>.</p>
<h2>Propriété intellectuelle</h2>
<p>Les textes, photographies, visuels et éléments graphiques de ce site sont la propriété de Madame Aiguille, sauf mention contraire. Toute reproduction ou réutilisation, totale ou partielle, est soumise à autorisation écrite préalable.</p>
<h2>Données personnelles</h2>
<p>Le traitement des données personnelles est décrit dans la <a href="{{store url='confidentialite'}}">politique de confidentialité</a>.</p>
HTML;
    }

    private function getTerms(): string
    {
        return $this->getDraftBanner() . <<<'HTML'
<h2>1. Objet et champ d’application</h2>
<p>Les présentes conditions générales régissent la vente des créations textiles proposées sur ce site par Céline Bultez, entreprise individuelle exerçant sous le nom commercial Madame Aiguille (ci-après « l’atelier »), à toute cliente agissant en qualité de consommatrice. Passer commande vaut acceptation sans réserve des présentes conditions, dans leur version en vigueur au jour de la commande.</p>
<h2>2. Les créations</h2>
<p>Les créations sont cousues à la main, en séries limitées. Les tissus étant chinés par petites quantités, une série terminée n’est pas nécessairement rééditée. Chaque fiche produit précise les caractéristiques de la création, ses dimensions, sa composition et le nombre d’exemplaires encore disponibles.</p>
<p>Les photographies ont une valeur d’illustration. De légères différences de teinte ou de placement de motif, inhérentes au travail artisanal et au rendu des écrans, ne constituent pas un défaut de conformité.</p>
<h2>3. Prix</h2>
<p>Les prix sont indiqués en euros, toutes taxes comprises. <strong>TVA non applicable, article 293 B du Code général des impôts</strong> : l’atelier bénéficie de la franchise en base et ne facture donc pas de TVA. Les frais de livraison sont indiqués séparément avant la validation de la commande.</p>
<p>L’atelier se réserve le droit de modifier ses prix à tout moment ; les créations sont facturées au prix en vigueur au moment de la validation de la commande.</p>
<h2>4. Commande</h2>
<p>La commande se déroule en ligne, avec ou sans création de compte. Elle n’est définitive qu’après validation du paiement. Un courriel de confirmation récapitule les créations commandées, le mode de livraison et le montant total.</p>
<p>Une création peut être épuisée entre sa mise au panier et la validation de la commande : dans ce cas, la commande n’est pas enregistrée pour cet article et aucun montant n’est prélevé à ce titre.</p>
<h2>5. Paiement</h2>
<p>Le paiement en ligne s’effectue <strong>par carte bancaire</strong>, par l’intermédiaire du prestataire de paiement Mollie. Les données bancaires sont transmises directement au prestataire et ne sont jamais conservées par l’atelier.</p>
<p>Pour les commandes retirées en main propre, le paiement s’effectue <strong>sur place</strong>, par carte bancaire ou en espèces, au moment du retrait.</p>
<h2>6. Livraison</h2>
<p>Les créations sont expédiées en <strong>point relais Mondial Relay</strong>, en <strong>France, Belgique et Luxembourg</strong>. Les frais de livraison sont calculés au poids de la commande et affichés avant le paiement.</p>
<p><strong>Livraison offerte à partir de 60 €</strong> d’achat, en point relais.</p>
<p>Les commandes sont expédiées sous <strong>4 à 5 jours ouvrés</strong>, auxquels s’ajoute le délai d’acheminement du transporteur. Ces délais sont indicatifs ; un retard ne peut donner lieu à annulation ou indemnité, sauf dispositions légales contraires.</p>
<h2>7. Retrait en main propre</h2>
<p>Le retrait en main propre est proposé sur rendez-vous, aux créneaux indiqués lors de la commande. Le paiement s’effectue sur place. À défaut de retrait au rendez-vous convenu, et sans nouvelle prise de rendez-vous, la commande peut être annulée et les créations remises en vente.</p>
<h2>8. Droit de rétractation</h2>
<p>Conformément aux articles L. 221-18 et suivants du Code de la consommation, la cliente dispose d’un délai de <strong>quatorze jours</strong> à compter de la réception de sa commande pour exercer son droit de rétractation, sans avoir à motiver sa décision.</p>
<p>Pour l’exercer, il suffit d’en informer l’atelier par le <a href="{{store url='contact'}}">formulaire de contact</a> ou par courriel avant l’expiration du délai. Les créations doivent être renvoyées complètes, non utilisées et dans leur état d’origine, dans les quatorze jours suivant cette notification.</p>
<p>Les frais de retour restent à la charge de la cliente. Le remboursement intervient au plus tard quatorze jours après récupération des créations, par le même moyen de paiement que celui utilisé lors de la commande.</p>
<p><strong>Exception</strong> : conformément à l’article L. 221-28 3° du même code, le droit de rétractation ne s’applique pas aux créations confectionnées selon les spécifications de la cliente ou nettement personnalisées, notamment lorsque le tissu, les dimensions ou la finition ont été choisis par elle.</p>
<h2>9. Garanties légales</h2>
<p>Toutes les créations bénéficient de la garantie légale de conformité (articles L. 217-3 et suivants du Code de la consommation) et de la garantie contre les vices cachés (articles 1641 et suivants du Code civil). Ces garanties s’appliquent indépendamment des présentes conditions.</p>
<h2>10. Réclamations et médiation</h2>
<p>Toute réclamation peut être adressée via le <a href="{{store url='contact'}}">formulaire de contact</a>. En l’absence de solution amiable, la cliente peut saisir gratuitement le médiateur de la consommation dont les coordonnées figurent dans les <a href="{{store url='mentions-legales'}}">mentions légales</a>.</p>
<h2>11. Données personnelles</h2>
<p>Les traitements de données réalisés dans le cadre d’une commande sont décrits dans la <a href="{{store url='confidentialite'}}">politique de confidentialité</a>.</p>
<h2>12. Droit applicable</h2>
<p>Les présentes conditions sont soumises au droit français. En cas de litige, et après recherche d’une solution amiable, les tribunaux français sont compétents, sous réserve des règles protectrices applicables aux consommateurs résidant dans un autre État membre de l’Union européenne.</p>
<p><em>Version du [date de mise en ligne].</em></p>
HTML;
    }

    private function getShipping(): string
    {
        return $this->getDraftBanner() . <<<'HTML'
<p class="cms-lead">Chaque création part de l’atelier emballée avec soin. Voici comment elle vous parvient, et ce qu’il se passe si elle ne vous convient pas.</p>
<h2>Où nous livrons</h2>
<p>Nous expédions en <strong>France, en Belgique et au Luxembourg</strong>, en point relais Mondial Relay. Le point relais se choisit au moment de la commande.</p>
<h2>Frais de livraison</h2>
<p>Les frais sont calculés au <strong>poids de la commande</strong> et s’affichent avant le paiement, une fois le point relais choisi.</p>
<p><strong>La livraison est offerte à partir de 60 €</strong> d’achat.</p>
<h2>Délais</h2>
<p>Les créations étant cousues à la main, les commandes sont expédiées sous <strong>4 à 5 jours ouvrés</strong>. S’ajoute ensuite le délai d’acheminement de Mondial Relay, généralement de deux à quatre jours ouvrés.</p>
<p>Les périodes de marchés et de congés sont annoncées dans le bandeau en haut du site.</p>
<h2>Retrait en main propre</h2>
<p>Vous pouvez aussi retirer votre commande sur rendez-vous, sans frais de livraison. Le créneau se choisit au moment de la commande, parmi les disponibilités de l’atelier, et <strong>le paiement s’effectue sur place</strong>, par carte bancaire ou en espèces.</p>
<p>Si vous ne pouvez pas venir au rendez-vous convenu, prévenez-nous : sans nouvelle de votre part, la commande peut être annulée et les créations remises en vente.</p>
<h2>Changer d’avis</h2>
<p>Vous disposez de <strong>quatorze jours</strong> après réception pour changer d’avis, sans avoir à vous justifier. Prévenez-nous par le <a href="{{store url='contact'}}">formulaire de contact</a>, puis renvoyez la création complète, non utilisée et dans son état d’origine, dans les quatorze jours qui suivent.</p>
<p>Les frais de retour sont à votre charge. Le remboursement intervient dans les quatorze jours suivant la réception du colis, sur le moyen de paiement utilisé lors de la commande.</p>
<p>Les créations confectionnées à votre demande — tissu, dimensions ou finition choisis par vous — ne peuvent pas être reprises.</p>
<h2>Un souci avec votre commande ?</h2>
<p>Création abîmée pendant le transport, colis qui n’arrive pas, erreur dans l’envoi : écrivez-nous, nous trouverons une solution.</p>
<aside class="cms-callout"><h2>Une question avant de commander ?</h2><p>Écrivez-nous : nous répondons sous [délai] jours ouvrés.</p><a class="btn btn-primary" href="{{store url='contact'}}">Nous écrire</a></aside>
HTML;
    }

    private function getPrivacy(): string
    {
        return $this->getDraftBanner() . <<<'HTML'
<p class="cms-lead">Nous ne collectons que ce qui est nécessaire pour traiter vos commandes et répondre à vos messages.</p>
<h2>Qui est responsable de vos données</h2>
<p>Céline Bultez, entreprise individuelle exerçant sous le nom commercial Madame Aiguille, 35 Grande Rue, 37800 Saint-Épain, SIRET 940 760 911 00013. Contact : [adresse e-mail de contact].</p>
<h2>Ce que nous collectons, et pourquoi</h2>
<dl>
<dt>Commande et livraison</dt><dd>Nom, adresse de livraison et de facturation, adresse e-mail, téléphone, détail de la commande. Nécessaires à l’exécution du contrat de vente.</dd>
<dt>Compte client</dt><dd>Nom, adresse e-mail, mot de passe chiffré, carnet d’adresses, historique de commandes. Créé à votre demande, pour vous éviter de ressaisir vos informations.</dd>
<dt>Paiement</dt><dd>Les données bancaires sont saisies directement chez notre prestataire Mollie et ne transitent jamais par nos serveurs. Nous ne conservons que la confirmation du paiement.</dd>
<dt>Formulaire de contact</dt><dd>Nom, adresse e-mail, message, et le cas échéant une photo jointe. La photo est stockée hors de l’espace public et <strong>supprimée automatiquement au bout de 30 jours</strong>.</dd>
<dt>Lettre d’information</dt><dd>Adresse e-mail, sur la base de votre consentement, confirmé par un courriel de validation. Désinscription possible à tout moment par le lien présent dans chaque envoi.</dd>
</dl>
<h2>À qui elles sont transmises</h2>
<p>Vos données ne sont ni vendues ni cédées. Elles sont transmises uniquement aux prestataires nécessaires à l’exécution de votre commande :</p>
<ul>
<li><strong>Mollie</strong>, prestataire de paiement, pour le traitement des paiements par carte bancaire ;</li>
<li><strong>Mondial Relay</strong>, transporteur, pour l’acheminement de votre colis ;</li>
<li><strong>[hébergeur]</strong>, pour l’hébergement du site ;</li>
<li><strong>[prestataire d’envoi d’e-mails]</strong>, pour les courriels de commande et la lettre d’information.</li>
</ul>
<h2>Combien de temps nous les gardons</h2>
<dl>
<dt>Commandes et factures</dt><dd>Dix ans, conformément aux obligations comptables.</dd>
<dt>Compte client</dt><dd>Jusqu’à sa suppression à votre demande, ou après trois ans sans activité.</dd>
<dt>Messages de contact</dt><dd>Trois ans ; les photos jointes, 30 jours.</dd>
<dt>Lettre d’information</dt><dd>Jusqu’à votre désinscription.</dd>
</dl>
<h2>Vos droits</h2>
<p>Vous disposez d’un droit d’accès, de rectification, d’effacement, de limitation et d’opposition, ainsi que d’un droit à la portabilité de vos données. Pour les exercer, écrivez-nous à [adresse e-mail de contact] ou via le <a href="{{store url='contact'}}">formulaire de contact</a>.</p>
<p>Vous pouvez également introduire une réclamation auprès de la Commission nationale de l’informatique et des libertés (CNIL), <a href="https://www.cnil.fr" rel="noopener noreferrer" target="_blank">cnil.fr</a>.</p>
<h2>Cookies</h2>
<p>Ce site dépose uniquement les cookies nécessaires à son fonctionnement : session de connexion, contenu du panier, préférences d’affichage et protection des formulaires. Ils ne servent ni au profilage ni à la publicité et ne requièrent pas votre consentement préalable.</p>
<p>[Compléter cette section si un outil de mesure d’audience ou un service tiers déposant des cookies est ajouté ultérieurement.]</p>
<p><em>Version du [date de mise en ligne].</em></p>
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
