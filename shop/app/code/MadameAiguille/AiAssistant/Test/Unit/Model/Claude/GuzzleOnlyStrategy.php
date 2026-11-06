<?php
declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Test\Unit\Model\Claude;

use Http\Discovery\Strategy\DiscoveryStrategy;

/**
 * Seules les classes Guzzle sont proposées à la découverte PSR-17 et PSR-18 du SDK, comme dans Magento réel
 * (l'autoloader des tests fabriquerait sinon une fabrique Phalcon fictive).
 */
class GuzzleOnlyStrategy implements DiscoveryStrategy
{
    /**
     * @param string $type
     * @return list<array{class: class-string, condition: class-string}>
     */
    public static function getCandidates($type): array
    {
        $class = str_starts_with($type, 'Psr\\Http\\Client') ? \GuzzleHttp\Client::class : \GuzzleHttp\Psr7\HttpFactory::class;

        return [['class' => $class, 'condition' => $class]];
    }
}
