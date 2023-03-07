<?php

namespace IngenicoClient\PaymentMethod;

class Twint extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'twint';

    /**
     * ID Code
     * @var string
     */
    protected string $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected string $name = 'Twint';

    /**
     * Logo
     * @var string
     */
    protected string $logo = 'twint.svg';

    /**
     * Category
     * @var string
     */
    protected string $category = 'real_time_banking';

    /**
     * Payment Method
     * @var string
     */
    protected string $pm = 'TWINT';

    /**
     * Brand
     * @var string
     */
    protected string $brand = 'TWINT';

    /**
     * Countries
     * @var array
     */
    protected array $countries = [
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
     * Is support Redirect only
     * @var bool
     */
    protected bool $is_redirect_only = true;

    /**
     * Is support Two phase flow
     * @var bool
     */
    protected bool $two_phase_flow = false;
}
