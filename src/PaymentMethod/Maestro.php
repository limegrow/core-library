<?php

namespace IngenicoClient\PaymentMethod;

class Maestro extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'maestro';

    /**
     * ID Code
     * @var string
     */
    protected string $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected string $name = 'Maestro';

    /**
     * Logo
     * @var string
     */
    protected string $logo = 'maestro.svg';

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
    protected string $brand = 'Maestro';

    /**
     * Countries
     * @var array
     */
    protected array $countries = [
        'AT' => [
            'popularity' => 20
        ],
        'BE' => [
            'popularity' => 40
        ],
        'FR' => [
            'popularity' => 20
        ],
        'DE' => [
            'popularity' => 20
        ],
        'IT' => [
            'popularity' => 20
        ],
        'LU' => [
            'popularity' => 20
        ],
        'PT' => [
            'popularity' => 20
        ],
        'ES' => [
            'popularity' => 20
        ],
        'GB' => [
            'popularity' => 20
        ]
    ];
}
