<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\ViewModel\Cms;

use Magento\Cms\Model\Page as CmsPage;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Page implements ArgumentInterface
{
    public function __construct(private readonly CmsPage $page)
    {
    }

    public function getHeading(): string
    {
        return trim((string) ($this->page->getContentHeading() ?: $this->page->getTitle()));
    }

    public function getIdentifier(): string
    {
        return (string) $this->page->getIdentifier();
    }
}
