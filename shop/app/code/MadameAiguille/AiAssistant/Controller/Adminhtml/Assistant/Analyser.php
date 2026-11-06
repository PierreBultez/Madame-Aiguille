<?php
/**
 * Étape 1 : le modèle lit la fiche et ses photos, puis renvoie ce qu'il a compris et les questions qui manquent.
 */

declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Controller\Adminhtml\Assistant;

class Analyser extends AbstractAction
{
    protected function handle(array $payload): array
    {
        return $this->assistant->analyse($this->product($payload), $this->images($payload), $this->user());
    }
}
