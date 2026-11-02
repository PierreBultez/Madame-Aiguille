<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Test\Unit\Setup\Patch\Data;

use MadameAiguille\Theme\Setup\Patch\Data\CreateWorkshopRole;
use PHPUnit\Framework\TestCase;

class CreateWorkshopRoleTest extends TestCase
{
    private const TREE = [
        ['id' => 'Magento_Backend::all'],
        ['id' => 'Magento_Backend::admin', 'children' => [
            ['id' => 'Magento_Sales::sales', 'children' => [
                ['id' => 'Magento_Sales::sales_operation', 'children' => [['id' => 'Magento_Sales::sales_order']]],
            ]],
            ['id' => 'Magento_Catalog::catalog', 'children' => [
                ['id' => 'Magento_Catalog::products', 'children' => [['id' => 'Magento_Catalog::edit_product_design']]],
            ]],
            ['id' => 'Magento_Customer::customer', 'children' => [
                ['id' => 'Magento_Customer::manage', 'children' => [
                    ['id' => 'Magento_Customer::actions', 'children' => [['id' => 'Magento_Customer::delete']]],
                ]],
                ['id' => 'Magento_Customer::group'],
            ]],
            ['id' => 'Magento_Backend::stores', 'children' => [
                ['id' => 'Magento_Backend::stores_settings', 'children' => [
                    ['id' => 'Magento_Config::config', 'children' => [
                        ['id' => 'Magento_Payment::payment'],
                        ['id' => 'MadameAiguille_Theme::config'],
                    ]],
                ]],
            ]],
            ['id' => 'Magento_Backend::system', 'children' => [['id' => 'Magento_User::acl']]],
        ]],
    ];

    public function testGrantsBranchesWithTheirParentsAndLeavesTheRestOut(): void
    {
        $allowed = CreateWorkshopRole::allowedResources(self::TREE);

        foreach ([
            'Magento_Backend::admin',
            'Magento_Sales::sales_order',
            'Magento_Catalog::products',
            'Magento_Customer::customer',
            'Magento_Customer::manage',
            'Magento_Backend::stores',
            'Magento_Config::config',
            'MadameAiguille_Theme::config',
        ] as $resource) {
            self::assertContains($resource, $allowed);
        }

        foreach ([
            'Magento_Backend::all',
            'Magento_Catalog::edit_product_design',
            'Magento_Customer::actions',
            'Magento_Customer::delete',
            'Magento_Customer::group',
            'Magento_Payment::payment',
            'Magento_Backend::system',
            'Magento_User::acl',
        ] as $resource) {
            self::assertNotContains($resource, $allowed);
        }
    }
}
