<?php
declare(strict_types=1);

namespace MadameAiguille\Checkout\Test\Unit\Plugin\Shipping;

use MadameAiguille\Checkout\Plugin\Shipping\AddPackagingWeight;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\OfflineShipping\Model\Carrier\Tablerate;
use Magento\Quote\Model\Quote\Address\RateRequest;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class AddPackagingWeightTest extends TestCase
{
    /**
     * @return array{0: float, 1: float} [poids vu par la grille, poids après le calcul]
     */
    private function collect(float $weight, string $packaging): array
    {
        $config = $this->createStub(ScopeConfigInterface::class);
        $config->method('getValue')->willReturn($packaging);
        $request = new RateRequest(['package_weight' => $weight, 'free_method_weight' => $weight]);
        $seen = null;

        (new AddPackagingWeight($config))->aroundCollectRates(
            $this->createStub(Tablerate::class),
            function (RateRequest $request) use (&$seen) {
                $seen = (float) $request->getFreeMethodWeight();
                return false;
            },
            $request
        );

        return [$seen, (float) $request->getFreeMethodWeight()];
    }

    public function testThePackagingIsAddedForTheGridThenRemoved(): void
    {
        [$seen, $after] = $this->collect(0.35, '0.2');

        self::assertEqualsWithDelta(0.55, $seen, 0.0001);
        self::assertEqualsWithDelta(0.35, $after, 0.0001);
    }

    public function testAnEmptyParcelOrNoPackagingLeavesTheWeightUnchanged(): void
    {
        self::assertSame([0.0, 0.0], $this->collect(0.0, '0.2'));
        self::assertSame([0.35, 0.35], $this->collect(0.35, ''));
    }
}
