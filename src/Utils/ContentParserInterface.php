<?php

namespace Makaira\OxidConnectEssential\Utils;

/**
 * Interface ContentParserInterface
 *
 * @package Makaira\Connect\Utils
 */
interface ContentParserInterface
{
    /**
     * Parse content through a templating engine
     *
     * @param string $id
     * @param string $content
     *
     * @return string
     */
    public function parseContent(string $id, string $content): string;
}
