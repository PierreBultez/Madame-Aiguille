<?php
/**
 * Fraîcheur du cron Magento : minutes écoulées depuis la dernière tâche terminée avec succès.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Model\Environment;

use Magento\Framework\App\ResourceConnection;

class CronHeartbeat
{
    public function __construct(
        private readonly ResourceConnection $resource
    ) {
    }

    /**
     * `finished_at` est enregistré en UTC : la comparaison se fait avec l'heure UTC de la base.
     */
    public function minutesSinceLastSuccess(): ?int
    {
        $connection = $this->resource->getConnection();
        $minutes = $connection->fetchOne(
            $connection->select()
                ->from(
                    $this->resource->getTableName('cron_schedule'),
                    ['minutes' => new \Zend_Db_Expr('TIMESTAMPDIFF(MINUTE, MAX(finished_at), UTC_TIMESTAMP())')]
                )
                ->where('status = ?', 'success')
        );

        return $minutes === null || $minutes === false ? null : (int) $minutes;
    }
}
