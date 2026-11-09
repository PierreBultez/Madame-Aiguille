<?php
/**
 * Choisit le vrai client ou la doublure locale : la doublure n'est possible qu'en mode développeur et avec le
 * jeton sentinelle StubClient::TOKEN.
 */

declare(strict_types=1);

namespace MadameAiguille\Instagram\Model\Api;

use Magento\Framework\App\State;

class ClientRouter implements ClientInterface
{
    public function __construct(
        private readonly GraphClient $graphClient,
        private readonly StubClient $stubClient,
        private readonly State $state
    ) {
    }

    public function fetchMedia(string $token, int $limit, ?string $accountId = null): array
    {
        return $this->client($token)->fetchMedia($token, $limit, $accountId);
    }

    public function isRenewable(string $token): bool
    {
        return $this->client($token)->isRenewable($token);
    }

    public function refreshToken(string $token): array
    {
        return $this->client($token)->refreshToken($token);
    }

    private function client(string $token): ClientInterface
    {
        return $token === StubClient::TOKEN && $this->state->getMode() === State::MODE_DEVELOPER
            ? $this->stubClient
            : $this->graphClient;
    }
}
