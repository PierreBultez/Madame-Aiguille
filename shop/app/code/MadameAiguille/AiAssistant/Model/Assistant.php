<?php
/**
 * Les deux appels de l'assistant : analyser la fiche (questions à Céline), puis générer les textes (retouches
 * comprises). Chaque appel est **sans état** : tout ce dont le modèle a besoin part dans la requête.
 *
 * La réponse du modèle n'est jamais renvoyée telle quelle : la description longue passe par le nettoyeur HTML, la clé
 * d'URL n'est gardée que pour un produit neuf, et `composition`, `dimensions`, `entretien` seulement si le champ est
 * vide dans la fiche.
 */

declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Model;

use MadameAiguille\AiAssistant\Model\Claude\ClientInterface;
use Magento\Catalog\Model\Product\Url as ProductUrl;

class Assistant
{
    public const MAX_QUESTIONS = 8;

    private const ANALYSIS_MAX_TOKENS = 8000;
    private const GENERATION_MAX_TOKENS = 16000;

    /** Champs proposés au navigateur, dans l'ordre du panneau. */
    public const FIELDS = ['short_description', 'description', 'meta_title', 'meta_description', 'url_key',
        'composition', 'dimensions', 'entretien'];

    public function __construct(
        private readonly ClientInterface $client,
        private readonly Prompt $prompt,
        private readonly DailyQuota $quota,
        private readonly UsageLogger $usageLogger,
        private readonly HtmlSanitizer $sanitizer,
        private readonly ProductUrl $productUrl
    ) {
    }

    /**
     * @param list<array{file: string, mediaType: string, data: string}> $images
     * @return array{comprehension: string, questions: list<array{id: string, question: string, type: string, choix: list<string>}>}
     * @throws AssistantException
     */
    public function analyse(ProductContext $product, array $images, string $user): array
    {
        $data = $this->call('analyser', $user, $this->content($images, [
            'etape' => 'analyse',
            'fiche' => $product->facts,
        ]), Schema::analysis(), self::ANALYSIS_MAX_TOKENS);

        $questions = [];
        foreach (is_array($data['questions'] ?? null) ? $data['questions'] : [] as $index => $question) {
            $text = trim((string) ($question['question'] ?? ''));
            if ($text === '') {
                continue;
            }
            $type = in_array($question['type'] ?? '', Schema::QUESTION_TYPES, true) ? $question['type'] : 'texte';
            $choices = $type === 'choix' ? $this->strings($question['choix'] ?? []) : [];
            $questions[] = [
                'id' => (string) ($question['id'] ?? '') ?: 'q' . $index,
                'question' => $text,
                'type' => $type === 'choix' && $choices === [] ? 'texte' : $type,
                'choix' => $choices,
            ];
        }

        return [
            'comprehension' => trim((string) ($data['comprehension'] ?? '')),
            'questions' => array_slice($questions, 0, self::MAX_QUESTIONS),
        ];
    }

    /**
     * @param list<array{file: string, mediaType: string, data: string}> $images
     * @param list<array{question: string, reponse: string}> $answers Réponse vide : question passée
     * @param array<string, mixed>|null $previous Résultat précédent, pour une retouche
     * @return array{fields: array<string, string>, alt: list<array{file: string, text: string}>, points: list<string>}
     * @throws AssistantException
     */
    public function generate(
        ProductContext $product,
        array $images,
        array $answers,
        string $notes,
        string $user,
        ?array $previous = null,
        string $instruction = ''
    ): array {
        $request = [
            'etape' => 'génération',
            'fiche' => $product->facts,
            'questions_et_reponses' => array_map(
                fn (array $answer): array => [
                    'question' => (string) $answer['question'],
                    'reponse' => trim((string) $answer['reponse']) === '' ? '(passée)' : (string) $answer['reponse'],
                ],
                $answers
            ),
        ];
        if (trim($notes) !== '') {
            $request['notes_de_celine'] = trim($notes);
        }
        if ($previous !== null) {
            $request['resultat_precedent'] = $this->previousForModel($previous);
            $request['consigne_de_retouche'] = trim($instruction) ?: 'régénérer';
        }

        $data = $this->call(
            $previous === null ? 'generer' : 'retoucher',
            $user,
            $this->content($images, $request),
            Schema::generation(),
            self::GENERATION_MAX_TOKENS
        );

        return $this->readGeneration($data, $product, $images);
    }

