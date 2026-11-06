<?php
/**
 * Plafond d'appels par jour, pour toute la boutique (garde-fou de coût, en plus de la limite de dépenses posée sur le
 * compte Anthropic). Le compteur vit dans la table `flag` ; il repart de zéro à minuit, heure de la boutique.
 *
 * Un appel est compté dès qu'il part, même s'il échoue : l'API facture aussi une réponse coupée.
 */

declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Model;

use Magento\Framework\FlagManager;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class DailyQuota
{
    public const FLAG_CODE = 'madameaiguille_ai_daily_calls';

    public function __construct(
        private readonly FlagManager $flagManager,
        private readonly TimezoneInterface $timezone,
        private readonly Config $config
    ) {
    }

    /**
     * @throws AssistantException quand le plafond du jour est atteint
     */
    public function consume(): void
    {
        $used = $this->used();
        $limit = $this->config->getDailyLimit();
        if ($used >= $limit) {
            throw new AssistantException(
                __('Le plafond quotidien de l\'assistant est atteint (%1 appels). Il reprend demain.', $limit),
                AssistantException::QUOTA
            );
        }
        $this->flagManager->saveFlag(self::FLAG_CODE, ['date' => $this->today(), 'count' => $used + 1]);
    }

    public function remaining(): int
    {
        return max(0, $this->config->getDailyLimit() - $this->used());
    }

    private function used(): int
    {
        $flag = $this->flagManager->getFlagData(self::FLAG_CODE);

        return is_array($flag) && ($flag['date'] ?? null) === $this->today() ? (int) ($flag['count'] ?? 0) : 0;
    }

    private function today(): string
    {
        return $this->timezone->date()->format('Y-m-d');
    }
}
