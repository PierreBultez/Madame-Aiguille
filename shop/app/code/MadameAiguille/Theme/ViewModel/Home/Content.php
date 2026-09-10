<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\ViewModel\Home;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\GetBlockByIdentifierInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class Content implements ArgumentInterface
{
    public function __construct(
        private readonly GetBlockByIdentifierInterface $getBlock,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    public function getActiveBlock(string $identifier): ?BlockInterface
    {
        try {
            $block = $this->getBlock->execute($identifier, (int) $this->storeManager->getStore()->getId());
            return $block->isActive() ? $block : null;
        } catch (NoSuchEntityException) {
            return null;
        }
    }
}
