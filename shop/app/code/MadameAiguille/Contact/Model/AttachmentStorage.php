<?php
declare(strict_types=1);

namespace MadameAiguille\Contact\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Mime;

class AttachmentStorage
{
    private const MAX_SIZE = 5242880;
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    public function __construct(
        private readonly DirectoryList $directoryList,
        private readonly Mime $mime
    ) {
    }

    public function store(?array $file): ?Attachment
    {
        if (!$file || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new LocalizedException(__('La photo n’a pas pu être reçue. Réessayez.'));
        }

        $temporaryPath = (string) ($file['tmp_name'] ?? '');
        $size = (int) ($file['size'] ?? 0);
        if ($temporaryPath === '' || $size < 1 || $size > self::MAX_SIZE) {
            throw new LocalizedException(__('La photo doit peser 5 Mo maximum.'));
        }

        $mimeType = $this->mime->getMimeType($temporaryPath);
        if (!isset(self::ALLOWED_MIME_TYPES[$mimeType])) {
            throw new LocalizedException(__('Seules les images JPG et PNG sont acceptées.'));
        }

        $directory = $this->directoryList->getPath(DirectoryList::VAR_DIR)
            . '/madameaiguille/contact/' . date('Y/m');
        if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
            throw new LocalizedException(__('La photo ne peut pas être enregistrée actuellement.'));
        }

        $destination = $directory . '/' . bin2hex(random_bytes(16)) . '.' . self::ALLOWED_MIME_TYPES[$mimeType];
        if (!move_uploaded_file($temporaryPath, $destination)) {
            throw new LocalizedException(__('La photo ne peut pas être enregistrée actuellement.'));
        }
        chmod($destination, 0640);

        return new Attachment(
            $destination,
            $this->sanitizeOriginalName((string) ($file['name'] ?? 'photo')),
            $mimeType
        );
    }

    public function delete(?Attachment $attachment): void
    {
        if ($attachment && is_file($attachment->getAbsolutePath())) {
            unlink($attachment->getAbsolutePath());
        }
    }

    private function sanitizeOriginalName(string $name): string
    {
        $name = basename(str_replace('\\', '/', $name));
        $name = preg_replace('/[^\pL\pN._ -]+/u', '-', $name) ?: 'photo';

        return mb_substr($name, 0, 150);
    }
}
