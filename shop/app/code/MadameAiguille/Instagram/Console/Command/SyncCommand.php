<?php
/**
 * bin/magento madameaiguille:instagram:sync [--renouveler-jeton] : synchronise la galerie tout de suite, par
 * exemple juste après la saisie du jeton. N'affiche jamais le jeton.
 */

declare(strict_types=1);

namespace MadameAiguille\Instagram\Console\Command;

use MadameAiguille\Instagram\Model\Api\InstagramException;
use MadameAiguille\Instagram\Model\Config;
use MadameAiguille\Instagram\Model\Feed\Synchronizer;
use MadameAiguille\Instagram\Model\Feed\TokenRenewer;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncCommand extends Command
{
    private const RENEW = 'renouveler-jeton';

    public function __construct(
        private readonly Config $config,
        private readonly Synchronizer $synchronizer,
        private readonly TokenRenewer $tokenRenewer,
        private readonly State $state
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('madameaiguille:instagram:sync')
            ->setDescription('Synchronise la galerie Instagram de l\'accueil')
            ->addOption(self::RENEW, null, InputOption::VALUE_NONE, 'Renouvelle aussi le jeton, quelle que soit sa date');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Même zone que le cron, pour les URL des visuels et les images
        try {
            $this->state->getAreaCode();
        } catch (LocalizedException) {
            $this->state->setAreaCode(Area::AREA_CRONTAB);
        }

        if (!$this->config->isEnabled() || $this->config->getAccessToken() === null) {
            $output->writeln('<comment>Synchronisation coupée ou jeton absent : Configuration › Général › Galerie Instagram.</comment>');
            return Command::FAILURE;
        }

        try {
            if ($input->getOption(self::RENEW)) {
                $this->tokenRenewer->execute(true);
                $output->writeln('Jeton renouvelé.');
            }
            $count = $this->synchronizer->execute();
        } catch (InstagramException $exception) {
            $output->writeln('<error>' . $exception->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $output->writeln(sprintf('<info>%d publication(s) affichée(s) sur l\'accueil.</info>', $count));

        return Command::SUCCESS;
    }
}
