<?php
/**
 * Contrôle d'environnement avant et après un déploiement (lot 8a).
 *
 * Lit la configuration **effective** de la vue `default` (ScopeConfig : valeurs de env.php, de la base et des
 * config.xml fusionnées), jamais `core_config_data` seul : `config:show` renvoie du vide sur une valeur par défaut.
 * Aucun secret n'est restitué : seule sa présence est vérifiée.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Model\Environment;

use MadameAiguille\Theme\Model\Catalog\MissingWeightFinder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Store\Model\ScopeInterface;

class EnvironmentChecker
{
    /** Formulaires protégés par reCAPTCHA (décision du 09/10/2026) ; le passage de commande ne l'est pas. */
    public const RECAPTCHA_FORMS = [
        'customer_create' => 'création de compte',
        'contact' => 'contact',
        'newsletter' => 'newsletter',
        'customer_forgot_password' => 'mot de passe oublié',
    ];

    /** Fontes WOFF2 originales attendues par thème (README) : non versionnées, à provisionner à chaque build. */
    public const FONTS = [
        'frontend/MadameAiguille/default' => [
            'Britney-Regular', 'Sentient-Light', 'Sentient-Regular', 'Sentient-Medium', 'Sentient-Italic',
        ],
        'frontend/MadameAiguille/checkout' => ['Sentient-Regular', 'Sentient-Medium', 'Sentient-Italic'],
    ];

    private const CRON_MAX_MINUTES = 10;

    /** @var list<Finding> */
    private array $findings = [];

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly DeploymentConfig $deploymentConfig,
        private readonly ModuleManager $moduleManager,
        private readonly ComponentRegistrarInterface $componentRegistrar,
        private readonly File $file,
        private readonly CronHeartbeat $cronHeartbeat,
        private readonly MissingWeightFinder $missingWeightFinder
    ) {
    }

    /**
     * @param bool $onServer Exigences d'un serveur exposé (production, HTTPS, webhook, cron…) ; sinon poste local
     * @param bool $noIndex  Le site ne doit pas être indexé (ouverture sur le domaine principal avant le lancement)
     * @return list<Finding>
     */
    public function check(bool $onServer, bool $noIndex): array
    {
        $this->findings = [];

        $this->checkMode($onServer);
        $this->checkBaseUrls($onServer);
        $this->checkIndexing($noIndex);
        $this->checkCaches($onServer);
        $this->checkSearchEngine();
        $this->checkCron($onServer);
        $this->checkAdminSecurity($onServer);
        $this->checkMail();
        $this->checkMollie($onServer);
        $this->checkRecaptcha($onServer);
        $this->checkShipping();
        $this->checkFonts();

        return $this->findings;
    }

    private function checkMode(bool $onServer): void
    {
        $mode = (string) ($this->deploymentConfig->get('MAGE_MODE') ?: 'default');
        if ($mode === 'production') {
            $this->ok('Mode Magento', 'production');
            return;
        }
        $this->add('Mode Magento', $onServer ? Finding::ERROR : Finding::OK, $mode);
    }

    private function checkBaseUrls(bool $onServer): void
    {
        $level = $onServer ? Finding::ERROR : Finding::WARNING;
        foreach (['web/unsecure/base_url', 'web/secure/base_url'] as $path) {
            $url = (string) $this->value($path);
            str_starts_with($url, 'https://')
                ? $this->ok('URL de base', sprintf('%s = %s', $path, $url))
                : $this->add('URL de base', $level, sprintf('%s = %s : HTTPS attendu', $path, $url));
        }
        foreach (['web/secure/use_in_frontend', 'web/secure/use_in_adminhtml'] as $path) {
            if (!$this->flag($path)) {
                $this->add('URL de base', $level, sprintf('%s désactivé : HTTPS non forcé', $path));
            }
        }
    }

    private function checkIndexing(bool $noIndex): void
    {
        $robots = strtoupper((string) $this->value('design/search_engine_robots/default_robots'));
        $indexed = !str_contains($robots, 'NOINDEX');
        if ($noIndex && $indexed) {
            $this->error('Indexation', sprintf('robots = %s : NOINDEX,NOFOLLOW attendu avant l\'ouverture', $robots));
            return;
        }
        $this->ok('Indexation', sprintf('robots = %s', $robots ?: '(vide)'));
    }

    private function checkCaches(bool $onServer): void
    {
        $level = $onServer ? Finding::WARNING : Finding::OK;
        $backend = (string) $this->deploymentConfig->get('cache/frontend/default/backend');
        $this->add(
            'Cache applicatif',
            $backend === 'redis' || $backend === 'valkey' ? Finding::OK : $level,
            $backend ?: 'fichiers'
        );

        $session = (string) $this->deploymentConfig->get('session/save');
        $this->add('Sessions', $session === 'redis' ? Finding::OK : $level, $session ?: 'files');

        $varnish = (string) $this->value('system/full_page_cache/caching_application') === '2';
        $this->add('Cache de pages', $varnish ? Finding::OK : $level, $varnish ? 'Varnish' : 'cache intégré');
    }

    private function checkSearchEngine(): void
    {
        $engine = (string) $this->value('catalog/search/engine');
        $engine === 'opensearch'
            ? $this->ok('Recherche', 'OpenSearch')
            : $this->warning('Recherche', sprintf('moteur « %s » : OpenSearch attendu', $engine));
    }

    private function checkCron(bool $onServer): void
    {
        $minutes = $this->cronHeartbeat->minutesSinceLastSuccess();
        if ($minutes !== null && $minutes <= self::CRON_MAX_MINUTES) {
            $this->ok('Cron', sprintf('dernière tâche réussie il y a %d min', $minutes));
            return;
        }
        $this->add(
            'Cron',
            $onServer ? Finding::ERROR : Finding::WARNING,
            $minutes === null ? 'aucune tâche réussie' : sprintf('dernière tâche réussie il y a %d min', $minutes)
        );
    }

    private function checkAdminSecurity(bool $onServer): void
    {
        $this->moduleManager->isEnabled('Magento_TwoFactorAuth')
            ? $this->ok('Administration', 'double authentification active')
            : $this->add(
                'Administration',
                $onServer ? Finding::WARNING : Finding::OK,
                'double authentification désactivée (Magento_TwoFactorAuth)'
            );
    }

    private function checkMail(): void
    {
        if ($this->flag('system/smtp/disable')) {
            $this->error('E-mails', 'envoi désactivé (system/smtp/disable)');
            return;
        }
        if ((string) $this->value('system/smtp/transport') !== 'smtp') {
            $this->warning('E-mails', 'transport sendmail : un SMTP authentifié est attendu');
        } elseif (!$this->value('system/smtp/host') || !$this->value('system/smtp/password')) {
            $this->error('E-mails', 'SMTP sans hôte ou sans mot de passe');
        } else {
            $this->ok('E-mails', sprintf(
                'SMTP %s:%s, identifiants renseignés',
                $this->value('system/smtp/host'),
                $this->value('system/smtp/port')
            ));
        }

        $domain = preg_match('#^https?://([^/:]+)#i', (string) $this->value('web/secure/base_url'), $host)
            ? strtolower($host[1])
            : '';
        foreach (['general', 'sales', 'support'] as $identity) {
            $email = (string) $this->value(sprintf('trans_email/ident_%s/email', $identity));
            if ($email === '') {
                $this->error('Expéditeurs', sprintf('expéditeur « %s » vide', $identity));
            } elseif ($domain !== '' && $domain !== 'localhost' && !str_ends_with($email, '@' . $this->apex($domain))) {
                $this->warning('Expéditeurs', sprintf('« %s » = %s, hors du domaine %s', $identity, $email, $domain));
            }
        }
    }

    private function checkMollie(bool $onServer): void
    {
        if (!$this->flag('payment/mollie_general/enabled')) {
            $this->warning('Mollie', 'module désactivé');
            return;
        }

        $mode = (string) $this->value('payment/mollie_general/type');
        $this->value('payment/mollie_general/apikey_' . $mode)
            ? $this->ok('Mollie', sprintf('mode %s, clé renseignée', $mode))
            : $this->error('Mollie', sprintf('mode %s sans clé', $mode));

        $webhooks = (string) $this->value('payment/mollie_general/use_webhooks');
        if ($webhooks === 'disabled') {
            // Sans webhook, Mollie 3.x ne facture jamais : la commande reste « En attente de paiement ».
            $this->add(
                'Mollie',
                $onServer ? Finding::ERROR : Finding::OK,
                'webhooks désactivés : les paiements ne passent pas la commande en « Paiement reçu »'
            );
            return;
        }
        $this->ok('Mollie', sprintf('webhooks : %s', $webhooks));
    }

    private function checkRecaptcha(bool $onServer): void
    {
        $level = $onServer ? Finding::WARNING : Finding::OK;
        foreach (self::RECAPTCHA_FORMS as $form => $label) {
            $type = (string) $this->value('recaptcha_frontend/type_for/' . $form);
            if ($type === '') {
                $this->add('reCAPTCHA', $level, sprintf('%s : non protégé', $label));
                continue;
            }
            $hasKeys = $this->value(sprintf('recaptcha_frontend/type_%s/public_key', $type))
                && $this->value(sprintf('recaptcha_frontend/type_%s/private_key', $type));
            $hasKeys
                ? $this->ok('reCAPTCHA', sprintf('%s : %s', $label, $type))
                : $this->error(
                    'reCAPTCHA',
                    sprintf('%s : type %s sans clés, le formulaire sera refusé', $label, $type)
                );
        }

        if ((string) $this->value('recaptcha_frontend/type_for/place_order') !== '') {
            $this->warning('reCAPTCHA', 'passage de commande protégé : non retenu, risque de gêner Mollie');
        }
    }

    private function checkShipping(): void
    {
        $brandCode = trim((string) $this->value('madameaiguille/mondial_relay/brand_code'));
        $brandCode === 'BDTEST' || $brandCode === ''
            ? $this->warning('Mondial Relay', 'code enseigne de démonstration BDTEST, la carte l\'affiche')
            : $this->ok('Mondial Relay', sprintf('code enseigne %s', $brandCode));

        $this->ok('Franco', sprintf(
            'point relais offert dès %s €',
            $this->value('madameaiguille/cart/free_shipping_threshold')
        ));

        $missing = count($this->missingWeightFinder->find());
        $missing === 0
            ? $this->ok('Poids', 'tous les produits activés ont un poids')
            : $this->error(
                'Poids',
                sprintf('%d produit(s) activé(s) sans poids : madameaiguille:catalog:check-weight', $missing)
            );
    }

    private function checkFonts(): void
    {
        foreach (self::FONTS as $theme => $fonts) {
            $directory = $this->componentRegistrar->getPath(ComponentRegistrar::THEME, $theme) . '/web/fonts/';
            $absent = array_values(array_filter(
                $fonts,
                fn (string $font): bool => !$this->file->isExists($directory . $font . '.woff2')
            ));
            $absent === []
                ? $this->ok('Fontes', sprintf('%s : %d WOFF2', $theme, count($fonts)))
                : $this->error('Fontes', sprintf('%s : absentes %s', $theme, implode(', ', $absent)));
        }
    }

    /** `www.madame-aiguille.fr` → `madame-aiguille.fr` : les expéditeurs appartiennent au domaine de la boutique. */
    private function apex(string $host): string
    {
        return preg_replace('/^www\./', '', $host) ?? $host;
    }

    private function value(string $path): mixed
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, 'default');
    }

    private function flag(string $path): bool
    {
        return (bool) (int) $this->value($path);
    }

    private function ok(string $topic, string $message): void
    {
        $this->add($topic, Finding::OK, $message);
    }

    private function warning(string $topic, string $message): void
    {
        $this->add($topic, Finding::WARNING, $message);
    }

    private function error(string $topic, string $message): void
    {
        $this->add($topic, Finding::ERROR, $message);
    }

    private function add(string $topic, string $level, string $message): void
    {
        $this->findings[] = new Finding($topic, $level, $message);
    }
}
