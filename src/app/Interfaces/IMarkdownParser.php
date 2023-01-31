<?php

namespace App\Interfaces;

interface IMarkdownParser
{
    function parseMarkdown(string $markdown): string;
    function parseMarkdownNoBlocks(string $markdown): string;
}
