<?php
/**
 * Appel unique au modèle avec une consigne permanente, un message et un schéma JSON imposé.
 *
 * Interface fine autour du SDK, pour que tout le reste du module se teste avec un client doublé.
 */

declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Model\Claude;

use MadameAiguille\AiAssistant\Model\AssistantException;

interface ClientInterface
{
    /**
     * @param string $system Consigne permanente, mise en cache côté API
     * @param list<array<string, mixed>> $content Blocs du message (images en base64 puis texte)
     * @param array<string, mixed> $schema Schéma JSON de la réponse
     * @throws AssistantException
     */
    public function request(string $system, array $content, array $schema, int $maxTokens): Response;
}
