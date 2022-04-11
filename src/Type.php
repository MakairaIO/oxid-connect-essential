<?php

namespace Makaira\OxidConnectEssential;

use Kore\DataObject\DataObject;

class Type extends DataObject
{
    /* primary es id field */
    public $es_id;

    /* primary id field */
    public $id;

    /* required fields + mak-fields */
    public $timestamp;
    public $url;
    public $active = true;
    public $shop = [];

    /* additional data array */
    public $additionalData = [];

    public $selfLinks;

    public function __set($name, $value)
    {
        try {
            parent::__set($name, $value);
        } catch (\Exception $e) {
            // catch exception on unknown fields
            // unknown fields will be added to additional data array
            $this->additionalData[ $name ] = $value;
        }
    }

    public function __get($name)
    {
        try {
            parent::__get($name);
        } catch (\Exception $e) {
            // catch exception on unknown fields
            // unknown fields are added to additional data array
            if (!array_key_exists($name, $this->additionalData)) {
                throw $e;
            }
            return $this->additionalData[ $name ];
        }
    }

    public function __isset($name)
    {
        // unknown fields are added to additional data array
        return isset($this->additionalData[ $name ]);
    }

    public function __unset($name)
    {
        try {
            parent::__unset($name);
        } catch (\Exception $e) {
            // catch exception on unknown fields
            // unknown fields are added to additional data array
            if (!array_key_exists($name, $this->additionalData)) {
                throw $e;
            }
            unset($this->additionalData[ $name ]);
        }
    }
}
