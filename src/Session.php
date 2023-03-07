<?php

namespace IngenicoClient;

/**
 * Trait Session
 * @package IngenicoClient
 */
trait Session
{
    /**
     * Get all Session values in a key => value format
     */
    public function getSessionValues(): array
    {
        $values = $this->extension->getSessionValues();

        foreach ($values as $key => $value) {
            if (false !== ($tmp = @unserialize($value))) {
                $values[$key] = $tmp;
            }
        }

        return $values;
    }

    /**
     * Get value from Session.
     */
    public function getSessionValue(string $key): mixed
    {
        $value = $this->extension->getSessionValue($key);

        if (false !== ($tmp = @unserialize($value))) {
            return $tmp;
        }

        return $value;
    }

    /**
     * Store value in Session.
     */
    public function setSessionValue(string $key, mixed $value): void
    {
        if (is_array($value) || is_object($value)) {
            $value = serialize($value);
        }

        $this->extension->setSessionValue($key, $value);
    }

    /**
     * Remove value from Session.
     *
     * @param $key
     */
    public function unsetSessionValue($key): void
    {
        $this->extension->unsetSessionValue($key);
    }
}
