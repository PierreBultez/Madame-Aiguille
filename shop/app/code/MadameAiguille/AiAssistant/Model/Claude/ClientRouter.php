<?php
/**
 * Choisit le client : le SDK, sauf sur un poste en mode développeur dont la clé est la sentinelle
 * `StubClient::KEY` (« doublure-locale »). En mode production, le client doublé est impossible : la sentinelle y
 * serait simplement refusée par l'API.
 */

declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Model\Claude;

use MadameAiguille\AiAssistant\Model\Config;
use Magento\Framework\App\State;

class ClientRouter implements ClientInterface
{
    public function __construct(
        private readonly SdkClient $sdkClient,
        private readonly StubClient $stubClient,
        private readonly Config $config,
        private readonly State $state
    ) {
    }

    public function request(string $system, array $content, array $schema, int $maxTokens): Response
    {
        $client = $this->state->getMode() === State::MODE_DEVELOPER && $this->config->getApiKey() === StubClient::KEY
            ? $this->stubClient
            : $this->sdkClient;

        return $client->request($system, $content, $schema, $maxTokens);
    }
}
