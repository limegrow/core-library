<?php

namespace IngenicoClient\PaymentMethod;

class Ideal extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'ideal';

    /**
     * ID Code
     * @var string
     */
    protected string $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected string $name = 'iDEAL';

    /**
     * Logo
     * @var string
     */
    protected string $logo = 'ideal.svg';

    /**
     * Category
     * @var string
     */
    protected string $category = 'real_time_banking';

    /**
     * Payment Method
     * @var string
     */
    protected string $pm = 'iDEAL';

    /**
     * Brand
     * @var string
     */
    protected string $brand = 'iDEAL';

    /**
     * Countries
     * @var array
     */
    protected array $countries = [
        'NL' => [
            'popularity' => 100
        ]
    ];

    /**
     * Is support Redirect only
     * @var bool
     */
    protected bool $is_redirect_only = true;
}
