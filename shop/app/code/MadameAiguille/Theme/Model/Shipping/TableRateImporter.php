<?php
/**
 * Remplace la grille « Mondial Relay — Point relais » (carrier tablerate, condition au poids)
 * d'un site web par le contenu d'un CSV versionné.
 *
 * La lecture et la validation sont celles de l'import natif de l'administration ; seule différence,
 * tout se fait dans une transaction : un CSV invalide ne vide jamais la grille en place.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Model\Shipping;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\File\ReadFactory;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\Import;

class TableRateImporter
{
    public const CONDITION_NAME = 'package_weight';
    private const CONDITION_FULL_NAME = 'Weight (and above)';

    public function __construct(
        private readonly Import $import,
        private readonly ReadFactory $readFactory,
        private readonly ResourceConnection $resource,
        private readonly GridRules $gridRules
    ) {
    }

    /**
     * @return array<string, int> nombre de paliers importés par pays (code ISO 2)
     * @throws LocalizedException si le fichier ou une ligne est invalide ; la grille en place est alors conservée
     */
    public function import(string $filePath, int $websiteId, bool $dryRun = false): array
    {
        $file = $this->readFactory->create($filePath, DriverPool::FILE);
        $rows = [];
        foreach ($this->import->getData($file, $websiteId, self::CONDITION_NAME, self::CONDITION_FULL_NAME) as $bunch) {
            array_push($rows, ...$bunch);
        }
        $file->close();

        if ($this->import->hasErrors()) {
            throw new LocalizedException(__(implode(PHP_EOL, $this->import->getErrors())));
        }

        $tiers = $this->gridRules->check(array_map(
            static fn (array $row): array => [(string) $row['dest_country_id'], (float) $row['condition_value']],
            $rows
        ));

        if ($dryRun) {
            return $tiers;
        }

        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('shipping_tablerate');
        $connection->beginTransaction();
        try {
            $connection->delete($table, [
                'website_id = ?' => $websiteId,
                'condition_name = ?' => self::CONDITION_NAME,
            ]);
            $connection->insertArray($table, $this->import->getColumns(), array_map('array_values', $rows));
            $connection->commit();
        } catch (\Throwable $exception) {
            $connection->rollBack();
            throw $exception;
        }

        return $tiers;
    }
}
