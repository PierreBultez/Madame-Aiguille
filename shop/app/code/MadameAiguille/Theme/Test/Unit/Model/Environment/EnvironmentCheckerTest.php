<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Test\Unit\Model\Environment;

use MadameAiguille\Theme\Model\Catalog\MissingWeightFinder;
use MadameAiguille\Theme\Model\Environment\CronHeartbeat;
use MadameAiguille\Theme\Model\Environment\EnvironmentChecker;
use MadameAiguille\Theme\Model\Environment\Finding;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Module\Manager as ModuleManager;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class EnvironmentCheckerTest extends TestCase
{
    /** Configuration d'un serveur prêt : chaque test n'en change qu'une valeur. */
    private const READY = [
        'web/unsecure/base_url' => 'https://madame-aiguille.fr/',
        'web/secure/base_url' => 'https://madame-aiguille.fr/',
        'web/secure/use_in_frontend' => '1',
        'web/secure/use_in_adminhtml' => '1',
        'design/search_engine_robots/default_robots' => 'NOINDEX,NOFOLLOW',
        'system/full_page_cache/caching_application' => '2',
        'catalog/search/engine' => 'opensearch',
        'system/smtp/disable' => '0',
        'system/smtp/transport' => 'smtp',
        'system/smtp/host' => 'smtp.example.test',
        'system/smtp/port' => '587',
        'system/smtp/password' => 'chiffré',
        'trans_email/ident_general/email' => 'contact@madame-aiguille.fr',
        'trans_email/ident_sales/email' => 'contact@madame-aiguille.fr',
        'trans_email/ident_support/email' => 'contact@madame-aiguille.fr',
        'payment/mollie_general/enabled' => '1',
        'payment/mollie_general/type' => 'test',
        'payment/mollie_general/apikey_test' => 'chiffré',
        'payment/mollie_general/use_webhooks' => 'enabled',
        'recaptcha_frontend/type_for/customer_create' => 'invisible',
        'recaptcha_frontend/type_for/contact' => 'invisible',
        'recaptcha_frontend/type_for/newsletter' => 'invisible',
        'recaptcha_frontend/type_for/customer_forgot_password' => 'invisible',
        'recaptcha_frontend/type_invisible/public_key' => 'publique',
        'recaptcha_frontend/type_invisible/private_key' => 'chiffré',
        'madameaiguille/mondial_relay/brand_code' => 'ABCDEFGH',
        'madameaiguille/cart/free_shipping_threshold' => '60',
    ];

    /**
     * @param array<string, string|null> $config
     * @param array<string, string> $deployment
     * @return list<Finding>
     */
    private function check(
        array $config = [],
        bool $onServer = true,
        bool $noIndex = true,
        array $deployment = [],
        ?int $cronMinutes = 1,
        bool $fontsPresent = true,
        int $productsWithoutWeight = 0
    ): array {
        $values = array_merge(self::READY, $config);
        $scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturnCallback(fn (string $path) => $values[$path] ?? null);

        $deployment += [
            'MAGE_MODE' => 'production',
            'cache/frontend/default/backend' => 'redis',
            'session/save' => 'redis',
        ];
        $deploymentConfig = $this->createStub(DeploymentConfig::class);
        $deploymentConfig->method('get')->willReturnCallback(fn (string $key) => $deployment[$key] ?? null);

        $moduleManager = $this->createStub(ModuleManager::class);
        $moduleManager->method('isEnabled')->willReturn(true);

        $registrar = $this->createStub(ComponentRegistrarInterface::class);
        $registrar->method('getPath')->willReturn('/theme');

        $file = $this->createStub(File::class);
        $file->method('isExists')->willReturn($fontsPresent);

        $cron = $this->createStub(CronHeartbeat::class);
        $cron->method('minutesSinceLastSuccess')->willReturn($cronMinutes);

        $weights = $this->createStub(MissingWeightFinder::class);
        $weights->method('find')->willReturn(array_fill(0, $productsWithoutWeight, ['sku' => 'X', 'name' => 'X']));

        return (new EnvironmentChecker(
            $scopeConfig,
            $deploymentConfig,
            $moduleManager,
            $registrar,
            $file,
            $cron,
            $weights
        ))->check($onServer, $noIndex);
    }

    /**
     * @param list<Finding> $findings
     * @return list<string>
     */
    private function messages(array $findings, string $level): array
    {
        return array_values(array_map(
            fn (Finding $finding): string => $finding->topic . ' — ' . $finding->message,
            array_filter($findings, fn (Finding $finding): bool => $finding->level === $level)
        ));
    }

    public function testAReadyServerHasNoErrorButKeepsTheDemonstrationAndBrandWarningsVisible(): void
    {
        $findings = $this->check(['madameaiguille/mondial_relay/brand_code' => 'BDTEST']);

        self::assertSame([], $this->messages($findings, Finding::ERROR));
        self::assertSame(
            ['Mondial Relay — code enseigne de démonstration BDTEST, la carte l\'affiche'],
            $this->messages($findings, Finding::WARNING)
        );
    }

    public function testDisabledMollieWebhooksBlockAServerButNotALocalMachine(): void
    {
        $config = ['payment/mollie_general/use_webhooks' => 'disabled'];

        self::assertStringContainsString(
            'webhooks désactivés',
            implode("\n", $this->messages($this->check($config), Finding::ERROR))
        );
        self::assertSame([], $this->messages($this->check($config, false, false), Finding::ERROR));
    }

    public function testIndexedRobotsAreAnErrorOnlyWhenTheSiteMustStayHidden(): void
    {
        $config = ['design/search_engine_robots/default_robots' => 'INDEX,FOLLOW'];

        self::assertSame(
            ['Indexation — robots = INDEX,FOLLOW : NOINDEX,NOFOLLOW attendu avant l\'ouverture'],
            $this->messages($this->check($config), Finding::ERROR)
        );
        self::assertSame([], $this->messages($this->check($config, true, false), Finding::ERROR));
    }

    public function testHttpBaseUrlsAndDeveloperModeAreErrorsOnAServer(): void
    {
        $errors = $this->messages(
            $this->check(
                ['web/secure/base_url' => 'http://madame-aiguille.fr/'],
                true,
                true,
                ['MAGE_MODE' => 'developer']
            ),
            Finding::ERROR
        );

        self::assertContains('Mode Magento — developer', $errors);
        self::assertContains('URL de base — web/secure/base_url = http://madame-aiguille.fr/ : HTTPS attendu', $errors);
    }

    public function testAProtectedFormWithoutKeysIsAnErrorBecauseEverySubmissionWouldBeRefused(): void
    {
        $errors = $this->messages(
            $this->check(['recaptcha_frontend/type_invisible/private_key' => null]),
            Finding::ERROR
        );

        self::assertContains('reCAPTCHA — contact : type invisible sans clés, le formulaire sera refusé', $errors);
    }

    public function testUnprotectedFormsAndProtectedOrderPlacementAreWarnings(): void
    {
        $warnings = $this->messages(
            $this->check([
                'recaptcha_frontend/type_for/newsletter' => null,
                'recaptcha_frontend/type_for/place_order' => 'invisible',
            ]),
            Finding::WARNING
        );

        self::assertContains('reCAPTCHA — newsletter : non protégé', $warnings);
        self::assertContains('reCAPTCHA — passage de commande protégé : non retenu, risque de gêner Mollie', $warnings);
    }

    public function testStoppedCronMissingFontsAndProductsWithoutWeightAreErrors(): void
    {
        $errors = $this->messages($this->check([], true, true, [], 45, false, 2), Finding::ERROR);

        self::assertContains('Cron — dernière tâche réussie il y a 45 min', $errors);
        self::assertContains(
            'Fontes — frontend/MadameAiguille/checkout : absentes Sentient-Regular, Sentient-Medium, Sentient-Italic',
            $errors
        );
        self::assertContains(
            'Poids — 2 produit(s) activé(s) sans poids : madameaiguille:catalog:check-weight',
            $errors
        );
    }

    public function testASenderOutsideTheShopDomainIsAWarning(): void
    {
        $warnings = $this->messages(
            $this->check([
                'web/secure/base_url' => 'https://www.madame-aiguille.fr/',
                'trans_email/ident_sales/email' => 'boutique@example.com',
            ]),
            Finding::WARNING
        );

        self::assertSame(
            ['Expéditeurs — « sales » = boutique@example.com, hors du domaine www.madame-aiguille.fr'],
            $warnings
        );
    }

    public function testAMollieModeWithoutItsKeyIsAnError(): void
    {
        $errors = $this->messages($this->check(['payment/mollie_general/type' => 'live']), Finding::ERROR);

        self::assertSame(['Mollie — mode live sans clé'], $errors);
    }
}
