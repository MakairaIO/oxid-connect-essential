<?php

namespace Makaira\OxidConnectEssential\Modifier\Common;

use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;
use Makaira\OxidConnectEssential\Utils\ContentParserInterface;

class LongDescriptionModifier extends Modifier
{
    /** @var  ContentParserInterface */
    private $contentParser;

    /**
     * LongDescriptionModifier constructor.
     *
     * @param ContentParserInterface $contentParser
     */
    public function __construct(ContentParserInterface $contentParser, private bool $parseThroughSmarty = false)
    {
        $this->contentParser      = $contentParser;
    }

    /**
     * Modify product and return modified product
     *
     * @param Type $product
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
