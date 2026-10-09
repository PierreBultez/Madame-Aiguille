<?php
/**
 * bin/magento madameaiguille:catalog:check-weight
 *
 * À passer avant chaque mise en production : sort en erreur tant qu'un produit activé n'a pas de poids.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Console\Command;

use MadameAiguille\Theme\Model\Catalog\MissingWeightFinder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckWeight extends Command
{
    public function __construct(
        private readonly MissingWeightFinder $finder
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('madameaiguille:catalog:check-weight')
            ->setDescription('Liste les produits activés sans poids (frais de port faussés)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $products = $this->finder->find();
        if ($products === []) {
            $output->writeln('Tous les produits activés ont un poids.');
            return Command::SUCCESS;
        }

        $output->writeln(sprintf('<error>%d produit(s) activé(s) sans poids :</error>', count($products)));
        (new Table($output))->setHeaders(['SKU', 'Nom'])->setRows($products)->render();
        return Command::FAILURE;
    }
}
