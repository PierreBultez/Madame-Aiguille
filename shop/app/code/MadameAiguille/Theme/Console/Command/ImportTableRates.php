<?php
/**
 * bin/magento madameaiguille:shipping:import-rates [--file=…] [--website=base] [--dry-run]
 *
 * Rejouable à volonté, en préproduction comme en production : la grille du site web est remplacée.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Console\Command;

use MadameAiguille\Theme\Model\Shipping\TableRateImporter;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportTableRates extends Command
{
    private const DEFAULT_FILE = 'data/tablerates-mondial-relay.csv';

    public function __construct(
        private readonly TableRateImporter $importer,
        private readonly StoreManagerInterface $storeManager,
        private readonly ComponentRegistrarInterface $componentRegistrar
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('madameaiguille:shipping:import-rates')
            ->setDescription('Remplace la grille de frais de port au poids (Mondial Relay) par le CSV versionné')
            ->addOption('file', null, InputOption::VALUE_REQUIRED, 'Chemin du CSV (par défaut : la grille du module)')
            ->addOption('website', null, InputOption::VALUE_REQUIRED, 'Code du site web', 'base')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Valide le fichier sans rien écrire');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $moduleDir = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, 'MadameAiguille_Theme');
        $file = $input->getOption('file') ?: $moduleDir . '/' . self::DEFAULT_FILE;
        $dryRun = (bool) $input->getOption('dry-run');

        try {
            $websiteId = (int) $this->storeManager->getWebsite($input->getOption('website'))->getId();
            $tiers = $this->importer->import($file, $websiteId, $dryRun);
        } catch (LocalizedException $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            $output->writeln('La grille en place n\'a pas été modifiée.');
            return Command::FAILURE;
        }

        $output->writeln(sprintf('%s : %s', $dryRun ? 'Fichier valide' : 'Grille importée', $file));
        foreach ($tiers as $country => $count) {
            $output->writeln(sprintf('  %s : %d paliers', $country, $count));
        }
        return Command::SUCCESS;
    }
}
