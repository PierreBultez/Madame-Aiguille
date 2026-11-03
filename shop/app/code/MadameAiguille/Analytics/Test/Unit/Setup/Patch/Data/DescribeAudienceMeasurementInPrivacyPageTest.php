<?php
declare(strict_types=1);

namespace MadameAiguille\Analytics\Test\Unit\Setup\Patch\Data;

use MadameAiguille\Analytics\Setup\Patch\Data\DescribeAudienceMeasurementInPrivacyPage as Patch;
use MadameAiguille\Theme\Setup\Patch\Data\FillLegalPages;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\Data\PageSearchResultsInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[AllowMockObjectsWithoutExpectations]
class DescribeAudienceMeasurementInPrivacyPageTest extends TestCase
{
    private PageRepositoryInterface&MockObject $repository;
    private LoggerInterface&MockObject $logger;
    private PageInterface&MockObject $page;
    private string $content;

    protected function setUp(): void
    {
        $this->page = $this->createMock(PageInterface::class);
        $this->page->method('getContent')->willReturnCallback(fn (): string => $this->content);
        $this->page->method('setContent')->willReturnCallback(function (string $content): PageInterface {
            $this->content = $content;
            return $this->page;
        });

        $results = $this->createStub(PageSearchResultsInterface::class);
        $results->method('getItems')->willReturn([$this->page]);
        $this->repository = $this->createMock(PageRepositoryInterface::class);
        $this->repository->method('getList')->willReturn($results);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    private function apply(): void
    {
        $builder = $this->createStub(SearchCriteriaBuilder::class);
        $builder->method('addFilter')->willReturnSelf();
        $builder->method('create')->willReturn($this->createStub(SearchCriteria::class));

        (new Patch($this->repository, $builder, $this->logger))->apply();
    }

    public function testThePlaceholderIsTheExactSentenceWrittenByFillLegalPages(): void
    {
        $source = (string) file_get_contents(
            (new \ReflectionClass(FillLegalPages::class))->getFileName()
        );

        self::assertStringContainsString(Patch::PLACEHOLDER, $source);
    }

    public function testReplacesOnlyTheBracketedSentenceAndKeepsTheRestOfThePage(): void
    {
        $this->content = "<h2>Cookies</h2>\n<p>Cookies nécessaires.</p>\n" . Patch::PLACEHOLDER . "\n<p>Version.</p>";
        $this->repository->expects(self::once())->method('save')->with($this->page);
        $this->logger->expects(self::never())->method('warning');

        $this->apply();

        self::assertSame(
            "<h2>Cookies</h2>\n<p>Cookies nécessaires.</p>\n" . Patch::TEXT . "\n<p>Version.</p>",
            $this->content
        );
        self::assertStringContainsString('MadameAiguille\Analytics\Block\OptOut', $this->content);
        self::assertStringContainsString('25 mois', $this->content);
    }

    public function testAnEditedPageIsLeftUntouchedAndTheLogSaysSo(): void
    {
        $this->content = '<h2>Cookies</h2><p>Texte réécrit par Céline.</p>';
        $this->repository->expects(self::never())->method('save');
        $this->logger->expects(self::once())->method('warning')
            ->with(self::stringContains('section Cookies non modifiée'));

        $this->apply();

        self::assertSame('<h2>Cookies</h2><p>Texte réécrit par Céline.</p>', $this->content);
    }
}
