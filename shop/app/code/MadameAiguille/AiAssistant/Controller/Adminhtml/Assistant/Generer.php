<?php
/**
 * Étape 3 : rédaction à partir de la fiche, des photos et des réponses ; avec `previous` et `instruction`, une
 * retouche (« plus court », « plus chaleureux », « régénérer », consigne libre).
 */

declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Controller\Adminhtml\Assistant;

class Generer extends AbstractAction
{
    protected function handle(array $payload): array
    {
        $answers = [];
        foreach (is_array($payload['answers'] ?? null) ? $payload['answers'] : [] as $answer) {
            if (is_array($answer) && is_scalar($answer['question'] ?? null)) {
                $answers[] = [
                    'question' => mb_substr((string) $answer['question'], 0, 500),
                    'reponse' => mb_substr(is_scalar($answer['reponse'] ?? null) ? (string) $answer['reponse'] : '', 0, 2000),
                ];
            }
        }
        $previous = is_array($payload['previous'] ?? null) ? $payload['previous'] : null;

        return $this->assistant->generate(
            $this->product($payload),
            $this->images($payload),
            array_slice($answers, 0, 12),
            mb_substr(is_scalar($payload['notes'] ?? null) ? (string) $payload['notes'] : '', 0, 3000),
            $this->user(),
            $previous,
            mb_substr(is_scalar($payload['instruction'] ?? null) ? (string) $payload['instruction'] : '', 0, 500)
        );
    }
}
