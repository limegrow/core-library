<?php

namespace IngenicoClient;

/**
 * Class Request.
 */
class Request implements \ArrayAccess
{
    /**
     * HTTP Request Constants
     */
    const ORDERID = 'orderID';
    const PAYID = 'PAYID';
    const PAYIDSUB = 'PAYIDSUB';
    const STATUS = 'STATUS';
    const NCERROR = 'NCERROR';
    const NCERRORPLUS = 'NCERRORPLUS';
    const PM = 'PM';
    const BRAND = 'BRAND';
    const CARDNO = 'CARDNO';
    const AMOUNT = 'amount';
    const CURRENCY = 'currency';
    const ALIAS_ID = 'Alias_AliasId';
    const ALIAS_ORDERID = 'Alias_OrderId';
    const ALIAS_STATUS = 'Alias_Status';
    const ALIAS_STOREPERMANENTLY = 'Alias_StorePermanently';
    const CARD_BRAND = 'Card_Brand';
    const CARD_NUMBER = 'Card_CardNumber';
    const CARD_BIN = 'Card_Bin';
    const CARD_EXPIRY_DATE = 'Card_ExpiryDate';


    /**
     * Result of the alias creation
     */
    const ALIAS_STATUS_OK = 0;
    const ALIAS_STATUS_NOK = 1;
    const ALIAS_STATUS_UPDATED = 2;
    const ALIAS_STATUS_CANCELLED = 3;

    /**
     * @var array
     */
    private array $data;

    /**
     * Request constructor.
     * @param array $data Array with source data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get Order ID
     * @param mixed|null $default
     * @return mixed
     */
    public function getOrderId(mixed $default = null): mixed
    {
        return $this->getParam(self::ORDERID, $default);
    }

    /**
     * Get PayID
     * @param mixed|null $default
     * @return mixed
     */
    public function getPayId(mixed $default = null): mixed
    {
        return $this->getParam(self::PAYID, $default);
    }

    /**
     * Get PayID Sub
     * @param mixed|null $default
     * @return mixed
     */
    public function getPayIdSub(mixed $default = null): mixed
    {
        return $this->getParam(self::PAYIDSUB, $default);
    }

    /**
     * Get PM
     * @param mixed|null $default
     * @return mixed
     */
    public function getPM(mixed $default = null): mixed
    {
        return $this->getParam(self::PM, $default);
    }

    /**
     * Get Brand
     * @param mixed|null $default
     * @return mixed
     */
    public function getBrand(mixed $default = null): mixed
    {
        return $this->getParam(self::BRAND, $default);
    }

    /**
     * Get CardNo
     * @param mixed|null $default
     * @return mixed
     */
    public function getCardNo(mixed $default = null): mixed
    {
        return $this->getParam(self::CARDNO, $default);
    }

    /**
     * Get Amount
     * @param mixed|null $default
     * @return mixed
     */
    public function getAmount(mixed $default = null): mixed
    {
        return $this->getParam(self::AMOUNT, $default);
    }

    /**
     * Get Currency
     * @param mixed|null $default
     * @return mixed
     */
    public function getCurrency(mixed $default = null): mixed
    {
        return $this->getParam(self::CURRENCY, $default);
    }

    /**
     * Get Status
     * @param mixed|null $default
     * @return mixed
     */
    public function getStatus(mixed $default = null): mixed
    {
        return $this->getParam(self::STATUS, $default);
    }

    /**
     * Get Error
     * @param mixed|null $default
     * @return mixed
     */
    public function getNCError(mixed $default = null): mixed
    {
        return $this->getParam(self::NCERROR, $default);
    }

    /**
     * Get Error Plus
     * @param mixed|null $default
     * @return mixed
     */
    public function getNCErrorPlus(mixed $default = null): mixed
    {
        return $this->getParam(self::NCERRORPLUS, $default);
    }

    /**
     * Get Alias ID
     * @param mixed|null $default
     * @return mixed
     */
    public function getAliasID(mixed $default = null): mixed
    {
        return $this->getParam(self::ALIAS_ID, $default);
    }

