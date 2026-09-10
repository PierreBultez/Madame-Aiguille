<?php
declare(strict_types=1);

namespace MadameAiguille\Contact\Model;

class Attachment
{
    public function __construct(
        private readonly string $absolutePath,
        private readonly string $originalName,
        private readonly string $mimeType
    ) {
    }

    public function getAbsolutePath(): string
    {
        return $this->absolutePath;
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }
}
