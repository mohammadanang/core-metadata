<?php

namespace Apollo16\Core\MetaData\Eloquent;

use Illuminate\Database\Eloquent\Model;

/**
 * Metadata able eloquent model.
 *
 * @author      mohammad.anang  <m.anangnur@gmail.com>
 */

trait Metadatable
{
    /**
     * Determine if this model has metadata relationship.
     *
     * @return bool
     */
    public function hasMetadata()
    {
        if ($this instanceof Model) {
            return method_exists($this, $this->getMetadataRelationshipName());
        }

        return isset($this->metadata);
    }

    /**
     * Get metadata object.
     *
     * @param $key
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getMetadataObject($key)
    {
        $found = null;

        foreach($this->getRelationValue($this->getMetadataRelationshipName()) as $metadata) {
            if ($metadata instanceof Model and $metadata->{$metadata->getKeyColumn()} == $key) {
                $found = $metadata;
                break;
            }
        }

        return $found;
    }

    /**
     * Get metadata value.
     *
     * @param $key
     * @return mixed|null
     */
    public function getMetadata($key)
    {
        $object = $this->getMetadataObject($key);

        if ($object instanceof Model) {
            if ($object->hasGetMutator($key)) {
                return $object->{'get'.studly_case($key).'Attribute'}();
            }

            return $object->{$object->getValueColumn()};
        }

        return;
    }
    
    /**
     * Get an attribute from the model.
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if(
            empty($value)
            and $this->hasMetadata()
            and $this->getMetadataRelationshipName() != $key
            and !array_key_exists($key, $this->attributes)
        ) {
            return $this->getMetadata($key);
        }

        return $value;
    }
    
    /**
     * Get metadata relationship name.
     *
     * @return string
     */
    public function getMetadataRelationshipName()
    {
        return (property_exists($this, 'metadataRelation')) ? $this->metadataRelation : 'metadata';
    }
}