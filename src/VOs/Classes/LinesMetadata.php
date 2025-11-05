<?php

declare(strict_types=1);

namespace Aurora\Reflection\VOs\Classes;

use JsonSerializable;

final class LinesMetadata implements JsonSerializable
{
    public function __construct(
        public int $start,
        public int $end,
    ) {}

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'start' => $this->start,
            'end' => $this->end,
        ];
    }

    /**
     * @return array<string, int>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}