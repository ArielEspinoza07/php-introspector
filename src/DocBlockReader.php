<?php

declare(strict_types=1);

namespace Aurora\Reflection;

use Aurora\Reflection\VOs\DocBlocks\DocBlockMetadata;
use Aurora\Reflection\VOs\DocBlocks\ParamTag;
use Aurora\Reflection\VOs\DocBlocks\ReturnTag;
use Aurora\Reflection\VOs\DocBlocks\ThrowsTag;
use Aurora\Reflection\VOs\DocBlocks\VarTag;

final class DocBlockReader
{
    public function getMetadata(?string $docComment): ?DocBlockMetadata
    {
        if ($docComment === null || $docComment === false || $docComment === '') {
            return null;
        }

        [$contentLines, $tagLines] = $this->parseDocComment($docComment);

        if (empty($contentLines) && empty($tagLines)) {
            return null;
        }

        [$summary, $description] = $this->extractSummaryAndDescription($contentLines);
        [$params, $return, $var, $throws] = $this->extractTags($tagLines);

        if ($summary === null && $description === null && empty($params) && $return === null && $var === null && empty($throws)) {
            return null;
        }

        return new DocBlockMetadata(
            summary: $summary,
            description: $description,
            params: $params,
            return: $return,
            var: $var,
            throws: $throws,
        );
    }

    /**
     * @return array{0: list<string>, 1: list<string>}
     */
    private function parseDocComment(string $docComment): array
    {
        // Remove /** and */
        $docComment = preg_replace('#^/\*\*#', '', $docComment);
        $docComment = preg_replace('#\*/$#', '', $docComment);

        // Split by lines
        $lines = explode("\n", $docComment);

        $contentLines = [];
        $tagLines = [];
        $inTags = false;

        foreach ($lines as $line) {
            // Remove leading * and whitespace
            $line = preg_replace('#^\s*\*\s?#', '', $line);
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            // Check if we're in the tags section
            if (str_starts_with($line, '@')) {
                $inTags = true;
            }

            if ($inTags) {
                $tagLines[] = $line;
            } else {
                $contentLines[] = $line;
            }
        }

        return [$contentLines, $tagLines];
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

    /**
     * @param  list<string>  $tagLines
     * @return array{0: list<ParamTag>, 1: ReturnTag|null, 2: VarTag|null, 3: list<ThrowsTag>}
     */
    private function extractTags(array $tagLines): array
    {
        $params = [];
        $return = null;
        $var = null;
        $throws = [];

        foreach ($tagLines as $line) {
            if (str_starts_with($line, '@param')) {
                $param = $this->parseParamTag($line);
                if ($param !== null) {
                    $params[] = $param;
                }
            } elseif (str_starts_with($line, '@return')) {
                $return = $this->parseReturnTag($line);
            } elseif (str_starts_with($line, '@var')) {
                $var = $this->parseVarTag($line);
            } elseif (str_starts_with($line, '@throws')) {
                $throw = $this->parseThrowsTag($line);
                if ($throw !== null) {
                    $throws[] = $throw;
                }
            }
        }

        return [$params, $return, $var, $throws];
    }

    private function parseParamTag(string $line): ?ParamTag
    {
        // @param type $name description
        $pattern = '/@param\s+([^\s]+)?\s*(\$[^\s]+)?\s*(.*)?/';
        if (preg_match($pattern, $line, $matches)) {
            $type = isset($matches[1]) && $matches[1] !== '' ? $matches[1] : null;
            $name = isset($matches[2]) && $matches[2] !== '' ? ltrim($matches[2], '$') : '';
            $description = isset($matches[3]) && $matches[3] !== '' ? trim($matches[3]) : null;

            if ($name !== '') {
                return new ParamTag(
                    name: $name,
                    type: $type,
                    description: $description,
                );
            }
        }

        return null;
    }

    private function parseReturnTag(string $line): ?ReturnTag
    {
        // @return type description
        $pattern = '/@return\s+([^\s]+)?\s*(.*)?/';
        if (preg_match($pattern, $line, $matches)) {
            $type = isset($matches[1]) && $matches[1] !== '' ? $matches[1] : null;
            $description = isset($matches[2]) && $matches[2] !== '' ? trim($matches[2]) : null;

            return new ReturnTag(
                type: $type,
                description: $description,
            );
        }

        return null;
    }

    private function parseVarTag(string $line): ?VarTag
    {
        // @var type description
        $pattern = '/@var\s+([^\s]+)?\s*(.*)?/';
        if (preg_match($pattern, $line, $matches)) {
            $type = isset($matches[1]) && $matches[1] !== '' ? $matches[1] : null;
            $description = isset($matches[2]) && $matches[2] !== '' ? trim($matches[2]) : null;

            return new VarTag(
                type: $type,
                description: $description,
            );
        }

        return null;
    }

    private function parseThrowsTag(string $line): ?ThrowsTag
    {
        // @throws ExceptionType description
        $pattern = '/@throws\s+([^\s]+)?\s*(.*)?/';
        if (preg_match($pattern, $line, $matches)) {
            $type = isset($matches[1]) && $matches[1] !== '' ? $matches[1] : null;
            $description = isset($matches[2]) && $matches[2] !== '' ? trim($matches[2]) : null;

            return new ThrowsTag(
                type: $type,
                description: $description,
            );
        }

        return null;
    }
}
