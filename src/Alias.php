<?php

namespace IngenicoClient;

use IngenicoClient\PaymentMethod\Afterpay;
use IngenicoClient\PaymentMethod\Klarna;

/**
 * Class Alias
 * @method int getAliasId()
 * @method int getCustomerId()
 * @method $this setCustomerId($value)
 * @method string getAlias()
 * @method $this setAlias($value)
 * @method string getEd()
 * @method $this setEd($value)
 * @method string getBrand()
 * @method $this setBrand($value)
 * @method string getCardno()
 * @method $this setCardno($value)
 * @method string getCn()
 * @method $this setCn($value)
 * @method string getBin()
 * @method $this setBin($value)
 * @method string getPm()
 * @method $this setPm($value)
 * @method string getOperation()
 * @method $this setOperation($operation)
 * @method string getUsage()
 * @method $this setUsage($usage)
 * @method string getIsShouldStoredPermanently()
 * @method $this setIsShouldStoredPermanently($value)
 * @method string getIsPreventStoring()
 * @method $this setIsPreventStoring($value)
 * @method string getForceSecurity()
 * @method $this setForceSecurity($value)
 * @method string getPaymentId()
 * @method $this setPaymentId($value)
 *
 * @package IngenicoClient
 */
class Alias extends Data
{
    const OPERATION_BY_MERCHANT = 'BYMERCHANT';
    const OPERATION_BY_PSP = 'BYPSP';

    /**
     * Alias constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $data = array_change_key_case($data);
        $this->setData($data);
    }

    /**
     * Pseudo for getAliasId()
     * @return int
     */
    public function getId(): int
    {
        return $this->getAliasId();
    }

    /**
     * Get Formatted Name
     * @return string
     */
    public function getName(): string
    {
        $brand = $this->getBrand() === 'CB' ? 'Carte Bancaire' : $this->getBrand();

        // @todo Translate that
        return sprintf(
            '%s ends with %s, expires on %s/%s',
            $brand,
            substr($this->getCardno(), -4, 4),
            substr($this->getEd(), 0, 2),
            substr($this->getEd(), 2, 4)
        );
    }

    /**
     * Get Payment Method Instance
     * @return PaymentMethod\PaymentMethod
     */
    public function getPaymentMethod(): PaymentMethod\PaymentMethod
    {
        if ($this->getPaymentId() && $paymentMethod = PaymentMethod::getPaymentMethodById($this->getPaymentId())) {
            // Map payment_id property as PaymentMethod->id
            if (in_array($paymentMethod->getId(), [Afterpay::CODE, Klarna::CODE])) {
                $paymentMethod->setPM($this->getPm())
                    ->setBrand($this->getBrand());
            }

            return $paymentMethod;
        } elseif ('Bancontact/Mister Cash' === $this->getBrand()) {
            // Workaround for Bancontact
            return PaymentMethod::getPaymentMethodByBrand('BCMC');
        } elseif ($this->getBrand() && $paymentMethod = PaymentMethod::getPaymentMethodByBrand($this->getBrand())) {
            // Map brand property as PaymentMethod->brand
            return $paymentMethod;
        }

        return new PaymentMethod\PaymentMethod();
    }

    /**
     * Get Logo
     * @return string
     */
    public function getEmbeddedLogo(): string
    {
        return $this->getPaymentMethod()->getEmbeddedLogo();
    }

    /**
     * Get Alias instance of SDK
     * @return \Ogone\Ecommerce\Alias
     */
    public function exchange(): \Ogone\Ecommerce\Alias
    {
        return new \Ogone\Ecommerce\Alias($this->getAlias(), $this->getOperation(), $this->getUsage());
    }

    /**
     * Get Alias instance of DirectLink SDK.
     *
     * @return \Ogone\DirectLink\Alias
     */
    public function exchangeDirectLink(): \Ogone\DirectLink\Alias
    {
        return (new \Ogone\DirectLink\Alias(
            $this->getAlias(),
            $this->getCardno(),
            $this->getCardno(),
            $this->getEd()
        ))->setAliasOperation($this->getOperation());
    }
}
