<?php

namespace App\Interfaces;

interface IMarkdownParser
{
    public function parseMarkdown(string $markdown): string;

    public function parseMarkdownNoBlocks(string $markdown): string;
}
