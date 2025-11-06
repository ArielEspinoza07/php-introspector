<?php

declare(strict_types=1);

namespace Aurora\Reflection\VOs\DocBlocks;

use JsonSerializable;

final readonly class CustomTag implements JsonSerializable
{
    public function __construct(
        public ?string $type = null,
        public ?string $description = null,
    ) {
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'description' => $this->description,
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
