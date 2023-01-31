<?php

namespace App\Helpers;

use App\Interfaces\IMarkdownParser;

class MarkdownParserWrapper implements IMarkdownParser
{
    private $_defaultParser;
    private $_parserNoBlocks;

    public function __construct()
    {
        $this->_defaultParser = resolve(MarkdownParser::class);
        $this->_parserNoBlocks = resolve(MarkdownParser::class, [['#','>']]);
    }

    public function parseMarkdown(string $markdown): string
    {
        return $this->_defaultParser->text($markdown);
    }

    public function parseMarkdownNoBlocks(string $markdown): string
    {
        return $this->_parserNoBlocks->text($markdown);
    }
}
