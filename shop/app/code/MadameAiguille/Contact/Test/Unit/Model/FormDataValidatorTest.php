<?php
declare(strict_types=1);

namespace MadameAiguille\Contact\Test\Unit\Model;

use MadameAiguille\Contact\Model\FormDataValidator;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FormDataValidatorTest extends TestCase
{
    private FormDataValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new FormDataValidator();
    }

    public function testValidDataIsTrimmedAndSubjectLabelIsResolved(): void
    {
        $data = $this->validator->validate([
            'name' => '  Céline  ',
            'email' => ' celine@example.test ',
            'subject' => 'product',
            'comment' => ' Une question sur ce modèle. ',
            'product' => ' MA-001 ',
            'consent' => '1',
            'hideit' => '',
        ]);

        self::assertSame('Céline', $data['name']);
        self::assertSame('celine@example.test', $data['email']);
        self::assertSame('Une création ou un tissu', $data['subject_label']);
        self::assertSame('MA-001', $data['product']);
    }

    /** @return iterable<string, array{array<string, string>}> */
    public static function invalidDataProvider(): iterable
    {
        $valid = [
            'name' => 'Céline',
            'email' => 'celine@example.test',
            'subject' => 'other',
            'comment' => 'Bonjour',
            'product' => '',
            'consent' => '1',
            'hideit' => '',
        ];

        yield 'email' => [array_replace($valid, ['email' => 'incorrect'])];
        yield 'objet inconnu' => [array_replace($valid, ['subject' => 'inconnu'])];
        yield 'message trop long' => [array_replace($valid, ['comment' => str_repeat('a', 1001)])];
        yield 'consentement absent' => [array_replace($valid, ['consent' => '0'])];
        yield 'pot de miel rempli' => [array_replace($valid, ['hideit' => 'robot'])];
    }

    #[DataProvider('invalidDataProvider')]
    public function testInvalidDataIsRejected(array $data): void
    {
        $this->expectException(LocalizedException::class);
        $this->validator->validate($data);
    }
}
