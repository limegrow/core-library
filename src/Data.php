<?php

namespace IngenicoClient;

use InvalidArgumentException;

class Data implements \ArrayAccess
{
    protected array $data = [];

    /**
     * Check is data exists
     * @param $key
     * @return bool
     */
    public function hasData($key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Get Data
     * @param mixed|null $key
     * @return array|mixed
     */
    public function getData(mixed $key = null): mixed
    {
        if (!$key) {
            return $this->data;
        }

        return $this->data[$key] ?? null;
    }

    /**
     * Set Data
     * @param $key
     * @param mixed|null $value
     * @return $this
     */
    public function setData($key, mixed $value = null): static
    {
        if (is_array($key)) {
            foreach ($key as $key1 => $value1) {
                if (is_scalar($key1)) {
                    $this->setData($key1, $value1);
                }
            }
        } elseif (is_scalar($key)) {
            $this->data[$key] = $value;
        } else {
            throw new InvalidArgumentException(sprintf('Invalid type for index %s', var_export($key, true)));
        }

        return $this;
    }

    /**
     * Unset Data
     * @param $key
     * @return $this
     */
    public function unsData($key): static
    {
        if ($this->hasData($key)) {
            unset($this->data[$key]);
        }

        return $this;
    }

    /**
     * Get Data as array
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Set/Get attribute wrapper
     *
     * @param string $method
     * @param array $args
     * @return  mixed
     * @throws Exception
     */
    public function __call(string $method, array $args)
    {
        switch (substr($method, 0, 3)) {
            case 'get':
                $key = $this->underscore(substr($method, 3));
                return $this->getData($key);
            case 'set':
                $key = $this->underscore(substr($method, 3));
                $this->setData($key, $args[0] ?? null);
                return $this;
            case 'uns':
                $key = $this->underscore(substr($method, 3));
                $this->unsData($key);
                return $this;
            case 'has':
                $key = $this->underscore(substr($method, 3));
                return $this->hasData($key);
        }

        throw new Exception(sprintf('Invalid method %s::%s', get_class($this), $method));
    }

    /**
     * Implementation of \ArrayAccess::offsetSet()
     *
     * @param string $offset
     * @param mixed $value
     * @return void
     * @link http://www.php.net/manual/en/arrayaccess.offsetset.php
     */
    public function offsetSet(string $offset, mixed $value): void
    {
        $this->data[$offset] = $value;
    }

    /**
     * Implementation of \ArrayAccess::offsetExists()
     *
     * @param string $offset
     * @return bool
     * @link http://www.php.net/manual/en/arrayaccess.offsetexists.php
     */
    public function offsetExists(string $offset): bool
    {
        return $this->hasData($offset);
    }

    /**
     * Implementation of \ArrayAccess::offsetUnset()
     *
     * @param string $offset
     * @return void
     * @link http://www.php.net/manual/en/arrayaccess.offsetunset.php
     */
    public function offsetUnset(string $offset): void
    {
        $this->unsData($offset);
    }

    /**
     * Implementation of \ArrayAccess::offsetGet()
     *
     * @param string $offset
     * @return mixed
     * @link http://www.php.net/manual/en/arrayaccess.offsetget.php
     */
    public function offsetGet($offset): mixed
    {
        return $this->getData($offset);
    }

    /**
     * Converts field names for setters and getters
     *
     * @param string $name
     * @return string
     */
    protected function underscore(string $name): string
    {
        return strtolower(preg_replace('/(.)([A-Z])/', '$1_$2', $name));
    }
}
