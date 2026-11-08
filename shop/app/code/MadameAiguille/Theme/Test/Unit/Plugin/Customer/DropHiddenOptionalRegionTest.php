<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Test\Unit\Plugin\Customer;

use MadameAiguille\Theme\Plugin\Customer\DropHiddenOptionalRegion;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Directory\Helper\Data as DirectoryHelper;
use PHPUnit\Framework\TestCase;

class DropHiddenOptionalRegionTest extends TestCase
{
    public function testHiddenOptionalRegionIsDropped(): void
    {
        $empty = $this->createStub(RegionInterface::class);
        $address = $this->createMock(AddressInterface::class);
        $address->method('getCountryId')->willReturn('FR');
        $address->expects(self::once())->method('setRegionId')->with(null);
        $address->expects(self::once())->method('setRegion')->with($empty);

        $this->plugin(required: false, shown: false, emptyRegion: $empty)->beforeSave(
            $this->createStub(AddressRepositoryInterface::class),
            $address
        );
    }

    public function testRequiredRegionIsKept(): void
    {
        $address = $this->createMock(AddressInterface::class);
        $address->method('getCountryId')->willReturn('BE');
        $address->expects(self::never())->method('setRegionId');

        $this->plugin(required: true, shown: false)->beforeSave(
            $this->createStub(AddressRepositoryInterface::class),
            $address
        );
    }

    public function testOptionalRegionShownToTheCustomerIsKept(): void
    {
        $address = $this->createMock(AddressInterface::class);
        $address->method('getCountryId')->willReturn('FR');
        $address->expects(self::never())->method('setRegionId');

        $this->plugin(required: false, shown: true)->beforeSave(
            $this->createStub(AddressRepositoryInterface::class),
            $address
        );
    }

    private function plugin(bool $required, bool $shown, ?RegionInterface $emptyRegion = null): DropHiddenOptionalRegion
    {
        $helper = $this->createStub(DirectoryHelper::class);
        $helper->method('isRegionRequired')->willReturn($required);
        $helper->method('isShowNonRequiredState')->willReturn($shown);
        $factory = $this->createStub(RegionInterfaceFactory::class);
        $factory->method('create')->willReturn($emptyRegion ?? $this->createStub(RegionInterface::class));

        return new DropHiddenOptionalRegion($helper, $factory);
    }
}
