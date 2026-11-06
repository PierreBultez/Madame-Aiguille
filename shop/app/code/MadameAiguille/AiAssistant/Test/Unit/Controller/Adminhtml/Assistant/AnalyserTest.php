<?php
declare(strict_types=1);

namespace MadameAiguille\AiAssistant\Test\Unit\Controller\Adminhtml\Assistant;

use MadameAiguille\AiAssistant\Controller\Adminhtml\Assistant\Analyser;
use MadameAiguille\AiAssistant\Model\Assistant;
use MadameAiguille\AiAssistant\Model\AssistantException;
use MadameAiguille\AiAssistant\Model\Config;
use MadameAiguille\AiAssistant\Model\GalleryImages;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\User\Model\User;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[AllowMockObjectsWithoutExpectations]
class AnalyserTest extends TestCase
{
    /** @var array<string, mixed> */
    private array $data = [];

    private LoggerInterface&MockObject $logger;
    private Assistant&MockObject $assistant;

    private function execute(bool $ready = true): array
    {
        $request = $this->createStub(RequestInterface::class);
        $request->method('getParam')->willReturn('{"product": {"name": "Coffret"}, "images": []}');
        $user = $this->createConfiguredStub(User::class, ['getUserName' => 'celine']);
        $auth = $this->createConfiguredStub(Auth::class, ['getUser' => $user]);
        $context = $this->createConfiguredStub(Context::class, ['getRequest' => $request, 'getAuth' => $auth]);

        $json = $this->createStub(Json::class);
        $json->method('setData')->willReturnCallback(function (array $data) use ($json): Json {
            $this->data = $data;
            return $json;
        });
        $jsonFactory = $this->createConfiguredStub(JsonFactory::class, ['create' => $json]);
        $config = $this->createConfiguredStub(Config::class, ['isReady' => $ready]);
        $images = $this->createConfiguredStub(GalleryImages::class, ['prepare' => []]);

        (new Analyser(
            $context,
            $config,
            $this->assistant,
            $images,
            $this->createStub(CollectionFactory::class),
            $jsonFactory,
            $this->logger
        ))->execute();

        return $this->data;
    }

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->assistant = $this->createMock(Assistant::class);
    }

    public function testAnUnexpectedErrorIsLoggedWithoutItsMessageAndTheFormIsUntouched(): void
    {
        $this->assistant->method('analyse')->willThrowException(
            new \RuntimeException('"Bearer cle-factice-de-test" is not valid header value')
        );
        $this->logger->expects(self::once())->method('error')->with(
            self::logicalAnd(
                self::stringContains('Assistant IA : erreur inattendue RuntimeException ('),
                self::logicalNot(self::stringContains('cle-factice-de-test'))
            ),
            []
        );

        self::assertSame(
            ['ok' => false, 'message' => 'Une erreur inattendue est survenue. Votre fiche n\'a pas été modifiée.'],
            $this->execute()
        );
    }

    public function testAnAssistantErrorIsShownToCelineAndNotLogged(): void
    {
        $this->assistant->method('analyse')->willThrowException(
            new AssistantException(__('Le service de rédaction est surchargé.'), AssistantException::OVERLOADED)
        );
        $this->logger->expects(self::never())->method('error');

        self::assertSame(['ok' => false, 'message' => 'Le service de rédaction est surchargé.'], $this->execute());
    }

    public function testNothingIsCalledWhenTheAssistantIsNotReady(): void
    {
        $this->assistant->expects(self::never())->method('analyse');

        self::assertFalse($this->execute(false)['ok']);
    }

    public function testTheAnalysisIsReturnedAsJson(): void
    {
        $this->assistant->method('analyse')->willReturn(['comprehension' => 'Un coffret.', 'questions' => []]);

        self::assertSame(['ok' => true, 'comprehension' => 'Un coffret.', 'questions' => []], $this->execute());
    }
}
