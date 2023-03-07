<?php

namespace IngenicoClient\PaymentMethod;

class Cbc extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'cbc';

    /**
     * ID Code
     * @var string
     */
    protected string $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected string $name = 'CBC';

    /**
     * Logo
     * @var string
     */
    protected string $logo = 'cbc.svg';

    /**
     * Category
     * @var string
     */
    protected string $category = 'real_time_banking';

    /**
     * Payment Method
     * @var string
     */
    protected string $pm = 'CBC Online';

    /**
     * Brand
     * @var string
     */
    protected string $brand = 'CBC Online';

    /**
     * Countries
     * @var array
     */
    protected array $countries = [
        'BE' => [
            'popularity' => 20
        ]
    ];

    /**
     * Is support Redirect only
     * @var bool
     */
    protected bool $is_redirect_only = true;
}
