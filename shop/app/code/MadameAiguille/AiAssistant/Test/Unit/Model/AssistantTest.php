<?php
declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Test\Unit\Model;

use MadameAiguille\AiAssistant\Model\Assistant;
use MadameAiguille\AiAssistant\Model\AssistantException;
use MadameAiguille\AiAssistant\Model\Claude\ClientInterface;
use MadameAiguille\AiAssistant\Model\Claude\Response;
use MadameAiguille\AiAssistant\Model\DailyQuota;
use MadameAiguille\AiAssistant\Model\HtmlSanitizer;
use MadameAiguille\AiAssistant\Model\ProductContext;
use MadameAiguille\AiAssistant\Model\Prompt;
use MadameAiguille\AiAssistant\Model\UsageLogger;
use Magento\Catalog\Model\Product\Url as ProductUrl;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class AssistantTest extends TestCase
{
    /** @var array<string, mixed> */
    private array $answer = [];

    /** @var list<array{system: string, content: list<array<string, mixed>>, schema: array<string, mixed>}> */
    private array $requests = [];

    private UsageLogger&MockObject $usageLogger;
    private DailyQuota&MockObject $quota;
    private ?AssistantException $failure = null;

    private const IMAGES = [
        ['file' => '/c/t/coffret.jpg', 'mediaType' => 'image/jpeg', 'data' => 'AAAA'],
        ['file' => '/c/t/detail.jpg', 'mediaType' => 'image/png', 'data' => 'BBBB'],
    ];

    protected function setUp(): void
    {
        $this->quota = $this->createMock(DailyQuota::class);
        $this->usageLogger = $this->createMock(UsageLogger::class);
    }

    private function assistant(): Assistant
    {
        $client = $this->createStub(ClientInterface::class);
        $client->method('request')->willReturnCallback(
            function (string $system, array $content, array $schema): Response {
                $this->requests[] = ['system' => $system, 'content' => $content, 'schema' => $schema];
                if ($this->failure !== null) {
                    throw $this->failure;
                }

                return new Response($this->answer, 'claude-haiku-5-5', 3000, 800, 2500, 0);
            }
        );
        $prompt = $this->createStub(Prompt::class);
        $prompt->method('system')->willReturn('Consigne');
        $url = $this->createStub(ProductUrl::class);
        $url->method('formatUrlKey')->willReturnCallback(
            fn (string $value): string => trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($value)), '-')
        );

        return new Assistant($client, $prompt, $this->quota, $this->usageLogger, new HtmlSanitizer(), $url);
    }

    private function product(bool $isNew = false, array $form = []): ProductContext
    {
        return ProductContext::fromForm($form + [
            'name' => 'Le coffret tendresse',
            'composition' => 'Velours côtelé et coton',
            'price' => '34',
            'weight' => '0.16',
        ], $isNew, ['Le tendre coton']);
    }

    /** @return array<string, mixed> */
    private function lastRequest(): array
    {
        $content = end($this->requests)['content'];

        return json_decode((string) end($content)['text'], true);
    }

    public function testAnalysisSendsNumberedPhotosThenTheProductAndKeepsEightQuestions(): void
    {
        $this->answer = [
            'comprehension' => 'Un coffret.',
            'questions' => array_map(
                fn (int $i): array => ['id' => 'q' . $i, 'question' => 'Question ' . $i, 'type' => 'texte', 'choix' => []],
                range(1, 10)
            ),
        ];
        $this->quota->expects(self::once())->method('consume');
        $this->usageLogger->expects(self::once())->method('log')->with('analyser', 'celine', self::anything(), self::isInstanceOf(Response::class));

        $result = $this->assistant()->analyse($this->product(), self::IMAGES, 'celine');

        self::assertCount(8, $result['questions']);
        $content = $this->requests[0]['content'];
        self::assertSame(['type' => 'text', 'text' => 'Photo 1'], $content[0]);
        self::assertSame(
            ['type' => 'image', 'source' => ['type' => 'base64', 'mediaType' => 'image/jpeg', 'data' => 'AAAA']],
            $content[1]
        );
        self::assertSame('Photo 2', $content[2]['text']);
        self::assertSame('Consigne', $this->requests[0]['system']);
        self::assertArrayHasKey('questions', $this->requests[0]['schema']['properties']);
        self::assertSame([
            'etape' => 'analyse',
            'fiche' => [
                'produit_neuf' => false,
                'nom' => 'Le coffret tendresse',
                'composition' => 'Velours côtelé et coton',
                'categories' => ['Le tendre coton'],
                'prix_euros' => 34,
                'poids_grammes' => 160,
            ],
        ], $this->lastRequest());
    }

    public function testAChoiceQuestionWithoutChoicesBecomesAFreeQuestion(): void
    {
        $this->answer = ['comprehension' => '', 'questions' => [
            ['id' => 'matiere', 'question' => 'Matière ?', 'type' => 'choix', 'choix' => []],
            ['id' => '', 'question' => ' ', 'type' => 'texte', 'choix' => []],
        ]];

        self::assertSame(
            [['id' => 'matiere', 'question' => 'Matière ?', 'type' => 'texte', 'choix' => []]],
            $this->assistant()->analyse($this->product(), [], 'celine')['questions']
        );
    }

    /** @return array<string, mixed> */
    private function generation(): array
    {
        return [
            'description_courte' => '<p>Une panière et quatre cotons.</p>',
            'description_html' => '<p onclick="x">Je vous propose…</p><script>alert(1)</script><h2>Matières</h2>',
            'titre_meta' => 'Coffret tendresse | Madame Aiguille',
            'description_meta' => 'Quatre cotons et leur panière.',
            'cle_url' => 'Coffret Tendresse Velours',
            'textes_alternatifs' => [
                ['photo' => 1, 'texte' => 'Panière en velours rose vue de face.'],
                ['photo' => 9, 'texte' => 'Photo inexistante.'],
            ],
            'composition' => 'Autre composition',
            'dimensions' => '14 × 12 × 14 cm',
            'entretien' => '',
            'points_a_confirmer' => ['Température de lavage ?'],
        ];
    }

    public function testGenerationIsSanitizedAndOnlyFillsWhatItMay(): void
    {
        $this->answer = $this->generation();

        $result = $this->assistant()->generate(
            $this->product(),
            self::IMAGES,
            [['question' => 'Usage ?', 'reponse' => 'Salle de bain'], ['question' => 'Couleur ?', 'reponse' => '']],
            'Idée cadeau',
            'celine'
        );

        self::assertSame([
            'short_description' => 'Une panière et quatre cotons.',
            'description' => '<p>Je vous propose…</p><h3>Matières</h3>',
            'meta_title' => 'Coffret tendresse | Madame Aiguille',
            'meta_description' => 'Quatre cotons et leur panière.',
            'dimensions' => '14 × 12 × 14 cm',
        ], $result['fields'], 'Produit en ligne : pas de clé d\'URL ; composition déjà saisie : pas remplacée');
        self::assertSame([['file' => '/c/t/coffret.jpg', 'text' => 'Panière en velours rose vue de face.']], $result['alt']);
        self::assertSame(['Température de lavage ?'], $result['points']);

        $request = $this->lastRequest();
        self::assertSame('génération', $request['etape']);
        self::assertSame(
            [['question' => 'Usage ?', 'reponse' => 'Salle de bain'], ['question' => 'Couleur ?', 'reponse' => '(passée)']],
            $request['questions_et_reponses']
        );
        self::assertSame('Idée cadeau', $request['notes_de_celine']);
        self::assertArrayNotHasKey('resultat_precedent', $request);
    }

    public function testANewProductGetsAUrlKey(): void
    {
        $this->answer = $this->generation();

        $fields = $this->assistant()->generate($this->product(true), [], [], '', 'celine')['fields'];

        self::assertSame('coffret-tendresse-velours', $fields['url_key']);
    }

    public function testARetouchSendsThePreviousResultAndTheInstruction(): void
    {
        $this->answer = $this->generation();
        $this->usageLogger->expects(self::once())->method('log')->with('retoucher');

        $this->assistant()->generate($this->product(), [], [], '', 'celine', [
            'fields' => ['short_description' => 'Ancienne accroche', 'description' => '<p>Ancien</p>'],
            'points' => ['Ancien point'],
        ], 'plus court');

        $request = $this->lastRequest();
        self::assertSame('plus court', $request['consigne_de_retouche']);
        self::assertSame('Ancienne accroche', $request['resultat_precedent']['description_courte']);
        self::assertSame('<p>Ancien</p>', $request['resultat_precedent']['description_html']);
        self::assertSame('', $request['resultat_precedent']['cle_url']);
        self::assertSame(['Ancien point'], $request['resultat_precedent']['points_a_confirmer']);
    }

    public function testAFailedCallIsLoggedWithItsReasonAndRethrown(): void
    {
        $this->failure = new AssistantException(__('Crédit épuisé'), AssistantException::CREDIT);
        $this->usageLogger->expects(self::once())->method('log')
            ->with('analyser', 'celine', self::anything(), null, AssistantException::CREDIT);

        $this->expectException(AssistantException::class);
        $this->assistant()->analyse($this->product(), [], 'celine');
    }

    public function testTheQuotaIsCheckedBeforeAnyCall(): void
    {
        $assistant = $this->assistant();
        $this->quota->method('consume')->willThrowException(
            new AssistantException(__('Plafond'), AssistantException::QUOTA)
        );

        try {
            $assistant->analyse($this->product(), [], 'celine');
            self::fail('Le plafond aurait dû bloquer l\'appel');
        } catch (AssistantException $exception) {
            self::assertSame(AssistantException::QUOTA, $exception->reason);
        }
        self::assertSame([], $this->requests);
    }
}
