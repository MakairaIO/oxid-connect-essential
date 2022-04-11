<?php

namespace Makaira\OxidConnectEssential\Modifier\Common;

use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;

use function array_keys;
use function array_merge;
use function get_object_vars;

class PriceModifier extends Modifier
{
    /**
     * PriceModifier constructor.
     *
     * @param bool $isNetto
     * @param bool $showNetto
     * @param int  $defaultVAT
     */
    public function __construct(
        private ?bool $isNetto = false,
        private ?bool $showNetto = false,
        private ?int $defaultVAT = 19
    ) {
        $this->isNetto    = (bool) $this->isNetto;
        $this->showNetto  = (bool) $this->showNetto;
        $this->defaultVAT = (int) $this->defaultVAT;
    }

    /**
     * Modify product and return modified product
     *
     * @param Type $type
     *
     * @return Type
     */
    public function apply(Type $type)
    {
        if ($this->isNetto && !$this->showNetto) {
            $keys      = array_merge(array_keys(get_object_vars($type)), array_keys($type->additionalData));
            $priceKeys = array_filter($keys, static fn($key) => str_contains(strtolower($key), 'price'));
            $vat       = 1 + ($type->OXVAT ?? $this->defaultVAT) / 100.0;

            foreach ($priceKeys as $priceKey) {
                $type->{$priceKey} *= $vat;
            }
        }

        return $type;
    }
}
