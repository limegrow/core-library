<?php

namespace IngenicoClient;

class PaymentMethod implements \Countable, \IteratorAggregate
{
    /**
     * Holds all the Payment Methods with their properties
     * @var array
     */
    private array $payment_methods = [];

    /**
     * Category Labels
     */
    const CATEGORY_LABELS = [
        'card' => 'Cards',
        'real_time_banking' => 'Real-time Banking',
        'e_wallet' => 'e-Wallet',
        'prepaid_vouchers' => 'Prepaid Vouchers',
        'open_invoice' => 'Open Invoice',
        'klarna' => 'Klarna'
    ];

    /**
     * PaymentMethod constructor.
     */
    public function __construct()
    {
        $directory = __DIR__ . DIRECTORY_SEPARATOR . 'PaymentMethod';
        $files = scandir($directory);
        foreach ($files as $file) {
            $file = $directory . DIRECTORY_SEPARATOR . $file;

            $info = pathinfo($file);
            if (!isset($info['extension']) || $info['extension'] !== 'php') {
                continue;
            }

            $payment_name = basename($info['filename'], '.php');
            if (in_array($payment_name, ['PaymentMethod', 'PaymentMethodInterface'])) {
                continue;
            }

            // Load class
            $class_name = '\\' . __NAMESPACE__ . '\\PaymentMethod\\' .  $payment_name;
            if (!class_exists($class_name, false)) {
                require_once $file;
            }

            if (class_exists($class_name, false)) {
                /** @var PaymentMethod\PaymentMethod $instance */
                $instance = new $class_name();
                $instance->setCategoryName(self::CATEGORY_LABELS[$instance->getCategory()]);
                $this->payment_methods[$instance->getId()] = $instance;
            }
        }
    }
    /**
     * Return count of Payments Methods.
     * @see \Countable
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->payment_methods);
    }

    /**
     * Returns Payments Methods.
     * Since they are inside an array, we can use the standard array-iterator.
     * @see \IteratorAggregate
     *
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->payment_methods);
    }

    /**
     * Returns Payment Methods as array.
     */
    public function toArray(): array
    {
        return $this->payment_methods;
    }

    /**
     * Get Payment Method by field and value
     */
    public static function find(string $field, mixed $value): array
    {
        $result = [];
        $paymentMethods = new PaymentMethod();

        /** @var PaymentMethod\PaymentMethod $paymentMethod */
        foreach ($paymentMethods as $paymentMethod) {
            if ($paymentMethod->getDataUsingMethod($field) === $value) {
                $result[$paymentMethod->getId()] = $paymentMethod;
            }
        }

        return $result;
    }

    /**
     * Get All Countries of Payment Methods.
     * Returns array like ['DE' => 'Germany']
     */
    public static function getAllCountries(): array
    {
        $countries = [];
        $paymentMethods = new PaymentMethod();

        /** @var PaymentMethod\PaymentMethod $payment_method */
        foreach ($paymentMethods as $paymentMethod) {
            $_countries = $paymentMethod->getCountries();

            foreach ($_countries as $isoCode => $popularity) {
                if (!isset($countries[$isoCode])) {
                    $countries[$isoCode] = IngenicoCoreLibrary::getCountryByCode($isoCode);
                }
            }
        }

        return $countries;
    }

    /**
     * Returns countries array match between each country and its PMs
     * @deprecated
     */
    public function getCountriesPaymentMethods(): array
    {
        $country_payment_methods = [];

        /** @var PaymentMethod\PaymentMethod $payment_method */
        foreach ($this->payment_methods as $payment_method) {
            $category = $payment_method->getCategory();
            $countries = $payment_method->getCountries();
            foreach ($countries as $iso_code => $payment_method_country) {
                if (!isset($country_payment_methods[$iso_code])) {
                    $country_payment_methods[$iso_code] = [];
                }

                if (!isset($country_payment_methods[$iso_code][$category])) {
                    $country_payment_methods[$iso_code][$category] = [];
                }

                $country_payment_methods[$iso_code][$category][] = $payment_method->getId();
            }
        }
        return $country_payment_methods;
    }

    /**
     * Returns Payment methods array
     */
    public static function getPaymentMethods(): array
    {
        return (new PaymentMethod)->toArray();
    }

    /**
     * Get payment methods by country ISO code
     * @param $country_iso_code
     *@deprecated
     */
    public function getPaymentMethodsByIsoCode($country_iso_code): array
    {
        $payment_methods_ids = [];

        /** @var PaymentMethod\PaymentMethod $payment_method */
        foreach ($this->payment_methods as $payment_method) {
            $countries = $payment_method->getCountries();
            if (in_array($country_iso_code, array_values($countries))) {
                $payment_methods_ids[] = $payment_method->getId();
            }
        }

        return array_unique($payment_methods_ids);
    }

    /**
     * Get payment methods by Category
     *
     * @param $category
     */
    public static function getPaymentMethodsByCategory($category): array
    {
        return self::find('category', $category);
    }

    /**
     * Get Payment Method by Brand
     *
     * @param IngenicoCoreLibrary|null $coreLibrary
     */
    public static function getPaymentMethodByBrand(string $brand, IngenicoCoreLibrary $coreLibrary = null): bool|PaymentMethod\PaymentMethod
    {
        // Workaround for Afterpay/Klarna
        if (in_array($brand, ['Open Invoice DE', 'Open Invoice NL'])) {
            if ($coreLibrary instanceof IngenicoCoreLibrary) {
                // If core library defined then use configuration to recognize the payment method
                $selected = $coreLibrary->getConfiguration()->getSelectedPaymentMethods();
                if (in_array('klarna', $selected)) {
                    return self::getPaymentMethodById('klarna');
                } elseif (in_array('afterpay', $selected)) {
                    return self::getPaymentMethodById('afterpay');
                } else {
                    return false;
                }
            }

            return self::getPaymentMethodById('klarna');
        }

        $paymentMethods = new PaymentMethod();

        /** @var PaymentMethod\PaymentMethod $paymentMethod */
        foreach ($paymentMethods as $paymentMethod) {
            if (strtolower($paymentMethod->getBrand()) === strtolower($brand)) {
                return $paymentMethod;
            }
        }

        return false;
    }

    /**
     * Get Payment Method by Id
     *
     * @param $id
     */
    public static function getPaymentMethodById($id): bool|PaymentMethod\PaymentMethod
    {
        $paymentMethods = new PaymentMethod();

        /** @var PaymentMethod\PaymentMethod $paymentMethod */
        foreach ($paymentMethods as $paymentMethod) {
            if (strtolower($paymentMethod->getId()) === strtolower((string) $id)) {
                return $paymentMethod;
            }
        }

        return false;
    }

    /**
     * Get Payment Categories
     */
    public static function getPaymentCategories(): array
    {
        return self::CATEGORY_LABELS;
    }
}
