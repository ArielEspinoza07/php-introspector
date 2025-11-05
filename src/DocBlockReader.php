<?php

declare(strict_types=1);

namespace Aurora\Reflection;

use Aurora\Reflection\VOs\DocBlocks\DocBlockMetadata;

final class DocBlockReader
{
    public function getMetadata(?string $docComment): ?DocBlockMetadata
    {
        if ($docComment === null || $docComment === false || $docComment === '') {
            return null;
        }

        $lines = $this->parseDocComment($docComment);

        if (empty($lines)) {
            return null;
        }

        [$summary, $description] = $this->extractSummaryAndDescription($lines);

        if ($summary === null && $description === null) {
            return null;
        }

        return new DocBlockMetadata(
            summary: $summary,
            description: $description,
        );
    }

    /**
     * @return list<string>
     */
    private function parseDocComment(string $docComment): array
    {
        // Remove /** and */
        $docComment = preg_replace('#^/\*\*#', '', $docComment);
        $docComment = preg_replace('#\*/$#', '', $docComment);

        // Split by lines
        $lines = explode("\n", $docComment);

        $parsed = [];
        foreach ($lines as $line) {
            // Remove leading * and whitespace
            $line = preg_replace('#^\s*\*\s?#', '', $line);
            $line = trim($line);

            // Stop at tags (@param, @return, etc.)
            if (str_starts_with($line, '@')) {
                break;
            }

            if ($line !== '') {
                $parsed[] = $line;
            }
        }

        return $parsed;
    }

    /**
     * @param  list<string>  $lines
     * @return array{0: string|null, 1: string|null}
     */
    private function extractSummaryAndDescription(array $lines): array
    {
        if (empty($lines)) {
            return [null, null];
        }

        // First line is the summary
        $summary = $lines[0];

        // Rest is description
        $descriptionLines = array_slice($lines, 1);
        $description = empty($descriptionLines) ? null : implode(' ', $descriptionLines);

        return [$summary, $description];
    }
}
