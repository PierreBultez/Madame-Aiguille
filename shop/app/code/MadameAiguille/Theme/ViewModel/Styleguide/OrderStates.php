<?php
/**
 * États de commande de démonstration pour la page de contrôle /styleguide.
 *
 * Les commandes construites ici ne sont jamais persistées : elles servent
 * uniquement à faire produire au vrai ViewModel Order\Progress la phrase
 * d'avancement, la variante de badge et la frise de chaque statut, sans
 * dépendre d'une commande réelle en base.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\ViewModel\Styleguide;

use MadameAiguille\Theme\Model\Order\StatusConfig;
use MadameAiguille\Theme\ViewModel\Order\Progress;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory;

class OrderStates implements ArgumentInterface
{
    /**
     * Dates d'exemple, une par étape franchie, dans l'ordre du parcours.
     */
    private const STEP_DATES = [
        '2026-09-02 09:12:00',
        '2026-09-02 09:31:00',
        '2026-09-04 14:05:00',
        '2026-09-07 08:40:00',
        '2026-09-09 17:20:00',
    ];

    private const DELIVERY_PATH = [
        StatusConfig::PENDING_PAYMENT,
        StatusConfig::PAYMENT_RECEIVED,
        StatusConfig::PREPARING,
        StatusConfig::SHIPPED,
        StatusConfig::DELIVERED,
    ];

    private const PICKUP_PATH = [
        StatusConfig::PENDING_PAYMENT,
        StatusConfig::PAYMENT_RECEIVED,
        StatusConfig::PREPARING,
        StatusConfig::READY_FOR_PICKUP,
        StatusConfig::DELIVERED,
    ];

    private const TOTAL_EXAMPLES = [
        StatusConfig::PENDING_PAYMENT => '53,00 €',
        StatusConfig::PAYMENT_RECEIVED => '41,00 €',
        StatusConfig::PREPARING => '66,00 €',
        StatusConfig::SHIPPED => '29,00 €',
        StatusConfig::READY_FOR_PICKUP => '36,00 €',
        StatusConfig::DELIVERED => '48,00 €',
    ];

    public function __construct(
        private readonly OrderInterfaceFactory $orderFactory,
        private readonly OrderStatusHistoryInterfaceFactory $historyFactory,
        private readonly Progress $progress,
        private readonly StatusConfig $statusConfig
    ) {
    }

    /**
     * Une carte d'historique par statut du parcours, dans l'ordre de la vie d'une commande.
     *
     * @return array<int, array{
     *     increment_id: string,
     *     status: string,
     *     status_label: Phrase,
     *     badge: string,
     *     phrase: Phrase,
     *     quantity: Phrase,
     *     total: string,
     *     date: string
     * }>
     */
    public function getCards(): array
    {
        $cards = [];
        $reference = 400120;

        foreach (array_keys($this->statusConfig->getAll()) as $code) {
            $path = $code === StatusConfig::READY_FOR_PICKUP ? self::PICKUP_PATH : self::DELIVERY_PATH;
            $order = $this->buildOrder($code, $path);

            $cards[] = [
                'increment_id' => (string) ++$reference,
                'status' => $code,
                'status_label' => $this->getStatusLabel($code),
                'badge' => $this->progress->getBadgeVariant($order),
                'phrase' => $this->progress->getPhrase($order),
                'quantity' => $this->progress->getQuantityLabel($order),
                'total' => self::TOTAL_EXAMPLES[$code] ?? '48,00 €',
                'date' => '2 septembre 2026',
            ];
        }

        return $cards;
    }

    /**
     * Les deux frises possibles : parcours livraison et parcours retrait.
     *
     * @return array<int, array{
     *     title: Phrase,
     *     note: Phrase,
     *     phrase: Phrase,
     *     timeline: array<int, array{code: string, label: Phrase, state: string, date: string|null}>
     * }>
     */
    public function getTimelines(): array
    {
        $delivery = $this->buildOrder(StatusConfig::SHIPPED, self::DELIVERY_PATH);
        $pickup = $this->buildOrder(StatusConfig::READY_FOR_PICKUP, self::PICKUP_PATH);

        return [
            [
                'title' => __('Parcours livraison — étape « Expédiée »'),
                'note' => __(
                    'Les étapes franchies portent leur date réelle, prise dans l’historique de statuts de Magento.'
                ),
                'phrase' => $this->progress->getPhrase($delivery),
                'timeline' => $this->progress->getTimeline($delivery),
            ],
            [
                'title' => __('Parcours retrait — étape « Prête pour retrait »'),
                'note' => __(
                    'Dès qu’un passage par « Prête pour retrait » figure dans l’historique, la frise remplace l’étape « Expédiée » par celle du retrait.'
                ),
                'phrase' => $this->progress->getPhrase($pickup),
                'timeline' => $this->progress->getTimeline($pickup),
            ],
        ];
    }

    /**
     * Les six statuts du projet avec leur état Magento, pour le tableau de contrôle.
     *
     * @return array<int, array{code: string, label: Phrase, state: string, badge: string}>
     */
    public function getStatuses(): array
    {
        $statuses = [];

        foreach ($this->statusConfig->getAll() as $code => $definition) {
            $statuses[] = [
                'code' => $code,
                'label' => __($definition['label']),
                'state' => $definition['state'],
                'badge' => $this->progress->getBadgeVariant($this->buildOrder($code, [$code])),
            ];
        }

        return $statuses;
    }

    /**
     * Construit une commande non persistée arrêtée au statut demandé.
     *
     * @param string[] $path
     */
    private function buildOrder(string $status, array $path): OrderInterface
    {
        $order = $this->orderFactory->create();
        $order->setStatus($status);
        $order->setTotalItemCount(2);
        $order->setUpdatedAt(self::STEP_DATES[0]);
        $order->setStatusHistories($this->buildHistories($status, $path));

        return $order;
    }

    /**
     * Historique décroissant, comme celui que Magento renvoie sur une vraie commande.
     *
     * @param string[] $path
     * @return OrderStatusHistoryInterface[]
     */
    private function buildHistories(string $status, array $path): array
    {
        $reached = array_search($status, $path, true);
        if ($reached === false) {
            $reached = 0;
        }

        $histories = [];
        for ($position = 0; $position <= $reached; $position++) {
            $history = $this->historyFactory->create();
            $history->setStatus($path[$position]);
            $history->setCreatedAt(self::STEP_DATES[$position] ?? self::STEP_DATES[0]);
            $histories[] = $history;
        }

        return array_reverse($histories);
    }

    private function getStatusLabel(string $code): Phrase
    {
        $definitions = $this->statusConfig->getAll();

        return __($definitions[$code]['label'] ?? $code);
    }
}
