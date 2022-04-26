<?php

declare(strict_types=1);

namespace Makaira\OxidConnectEssential\Domain;

use DateTimeImmutable;
use DateTimeInterface;

class Revision
{
    public const TYPE_PRODUCT      = 'product';
    public const TYPE_VARIANT      = 'variant';
    public const TYPE_CATEGORY     = 'category';
    public const TYPE_MANUFACTURER = 'manufacturer';

    public string $type;

    public string $objectId;

    public ?DateTimeInterface $changed = null;

    public ?int $revision = null;

    /**
     * @param string                 $type
     * @param string                 $objectId
     * @param DateTimeInterface|null $changed
     * @param int|null               $revision
     */
    public function __construct(
        string $type,
        string $objectId,
        ?DateTimeInterface $changed = null,
        ?int $revision = null
    ) {
        $this->revision = $revision;
        $this->changed  = $changed;
        $this->objectId = $objectId;
        $this->type     = $type;
        if (null === $changed) {
            $this->changed = new DateTimeImmutable();
        }
    }
}
