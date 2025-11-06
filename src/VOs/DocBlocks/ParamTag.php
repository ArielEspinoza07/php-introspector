<?php

declare(strict_types=1);

namespace Aurora\Reflection\VOs\DocBlocks;

use JsonSerializable;

final readonly class ParamTag implements JsonSerializable
{
    public function __construct(
        public string $name,
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
            'name' => $this->name,
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
