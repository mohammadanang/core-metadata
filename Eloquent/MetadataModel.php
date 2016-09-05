<?php

namespace Apollo16\Core\MetaData\Eloquent;

use Carbon\Carbon;
use DateTime;

/**
 * Metadata eloquent model.
 *
 * @author      mohammad.anang  <m.anangnur@gmail.com>
 */

trait MetadataModel
{
    /**
     * The "booting" method of the model.
     */
    public static function bootMetadataModel()
    {
        static::saving(function($metadata) {
            $metadata->castValueColumnAttribute();

            return true;
        });
    }

    /**
     * Get a plain attribute (not a relationship).
     *
     * @param string $key
     * @return mixed
     */
    public function getAttributeValue($key)
    {
        if (
            $this->isCastable() == true
            and $this->hasGetMutator($key) == false
            and $this->getValueColumn() == $key
        ) {
            $value = $this->getAttributeFromArray($key);

            switch ($this->getOriginal($this->getTypeColumn())) {
                case 'int':
                case 'integer':
                    return (int) $value;
                case 'real':
                case 'float':
                case 'double':
                    return (float) $value;
                case 'string':
                    return (string) $value;
                case 'bool':
                case 'boolean':
                    return (bool) $value;
                case 'object':
                    return json_decode($value);
                case 'datetime':
                case 'date':
                case 'timestamp':
                    return Carbon::createFromFormat('Y-m-d H:i:s', $value);
                case 'array':
                case 'json':
                    return json_decode($value, true);
                case 'collection':
                    return $this->newCollection(json_decode($value, true));
                default:
                    return $value;
            }
        }

        return parent::getAttributeValue($key);
    }

    /**
     * Cast value column to match the cast type.
     *
     * @return mixed
     */
    public function castValueColumnAttribute()
    {
        $cast = $this->getAttributeFromArray($this->getTypeColumn()) ?: 'string';
        $value = $this->getAttributeFromArray($this->getValueColumn());

        switch($cast) {
            case 'array':
            case 'json':
            case 'object':
            case 'collection':
                return json_encode($this->attributes[$this->getValueColumn()]);
            break;
            case 'datetime':
            case 'date':
            case 'timestamp':
                if ($value instanceof DateTime) {
                    $value->$value->format('Y-m-d H:i:s');
                } else {
                    $value = $this->asDateTime($this->attributes[$this->getValueColumn()])->format('Y-m-d H:i:s');
                }
        }
        $this->attributes[$this->getValueColumn()] = $value;
    }

    /**
     * Get metadata key column.
     *
     * @return string
     */
    public function getKeyColumn()
    {
        return property_exists($this, 'keyColumn') ? $this->{'keyColumn'} : 'key';
    }

    /**
     * Get metadata value column.
     * 
     * @return string
     */
    public function getValueColumn()
    {
        return property_exists($this, 'valueColumn') ? $this->{'valueColumn'} : 'value';
    }

    /**
     * Get metadata type column.
     *
     * @return string
     */
    public function getTypeColumn()
    {
        return property_exists($this, 'typeColumn') ? $this->{'typeColumn'} : 'type';
    }

    /**
     * Determine if this metadata is castable.
     *
     * @return bool
     */
    public function isCastable()
    {
        return property_exists($this, 'castable') ? $this->{'castable'} : true;
    }
}