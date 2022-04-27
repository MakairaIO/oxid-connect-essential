<?php

namespace Makaira\OxidConnectEssential\Modifier\Common;

use Makaira\OxidConnectEssential\Modifier;
use Makaira\OxidConnectEssential\Type;

class ZeroDateTimeModifier extends Modifier
{
    /**
     * @var array<string>
     */
    private array $zeroDateValues = ['0000-00-00', '0000-00-00 00:00:00'];
    /**
     * Modify product and return modified product
     *
     * @param Type $type
     *
     * @return Type
     */
    public function apply(Type $type)
    {
        foreach ($type as $property => $value) {
            if (is_array($value)) {
                $type->$property = array_map(fn ($item) => $this->isZeroDate($item) ? null : $item, $value);
            }
            if ($this->isZeroDate($value)) {
                $type->$property = null;
            }
        }

        return $type;
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    private function isZeroDate($value): bool
    {
        return is_string($value) && in_array($value, $this->zeroDateValues, true);
    }
}
