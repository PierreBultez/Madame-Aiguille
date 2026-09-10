<?php
declare(strict_types=1);

namespace MadameAiguille\Contact\Cron;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class PurgeAttachments
{
    private const CONFIG_PATH = 'madameaiguille/contact/attachment_retention_days';

    public function __construct(
        private readonly DirectoryList $directoryList,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(): void
    {
        $root = $this->directoryList->getPath(DirectoryList::VAR_DIR) . '/madameaiguille/contact';
        if (!is_dir($root)) {
            return;
        }

        $days = max(0, (int) $this->scopeConfig->getValue(self::CONFIG_PATH, ScopeInterface::SCOPE_STORE));
        $cutoff = time() - ($days * 86400);

        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($iterator as $item) {
                if ($item->isLink()) {
                    continue;
                }
                if ($item->isFile() && $item->getMTime() <= $cutoff) {
                    unlink($item->getPathname());
                } elseif ($item->isDir() && count(scandir($item->getPathname()) ?: []) === 2) {
                    rmdir($item->getPathname());
                }
            }
        } catch (\Throwable $exception) {
            $this->logger->error('La purge des pièces jointes de contact a échoué.', ['exception' => $exception]);
        }
    }
}
