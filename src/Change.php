<?php

namespace Makaira\OxidConnectEssential;

/**
 * Class Change
 *
 * @package Makaira\Connect
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class Change extends \Kore\DataObject\DataObject
{
    /**
     * @var string|null
     */
    public ?string $id = null;

    /**
     * @var int|null
     */
    public ?int $sequence = null;

    /**
     * @var bool
     */
    public bool $deleted = false;

    /**
     * @var Type|null
     */
    public ?Type $data = null;

    /**
     * @var string
     */
    public string $type = '';
}
