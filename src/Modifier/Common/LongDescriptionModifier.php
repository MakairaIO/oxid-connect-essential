<?php

namespace Makaira\OxidConnectEssential\Modifier\Common;

use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;
use Makaira\OxidConnectEssential\Utils\ContentParserInterface;

/**
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
class LongDescriptionModifier extends Modifier
{
    /**
     * @param ContentParserInterface $renderer
     * @param bool|null              $parseLongDesc
     */
    public function __construct(
        private ContentParserInterface $renderer,
        private ?bool $parseLongDesc = false,
    ) {
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
        if ($this->parseLongDesc) {
            $product->longdesc = $this->renderer->parseContent((string)$product->id, $product->longdesc);
        }

        $product->longdesc = trim(strip_tags($product->longdesc));

        return $product;
    }
}
