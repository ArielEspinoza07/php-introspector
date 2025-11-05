<?php

declare(strict_types=1);

namespace Aurora\Reflection;

use Aurora\Reflection\VOs\DocBlocks\CustomTag;
use Aurora\Reflection\VOs\DocBlocks\DocBlockMetadata;
use Aurora\Reflection\VOs\DocBlocks\ParamTag;
use Aurora\Reflection\VOs\DocBlocks\ReturnTag;
use Aurora\Reflection\VOs\DocBlocks\ThrowsTag;
use Aurora\Reflection\VOs\DocBlocks\VarTag;

final class DocBlockReader
{
    public function getMetadata(?string $docComment): ?DocBlockMetadata
    {
        if (in_array($docComment, [null, false, ''])) {
            return null;
        }

        [$contentLines, $tagLines] = $this->parseDocComment($docComment);

        if (empty($contentLines) && empty($tagLines)) {
            return null;
        }

        [$summary, $description] = $this->extractSummaryAndDescription($contentLines);
        [$params, $return, $var, $throws, $customs] = $this->extractTags($tagLines);

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
            custom: $customs,
        );
    }

    /**
     * @return array{0: list<string>, 1: list<string>}
     */
    private function parseDocComment(string $docComment): array
    {
        // Remove /**, */ and *
        $docComment = strtr($docComment, ['/**' => '', '*/' => '', '*' => '']);

        // Split by lines
        $lines = explode("\n", $docComment);

        $contentLines = [];
        $tagLines = [];
        $inTags = false;

        foreach ($lines as $line) {
            // Remove whitespace
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
     * @return array{0: list<ParamTag>, 1: ReturnTag|null, 2: VarTag|null, 3: list<ThrowsTag>, 4: list<CustomTag>}
     */
    private function extractTags(array $tagLines): array
    {
        $params = [];
        $return = null;
        $var = null;
        $throws = [];
        $customs = [];

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
            } elseif (str_starts_with($line, '@')) {
                $custom = $this->parseCustomTag($line);
                if ($custom !== null) {
                    $customs[] = $custom;
                }
            }
        }

        return [$params, $return, $var, $throws, $customs];
    }

    private function parseParamTag(string $line): ?ParamTag
    {
        // @param type $name description
        // Remove the @param tag
        $text = preg_replace('/^@param\s+/', '', $line);

        if ($text === null || $text === '') {
            return null;
        }

        // Extract the type first
        [$type, $remaining] = $this->extractType($text);

        // Now extract the parameter name ($variable or &$variable for by-reference)
        $name = '';
        $description = '';

        if (preg_match('/^&?\$([^\s]+)\s*(.*)/', $remaining, $matches)) {
            $name = $matches[1];
            /** @phpstan-ignore-next-line */
            $description = isset($matches[2]) && $matches[2] !== '' ? trim($matches[2]) : '';
        }

        if ($name === '') {
            return null;
        }

        return new ParamTag(
            name: $name,
            type: $type,
            description: $description !== '' ? $description : null,
        );
    }

    private function parseReturnTag(string $line): ?ReturnTag
    {
        // @return type description
        // Remove the @return tag
        $text = preg_replace('/^@return\s+/', '', $line);

        if ($text === null || $text === '') {
            return null;
        }

        [$type, $description] = $this->extractType($text);

        return new ReturnTag(
            type: $type,
            description: $description !== '' ? $description : null,
        );
    }

    private function parseVarTag(string $line): ?VarTag
    {
        // @var type description
        // Remove the @var tag
        $text = preg_replace('/^@var\s+/', '', $line);

        if ($text === null || $text === '') {
            return null;
        }

        [$type, $description] = $this->extractType($text);

        return new VarTag(
            type: $type,
            description: $description !== '' ? $description : null,
        );
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

    private function parseCustomTag(string $line): ?CustomTag
    {
        // @tagname description
        // Extract tag name and description
        $pattern = '/^@([a-zA-Z0-9_-]+)\s*(.*)?/';
        if (preg_match($pattern, $line, $matches)) {
            $type = $matches[1];
            $description = isset($matches[2]) && $matches[2] !== '' ? trim($matches[2]) : null;

            return new CustomTag(
                type: $type,
                description: $description,
            );
        }

        return null;
    }

    /**
     * Extract type from a line, handling generic types with angle brackets
     *
     * This method parses types character by character to correctly handle
     * generic types like array<string, mixed> or Collection<User, array<int, string>>
     *
     * @param  string  $text  The text to parse (after the tag name)
     * @return array{0: string|null, 1: string} [type, remaining text]
     */
    private function extractType(string $text): array
    {
        $text = ltrim($text);

        if ($text === '') {
            return [null, ''];
        }

        $depth = 0;
        $type = '';
        $length = mb_strlen($text);

        for ($i = 0; $i < $length; $i++) {
            $char = $text[$i];

            if ($char === '<') {
                $depth++;
                $type .= $char;
            } elseif ($char === '>') {
                $depth--;
                $type .= $char;

                // If we've closed all brackets, continue until we hit a space or end
                if ($depth === 0) {
                    continue;
                }
            } elseif ($depth === 0 && ctype_space($char)) {
                // Found a space outside of angle brackets - type is complete
                break;
            } else {
                $type .= $char;
            }
        }

        // Get the remaining text after the type
        $remaining = mb_substr($text, mb_strlen($type));

        return [trim($type) !== '' ? trim($type) : null, trim($remaining)];
    }
}
