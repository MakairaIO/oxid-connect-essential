<?php

namespace Makaira\OxidConnectEssential\Modifier\Common;

use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;
use Makaira\OxidConnectEssential\Utils\ContentParserInterface;

class LongDescriptionModifier extends Modifier
{
    /** @var  ContentParserInterface */
    private ContentParserInterface $contentParser;

    private bool $parseThroughSmarty;

    /**
     * LongDescriptionModifier constructor.
     *
     * @param ContentParserInterface $contentParser
     * @param bool                   $parseThroughSmarty
     */
    public function __construct(ContentParserInterface $contentParser, bool $parseThroughSmarty = false)
    {
        $this->parseThroughSmarty = $parseThroughSmarty;
        $this->contentParser      = $contentParser;
    }

    /**
     * Modify product and return modified product
     *
     * @param Type\Product\Product $product
     *
     * @return Type
     */
    public function apply(Type $product)
    {
        if ($this->parseThroughSmarty) {
            $product->longdesc = $this->contentParser->parseContent($product->longdesc);
        }

        $product->longdesc = trim(strip_tags($product->longdesc));

        return $product;
    }
}
