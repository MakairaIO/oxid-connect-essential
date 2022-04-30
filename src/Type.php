<?php

namespace Makaira\OxidConnectEssential;

class Type
{
    /* primary es id field */
    public ?string $es_id;

    /* primary id field */
    public ?string $id;

    /* required fields + mak-fields */
    public ?string $timestamp;
    public ?string $url;
    public bool $active = true;
    public array $shop = [];

    /** @var array<string, float|int|string|array<int|string, string|float|int>> */
    public array $additionalData = [];

    public array $selfLinks = [];

    /**
     * Generic constructor
     *
     * @param array $values
     * @return void
     */
    public function __construct(array $values = array())
    {
        foreach ($values as $name => $value) {
            $this->{$name} = $value;
        }
    }

    /**
     * @param string $name
     * @param float|int|string|array<int|string, string|float|int> $value
     *
     * @return void
     */
    public function __set(string $name, $value)
    {
        $this->additionalData[$name] = $value;
    }

    /**
     * @param string $name
     *
     * @return float|int|string|array<int|string, string|float|int>|null
     */
    public function __get(string $name)
    {
        return $this->additionalData[$name] ?? null;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name)
    {
        // unknown fields are added to additional data array
        return isset($this->additionalData[$name]);
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function __unset(string $name)
    {
        unset($this->additionalData[$name]);
    }
}
