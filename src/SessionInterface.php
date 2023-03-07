<?php

namespace IngenicoClient;

interface SessionInterface
{
    /**
     * Get all Session values in a key => value format
     *
     * @return array
     */
    public function getSessionValues(): array;

    /**
     * Get value from Session.
     *
     * @param string $key
     * @return mixed
     */
    public function getSessionValue(string $key): mixed;

    /**
     * Store value in Session.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setSessionValue(string $key, mixed $value): void;

    /**
     * Remove value from Session.
     *
     * @param $key
     * @return void
     */
    public function unsetSessionValue($key): void;
}
