<?php

namespace IngenicoClient\PaymentMethod;

class Ing extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'ing';

    /**
     * ID Code
     * @var string
     */
    protected string $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected string $name = 'ING Home\'Pay';

    /**
     * Logo
     * @var string
     */
    protected string $logo = 'ing.svg';

    /**
     * Category
     * @var string
     */
    protected string $category = 'real_time_banking';

    /**
     * Payment Method
     * @var string
     */
    protected string $pm = 'ING HomePay';

    /**
     * Brand
     * @var string
     */
    protected string $brand = 'ING HomePay';

    /**
     * Countries
     * @var array
     */
    protected array $countries = [
        'BE' => [
            'popularity' => 40
        ]
    ];

    /**
     * Is support Redirect only
     * @var bool
     */
    protected bool $is_redirect_only = true;
}
