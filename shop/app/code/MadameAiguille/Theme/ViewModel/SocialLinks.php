<?php
/**
 * Madame Aiguille — liens vers les réseaux sociaux (footer)
 *
 * Les URL sont saisies en back-office : Stores › Configuration › Général ›
 * Madame Aiguille › Réseaux sociaux. Un réseau sans URL n'est pas affiché.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

class SocialLinks implements ArgumentInterface
{
    private const XML_PATH_PREFIX = 'madameaiguille/social/';

    /**
     * clé de configuration => [libellé, icône Lucide]
     */
    private const NETWORKS = [
        'instagram' => ['Instagram', 'instagram'],
        'facebook' => ['Facebook', 'facebook'],
        'tiktok' => ['TikTok', 'tiktok'],
    ];

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @return array<int, array{name: string, icon: string, url: string}>
     */
    public function getLinks(): array
    {
        $links = [];

        foreach (self::NETWORKS as $key => [$name, $icon]) {
            $url = trim((string) $this->scopeConfig->getValue(
                self::XML_PATH_PREFIX . $key . '_url',
                ScopeInterface::SCOPE_STORE
            ));

            if ($url === '') {
                continue;
            }

            $links[] = ['name' => $name, 'icon' => $icon, 'url' => $url];
        }

        return $links;
    }
}
