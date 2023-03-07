<?php

namespace IngenicoClient\PaymentMethod;

class Kbc extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'kbc';

    /**
     * ID Code
     * @var string
     */
    protected string $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected string $name = 'KBC';

    /**
     * Logo
     * @var string
     */
    protected string $logo = 'kbc.svg';

    /**
     * Category
     * @var string
     */
    protected string $category = 'real_time_banking';

    /**
     * Payment Method
     * @var string
     */
    protected string $pm = 'KBC Online';

    /**
     * Brand
     * @var string
     */
    protected string $brand = 'KBC Online';

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
