<?php
/**
 * bin/magento madameaiguille:env:check [--serveur] [--noindex]
 *
 * À passer après chaque déploiement : sort en erreur si un réglage rend la boutique inutilisable ou dangereuse
 * (HTTP, webhook Mollie coupé, cron arrêté, fontes absentes, produit sans poids…). N'affiche aucun secret.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Console\Command;

use MadameAiguille\Theme\Model\Environment\EnvironmentChecker;
use MadameAiguille\Theme\Model\Environment\Finding;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckEnvironment extends Command
{
    private const SERVER = 'serveur';
    private const NO_INDEX = 'noindex';

    public function __construct(
        private readonly EnvironmentChecker $checker
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('madameaiguille:env:check')
            ->setDescription('Contrôle la configuration effective de l\'environnement (sans afficher de secret)')
            ->addOption(
                self::SERVER,
                null,
                InputOption::VALUE_NONE,
                'Exigences d\'un serveur exposé : production, HTTPS, webhook, cron'
            )
            ->addOption(self::NO_INDEX, null, InputOption::VALUE_NONE, 'Le site ne doit pas être indexé');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $findings = $this->checker->check(
            (bool) $input->getOption(self::SERVER),
            (bool) $input->getOption(self::NO_INDEX)
        );

        $rows = array_map(
            fn (Finding $finding): array => [$this->badge($finding->level), $finding->topic, $finding->message],
            $findings
        );
        (new Table($output))->setHeaders(['', 'Sujet', 'Constat'])->setRows($rows)->render();

        $errors = count(array_filter($findings, fn (Finding $finding): bool => $finding->isError()));
        $warnings = count(array_filter($findings, fn (Finding $finding): bool => $finding->level === Finding::WARNING));
        $output->writeln(sprintf('%d erreur(s), %d avertissement(s).', $errors, $warnings));

        return $errors === 0 ? Command::SUCCESS : Command::FAILURE;
    }

    private function badge(string $level): string
    {
        return match ($level) {
            Finding::ERROR => '<error>ERREUR</error>',
            Finding::WARNING => '<comment>ATTENTION</comment>',
            default => '<info>OK</info>',
        };
    }
}
