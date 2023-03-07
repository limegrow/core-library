<?php

namespace IngenicoClient\PaymentMethod;

class Visa extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'visa';

    /**
     * ID Code
     * @var string
     */
    protected string $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected string $name = 'Visa';

    /**
     * Logo
     * @var string
     */
    protected string $logo = 'visa.svg';

    /**
     * Category
     * @var string
     */
    protected string $category = 'card';

    /**
     * Payment Method
     * @var string
     */
    protected string $pm = 'CreditCard';

    /**
     * Brand
     * @var string
     */
    protected string $brand = 'VISA';

    /**
     * Countries
     * @var array
     */
    protected array $countries = [
        'AT' => [
            'popularity' => 100
        ],
        'BE' => [
            'popularity' => 80
        ],
        'FR' => [
            'popularity' => 100
        ],
        'DE' => [
            'popularity' => 100
        ],
        'IT' => [
            'popularity' => 100
        ],
        'LU' => [
            'popularity' => 100
        ],
        'NL' => [
            'popularity' => 40
        ],
        'PT' => [
            'popularity' => 100
        ],
        'ES' => [
            'popularity' => 100
        ],
        'CH' => [
            'popularity' => 100
        ],
        'GB' => [
            'popularity' => 100
        ]
    ];

    /**
     * Is Security Mandatory
     * @var bool
     */
    protected bool $is_security_mandatory = false;
}
