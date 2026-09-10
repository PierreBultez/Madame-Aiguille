<?php
/**
 * Présentation de l'avancement d'une commande côté cliente.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\ViewModel\Order;

use MadameAiguille\Theme\Model\Order\StatusConfig;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;

class Progress implements ArgumentInterface
{
    private const DELIVERY_STEPS = [
        StatusConfig::PENDING_PAYMENT,
        StatusConfig::PAYMENT_RECEIVED,
        StatusConfig::PREPARING,
        StatusConfig::SHIPPED,
        StatusConfig::DELIVERED,
    ];

    private const PICKUP_STEPS = [
        StatusConfig::PENDING_PAYMENT,
        StatusConfig::PAYMENT_RECEIVED,
        StatusConfig::PREPARING,
        StatusConfig::READY_FOR_PICKUP,
        StatusConfig::DELIVERED,
    ];

    public function __construct(
        private readonly TimezoneInterface $timezone,
        private readonly StatusConfig $statusConfig
    ) {
    }

    public function getPhrase(OrderInterface $order): Phrase
    {
        return match ($order->getStatus()) {
            StatusConfig::PENDING_PAYMENT => __(
                'Nous attendons votre règlement. Vos créations restent réservées.'
            ),
            StatusConfig::PAYMENT_RECEIVED => __(
                'Votre paiement a bien été reçu. Votre commande va entrer en préparation.'
            ),
            StatusConfig::PREPARING => __(
                'Je couds votre commande avec soin avant son expédition.'
            ),
            StatusConfig::SHIPPED => __(
                'Votre commande a été confiée au transporteur.'
            ),
            StatusConfig::READY_FOR_PICKUP => __(
                'Votre commande est prête à être retirée. Les modalités vous ont été envoyées par e-mail.'
            ),
            StatusConfig::DELIVERED => __(
                'Votre commande a été livrée. Merci pour votre confiance.'
            ),
            default => __('Votre commande est actuellement au statut « %1 ».', $order->getStatus()),
        };
    }

    public function getBadgeVariant(OrderInterface $order): string
    {
        return match ($order->getStatus()) {
            StatusConfig::PENDING_PAYMENT => 'pending',
            StatusConfig::PAYMENT_RECEIVED => 'paid',
            StatusConfig::PREPARING => 'preparing',
            StatusConfig::SHIPPED => 'shipped',
            StatusConfig::READY_FOR_PICKUP => 'pickup',
            StatusConfig::DELIVERED => 'delivered',
            default => 'neutral',
        };
    }

    public function getCurrentStep(OrderInterface $order): int
    {
        $steps = $this->getStepCodes($order);
        $step = array_search($order->getStatus(), $steps, true);

        return $step === false ? 0 : $step;
    }

    /**
     * @return array<int, array{code: string, label: Phrase, state: string, date: string|null}>
     */
    public function getTimeline(OrderInterface $order): array
    {
        $currentStep = $this->getCurrentStep($order);
        $dates = $this->getStatusDates($order);
        $timeline = [];

        foreach ($this->getStepCodes($order) as $position => $code) {
            $timeline[] = [
                'code' => $code,
                'label' => __($this->statusConfig->getAll()[$code]['label']),
                'state' => $position < $currentStep
                    ? 'complete'
                    : ($position === $currentStep ? 'current' : 'upcoming'),
                'date' => $dates[$code] ?? null,
            ];
        }

        return $timeline;
    }

    public function getQuantityLabel(OrderInterface $order): Phrase
    {
        $quantity = (int) round((float) $order->getTotalItemCount());

        return $quantity === 1
            ? __('1 pièce')
            : __('%1 pièces', $quantity);
    }

    /**
     * @return string[]
     */
    private function getStepCodes(OrderInterface $order): array
    {
        $statuses = array_map(
            static fn(OrderStatusHistoryInterface $history): ?string => $history->getStatus(),
            $order->getStatusHistories() ?? []
        );

        return $order->getStatus() === StatusConfig::READY_FOR_PICKUP
            || in_array(StatusConfig::READY_FOR_PICKUP, $statuses, true)
            ? self::PICKUP_STEPS
            : self::DELIVERY_STEPS;
    }

    /**
     * @return array<string, string>
     */
    private function getStatusDates(OrderInterface $order): array
    {
        $dates = [];

        foreach (array_reverse($order->getStatusHistories() ?? []) as $history) {
            $status = $history->getStatus();
            $createdAt = $history->getCreatedAt();
            if ($status === null || $createdAt === null || isset($dates[$status])) {
                continue;
            }

            $dates[$status] = $this->timezone->formatDateTime(
                $createdAt,
                \IntlDateFormatter::MEDIUM,
                \IntlDateFormatter::NONE
            );
        }

        if (!isset($dates[(string) $order->getStatus()]) && $order->getUpdatedAt()) {
            $dates[(string) $order->getStatus()] = $this->timezone->formatDateTime(
                $order->getUpdatedAt(),
                \IntlDateFormatter::MEDIUM,
                \IntlDateFormatter::NONE
            );
        }

        return $dates;
    }
}