    /**
     * Get Alias Order ID
     * @param mixed|null $default
     * @return mixed
     */
    public function getAliasOrderID(mixed $default = null): mixed
    {
        return $this->getParam(self::ALIAS_ORDERID, $default);
    }

    /**
     * Get Alias Status
     * @param mixed|null $default
     * @return int
     */
    public function getAliasStatus(mixed $default = null): int
    {
        return (int)$this->getParam(self::ALIAS_STATUS, $default);
    }

    /**
     * Get Alias Status
     * @param mixed|null $default
     * @return bool
     */
    public function getAliasStorePermanently(mixed $default = null): bool
    {
        return $this->getParam(self::ALIAS_STOREPERMANENTLY, $default) === 'Y';
    }

    /**
     * Get Card Brand
     * @param mixed|null $default
     * @return mixed
     */
    public function getCardBrand(mixed $default = null): mixed
    {
        return $this->getParam(self::CARD_BRAND, $default);
    }

    /**
     * Get Card Number
     * @param mixed|null $default
     * @return mixed
     */
    public function getCardNumber(mixed $default = null): mixed
    {
        return $this->getParam(self::CARD_NUMBER, $default);
    }

    /**
     * Get Card BIN
     * @param mixed|null $default
     * @return mixed
     */
    public function getCardBin(mixed $default = null): mixed
    {
        return $this->getParam(self::CARD_BIN, $default);
    }

    /**
     * Get Card Expiry Date
     * @param mixed|null $default
     * @return mixed
     */
    public function getCardExpiryDate(mixed $default = null): mixed
    {
        return $this->getParam(self::CARD_EXPIRY_DATE, $default);
    }

    /**
     * Get param
     * @param string $name
     * @param mixed|null $default
     * @return mixed
     */
    public function getParam(string $name, mixed $default = null): mixed
    {
        return $this->hasParam($name) ? $this->data[$name] : $default;
    }

    /**
     * Has Param
     * @param $name
     * @return bool
     */
    public function hasParam($name): bool
    {
        return isset($this->data[$name]);
    }

    /**
     * Is have Alias
     * @return bool
     */
    public function hasAlias(): bool
    {
        return $this->getAliasID(false) !== false;
    }

    /**
     * Is Alias Stored Success
     * @return bool
     */
    public function isAliasStoredSuccess(): bool
    {
        return in_array($this->getAliasStatus(), [self::ALIAS_STATUS_OK, self::ALIAS_STATUS_UPDATED]);
    }

    /**
     * Get Alias Card Data
     * @return array
     */
    public function getAliasData(): array
    {
        return [
            'ALIAS' => $this->getAliasID(),
            'BRAND' => $this->getCardBrand(''),
            'CARDNO' => $this->getCardNumber(''),
            'BIN' => $this->getCardBin(''),
            'PM' => 'CreditCard', // @todo Get correct PM value
            'ED' => $this->getCardExpiryDate(''),
        ];
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
        return isset($this->data[$offset]);
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
        unset($this->data[$offset]);
    }

    /**
     * Implementation of \ArrayAccess::offsetGet()
     *
     * @param string $offset
     * @return mixed
     * @link http://www.php.net/manual/en/arrayaccess.offsetget.php
     */
    public function offsetGet(string $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    /**
     * Set/Get attribute wrapper
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed
     * @throws Exception
     */
    public function __call($method, $arguments)
    {
        $key = lcfirst(mb_substr($method, 3, mb_strlen($method, 'UTF-8'), 'UTF-8'));
        switch (mb_substr($method, 0, 3, 'UTF-8')) {
            case 'get':
                return $this->data[$key] ?? null;
            case 'set':
                $this->data[$key] = $arguments[0];
                return $this;
            case 'uns':
                unset($this->data[$key]);
                return $this;
            case 'has':
                return isset($this->data[$key]);
        }

        throw new Exception(sprintf('Invalid method %s::%s', get_class($this), $method));
    }
}
