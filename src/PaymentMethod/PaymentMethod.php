<?php

namespace IngenicoClient\PaymentMethod;

use IngenicoClient\Exception;

/**
 * Class PaymentMethod
 * @method mixed getIFrameUrl()
 * @method $this setIFrameUrl($url)
 * @method bool getOrderLineItemsRequired();
 * @method $this setOrderLineItemsRequired(bool $value);
 * @method bool getAdditionalDataRequired();
 * @method $this setAdditionalDataRequired(bool $value);
 * @method array getCommonFields();
 * @method $this setCommonFields(array $value);
 *
 * @package IngenicoClient\PaymentMethod
 */
class PaymentMethod implements \ArrayAccess, PaymentMethodInterface
{
    /**
     * Checkout Types
     */
    const CHECKOUT_B2C = 'b2c';
    const CHECKOUT_B2B = 'b2b';

    /**
     * Customer Field Types
     */
    const TYPE_TEXT = 'text';
    const TYPE_RADIO = 'radio';
    const TYPE_NUMBERIC = 'number';
    const TYPE_DATE = 'date';

    /**
     * ID Code
     * @var string
     */
    protected string $id;

    /**
     * Name
     * @var string
     */
    protected string $name;

    /**
     * Logo
     * @var string
     */
    protected string $logo;

    /**
     * Category
     * @var string
     */
    protected string $category;

    /**
     * Category Name
     * @var string
     */
    protected string $category_name;

    /**
     * Payment Method
     * @var string
     */
    protected string $pm;

    /**
     * Brand
     * @var string
     */
    protected string $brand;

    /**
     * Countries
     * @var array
     */
    protected array $countries;

    /**
     * Is Security Mandatory
     * @var bool
     */
    protected bool $is_security_mandatory = false;

    /**
     * Credit Debit Flag (C or D)
     * @var string
     */
    protected string $credit_debit;

    /**
     * Is support Redirect only
     * @var bool
     */
    protected bool $is_redirect_only = false;

    /**
     * Defines if this payment method requires order line items to be sent with the request
     * @var bool
     */
    protected bool $order_line_items_required = false;

    /**
     * Is support Two phase flow
     * @var bool
     */
    protected bool $two_phase_flow = true;

    /**
     * Defines if this payment method requires additional data to be sent with the request.
     * Like OpenInvoice/Klarna/Afterpay
     * @var bool
     */
    protected bool $additional_data_required = false;

    /**
     * Defines if this payment method should be hidden from the checkout or listing
     * @var bool
     */
    protected bool $is_hidden = false;

    /**
     * Transaction codes that indicate capturing.
     * @var array
     */
    protected array $direct_sales_success_code = [9];

    /**
     * Transaction codes that indicate authorization.
     * @var array
     */
    protected array $auth_mode_success_code = [5];

    /**
     * Different PM values per different countries
     * @var array
     */
    protected array $pm_per_country = [];

    /**
     * Different Brand values per different countries
     * @var array
     */
    protected array $brand_per_country = [];

    /**
     * Common fields
     * @var array
     * @SuppressWarnings("Duplicates")
     */
    protected array $common_fields = [];

    /**
     * Additional fields
     * @var array
     * @SuppressWarnings("Duplicates")
     */
    protected array $additional_fields = [
        'b2c' => [],
        'b2b' => []
    ];

    /**
     * Missing Fields
     * @var array
     */
    private array $missing_fields = [];

    /**
     * PaymentMethod constructor.
     * @param array|null $data
     */
    public function __construct(array $data = null)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    /**
     * Get ID
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get Name
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get Category
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * Set Category Name
     * @param string $categoryName
     * @return $this
     */
    public function setCategoryName(string $categoryName): static
    {
        $this->category_name = $categoryName;

        return $this;
    }

    /**
     * Get Category Name
     * @return string
     */
    public function getCategoryName(): string
    {
        return $this->category_name;
    }

    /**
     * Get PM
     * @return string
     */
    public function getPM(): string
    {
        return $this->pm;
    }

    /**
     * Set PM
     * @param string $pm
     * @return $this
     */
    public function setPM(string $pm): static
    {
        $this->pm = $pm;

        return $this;
    }

    /**
     * Get Brand
     * @return string
     */
    public function getBrand(): string
    {
        return $this->brand;
    }

