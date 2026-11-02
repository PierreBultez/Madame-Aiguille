<?php

declare(strict_types=1);

namespace MadameAiguille\Theme\Test\Unit\Plugin\Catalog;

use MadameAiguille\Theme\Plugin\Catalog\CategoryMetaDescriptionFallback;
use Magento\Catalog\Block\Category\View;
use Magento\Catalog\Model\Category;
use Magento\Framework\View\Page\Config as PageConfig;
use PHPUnit\Framework\TestCase;

class CategoryMetaDescriptionFallbackTest extends TestCase
{
    public function testKeepsMetaDescriptionEnteredInAdmin(): void
    {
        $pageConfig = $this->createMock(PageConfig::class);
        $pageConfig->expects($this->never())->method('setDescription');

        (new CategoryMetaDescriptionFallback($pageConfig))
            ->afterSetLayout($this->block('Petits sacs', '', 'Saisie par Céline'), null);
    }

    public function testUsesCategoryNameWithoutDescription(): void
    {
        $pageConfig = $this->createMock(PageConfig::class);
        $pageConfig->expects($this->once())->method('setDescription')->with(
            'Petits sacs : créations en tissu cousues main à Saint-Épain, en séries limitées de 5 à 10 pièces.'
        );

        (new CategoryMetaDescriptionFallback($pageConfig))->afterSetLayout($this->block('Petits sacs', '', ''), null);
    }

    public function testStripsAndShortensDescription(): void
    {
        $plugin = new CategoryMetaDescriptionFallback($this->createMock(PageConfig::class));

        $this->assertSame('Des trousses <douces>.', $plugin->compose('X', "<p>Des trousses\n &lt;douces&gt;.</p>"));

        $long = $plugin->compose('X', str_repeat('mot ', 60));
        $this->assertLessThanOrEqual(CategoryMetaDescriptionFallback::MAX_LENGTH, mb_strlen($long));
        $this->assertStringEndsWith('mot…', $long);
    }

    private function block(string $name, string $description, string $meta): View
    {
        $category = $this->createMock(Category::class);
        $category->method('getName')->willReturn($name);
        $category->method('getData')->willReturnMap([
            ['description', null, $description],
            ['meta_description', null, $meta],
        ]);

        $block = $this->createMock(View::class);
        $block->method('getCurrentCategory')->willReturn($category);

        return $block;
    }
}