    /**
     * @param array<string, mixed> $data
     * @param list<array{file: string, mediaType: string, data: string}> $images
     * @return array{fields: array<string, string>, alt: list<array{file: string, text: string}>, points: list<string>}
     */
    private function readGeneration(array $data, ProductContext $product, array $images): array
    {
        $plain = fn (string $key): string => trim((string) preg_replace(
            '/\s+/u',
            ' ',
            html_entity_decode(strip_tags((string) ($data[$key] ?? '')))
        ));

        $fields = [
            'short_description' => $plain('description_courte'),
            'description' => $this->sanitizer->sanitize((string) ($data['description_html'] ?? '')),
            'meta_title' => $plain('titre_meta'),
            'meta_description' => $plain('description_meta'),
            'url_key' => $product->isNew ? (string) $this->productUrl->formatUrlKey($plain('cle_url')) : '',
        ];
        foreach (ProductContext::FILL_IF_EMPTY as $code) {
            $fields[$code] = $product->isEmpty($code) ? $plain($code) : '';
        }

        $alt = [];
        foreach (is_array($data['textes_alternatifs'] ?? null) ? $data['textes_alternatifs'] : [] as $item) {
            $index = (int) ($item['photo'] ?? 0) - 1;
            $text = trim(strip_tags((string) ($item['texte'] ?? '')));
            if (isset($images[$index]) && $text !== '') {
                $alt[$images[$index]['file']] = ['file' => $images[$index]['file'], 'text' => $text];
            }
        }

        return [
            'fields' => array_filter($fields, fn (string $value): bool => $value !== ''),
            'alt' => array_values($alt),
            'points' => $this->strings($data['points_a_confirmer'] ?? []),
        ];
    }

    /**
     * Résultat renvoyé par le navigateur pour une retouche, remis dans le vocabulaire du schéma de génération.
     *
     * @param array<string, mixed> $previous
     * @return array<string, mixed>
     */
    private function previousForModel(array $previous): array
    {
        $names = [
            'short_description' => 'description_courte',
            'description' => 'description_html',
            'meta_title' => 'titre_meta',
            'meta_description' => 'description_meta',
            'url_key' => 'cle_url',
            'composition' => 'composition',
            'dimensions' => 'dimensions',
            'entretien' => 'entretien',
        ];
        $fields = is_array($previous['fields'] ?? null) ? $previous['fields'] : [];
        $result = [];
        foreach ($names as $code => $name) {
            $result[$name] = mb_substr(is_scalar($fields[$code] ?? null) ? (string) $fields[$code] : '', 0, 6000);
        }
        $result['points_a_confirmer'] = array_slice($this->strings($previous['points'] ?? []), 0, 20);

        return $result;
    }

    /**
     * Photos numérotées d'abord, puis la demande en JSON : la consigne permanente reste en tête, seule en cache.
     *
     * @param list<array{file: string, mediaType: string, data: string}> $images
     * @param array<string, mixed> $request
     * @return list<array<string, mixed>>
     */
    private function content(array $images, array $request): array
    {
        $content = [];
        foreach ($images as $index => $image) {
            $content[] = ['type' => 'text', 'text' => sprintf('Photo %d', $index + 1)];
            $content[] = [
                'type' => 'image',
                'source' => ['type' => 'base64', 'mediaType' => $image['mediaType'], 'data' => $image['data']],
            ];
        }
        $content[] = [
            'type' => 'text',
            'text' => (string) json_encode($request, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];

        return $content;
    }

    /**
     * @param list<array<string, mixed>> $content
     * @param array<string, mixed> $schema
     * @return array<string, mixed>
     * @throws AssistantException
     */
    private function call(string $action, string $user, array $content, array $schema, int $maxTokens): array
    {
        $this->quota->consume();
        $start = hrtime(true);
        $response = null;
        try {
            $response = $this->client->request($this->prompt->system(), $content, $schema, $maxTokens);
        } catch (AssistantException $exception) {
            $this->usageLogger->log($action, $user, $this->elapsed($start), null, $exception->reason);
            throw $exception;
        }
        $this->usageLogger->log($action, $user, $this->elapsed($start), $response);

        return $response->data;
    }

    private function elapsed(int $start): int
    {
        return (int) round((hrtime(true) - $start) / 1e6);
    }

    /**
     * @return list<string>
     */
    private function strings(mixed $values): array
    {
        return is_array($values)
            ? array_values(array_filter(array_map(
                fn (mixed $value): string => is_scalar($value) ? trim(strip_tags((string) $value)) : '',
                $values
            )))
            : [];
    }
}
