<?php

namespace IngenicoClient\PaymentMethod;

class SofortUberweisung extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'sofort_uberweisung';

    /**
     * ID Code
     * @var string
     */
    protected string $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected string $name = 'Sofort Überweisung';

    /**
     * Logo
     * @var string
     */
    protected string $logo = 'sofort_uberweisung.svg';

    /**
     * Category
     * @var string
     */
    protected string $category = 'real_time_banking';

    /**
     * Payment Method
     * @var string
     */
    protected string $pm = 'DirectEbanking';

    /**
     * Brand
     * @var string
     */
    protected string $brand = 'Sofort Uberweisung';

    /**
     * Countries
     * @var array
     */
    protected array $countries = [
        'DE' => [
            'popularity' => 20
        ]
    ];

    /**
     * Is support Redirect only
     * @var bool
     */
    protected bool $is_redirect_only = true;
}