    /**
     * Set Brand
     * @param string $brand
     * @return $this
     */
    public function setBrand(string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Set PM for Country
     * @param string $country
     * @param string $pm
     * @return $this
     */
    public function setPMByCountry(string $country, string $pm): static
    {
        $this->pm_per_country[$country] = $pm;

        return $this;
    }

    /**
     * Get PM by Country Code
     * @param string $country
     * @return string|null
     */
    public function getPMByCountry(string $country): ?string
    {
        if (array_key_exists($country, $this->pm_per_country)) {
            return $this->pm_per_country[$country];
        }

        return null;
    }

    /**
     * Set Brand for Country
     * @param string $country
     * @param string $brand
     * @return $this
     */
    public function setBrandByCountry(string $country, string $brand): static
    {
        $this->brand_per_country[$country] = $brand;

        return $this;
    }

    /**
     * Get Brand by Country Code
     * @param string $country
     * @return string|null
     */
    public function getBrandByCountry(string $country): ?string
    {
        if (array_key_exists($country, $this->brand_per_country)) {
            return $this->brand_per_country[$country];
        }

        return null;
    }

    /**
     * Set Additional Fields
     * @param $checkout_type
     * @param array $fields
     * @return $this
     */
    public function setAdditionalFields($checkout_type, array $fields = []): static
    {
        $this->additional_fields = $fields;

        return $this;
    }

    /**
     * Get Additional Fields
     * @param string $checkout_type
     * @return array
     */
    public function getAdditionalFields(string $checkout_type): array
    {
        return $this->additional_fields;
    }

    /**
     * Get Expected Fields
     * @param $checkout_type
     * @return array
     */
    public function getExpectedFields($checkout_type): array
    {
        return array_merge($this->getCommonFields(), $this->getAdditionalFields($checkout_type));
    }

    /**
     * Set Missing Fields
     * @param array $fields
     */
    public function setMissingFields(array $fields)
    {
        $this->missing_fields = $fields;
    }

    /**
     * Get Missing Fields
     * @return array
     */
    public function getMissingFields(): array
    {
        return $this->missing_fields;
    }

    /**
     * Get Countries
     * @return array
     */
    public function getCountries(): array
    {
        return $this->countries;
    }

    /**
     * Is Security Mandatory
     * @return bool
     */
    public function isSecurityMandatory(): bool
    {
        return $this->is_security_mandatory;
    }

    /**
     * Get Credit Debit Flag
     * @return string
     */
    public function getCreditDebit(): string
    {
        return $this->credit_debit;
    }

    /**
     * Is support Redirect only
     * @return bool
     */
    public function isRedirectOnly(): bool
    {
        return $this->is_redirect_only;
    }

    /**
     * Is Hidden
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->is_hidden;
    }

    /**
     * Returns codes that indicate capturing.
     * @return array
     */
    public function getDirectSalesSuccessCode(): array
    {
        return $this->direct_sales_success_code;
    }

    /**
     * Returns codes that indicate authorization.
     * @return array
     */
    public function getAuthModeSuccessCode(): array
    {
        return $this->auth_mode_success_code;
    }

    /**
     * Is support Two Phase Flow
     * @return bool
     */
    public function isTwoPhaseFlow(): bool
    {
        return $this->two_phase_flow;
    }

    /**
     * Get Logo
     * @return string
     */
    public function getEmbeddedLogo(): string
    {
        if (filter_var($this->logo, FILTER_VALIDATE_URL) !== false) {
            return $this->logo;
        }

        $file = realpath(__DIR__ . '/../../assets/images/payment_logos/' . $this->logo);
        if (file_exists($file)) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $mime = mime_content_type($file);

            if ('svg' === $extension) {
                $mime = 'image/svg+xml';
            }

            if (str_contains($mime, 'image')) {
                $contents = file_get_contents($file);
                return sprintf('data:%s;base64,%s', $mime, base64_encode($contents));
            }
        }

        return '';
    }

    /**
     * Get object data by key with calling getter method
     *
     * @param string $key
     * @param mixed|null $args
     * @return mixed
     */
    public function getDataUsingMethod(string $key, mixed $args = null): mixed
    {
        $method = 'get' . $this->camelize($key);
        return $this->$method($args);
    }

    /**
     * Get data
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
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
                return property_exists($this, $key) ? $this->$key : null;
            case 'set':
                $key = $this->underscore(substr($method, 3));
                $this->$key = $args[0] ?? null;
                return $this;
            case 'uns':
                $key = $this->underscore(substr($method, 3));
                unset($this->$key);
                return $this;
            case 'has':
                $key = $this->underscore(substr($method, 3));
                return property_exists($this, $key);
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
        $this->$offset = $value;
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
        return property_exists($this, $offset);
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
        if (property_exists($this, $offset)) {
            unset($this->$offset);
        }
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
        if (property_exists($this, $offset)) {
            return $this->$offset;
        }

        return null;
    }

    /**
     * Converts field names for setters and getters
     *
     * @param string $name
     * @return string
     */
    protected function underscore(string $name): string
    {
        return strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
    }

    /**
     * Camelize string
     * Example: super_string to superString
     *
     * @param $name
     * @return string
     */
    protected function camelize($name): string
    {
        return $this->ucWords($name, '');
    }

    /**
     * Tiny function to enhance functionality of ucwords
     *
     * Will capitalize first letters and convert separators if needed
     *
     * @param string $str
     * @param string $destSep
     * @param string $srcSep
     * @return string
     */
    protected function ucWords(string $str, string $destSep = '_', string $srcSep = '_'): string
    {
        return str_replace(' ', $destSep, ucwords(str_replace($srcSep, ' ', $str)));
    }
}
