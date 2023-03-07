<?php

namespace IngenicoClient\PaymentMethod;

class Aurore extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'aurore';

    /**
     * ID Code
     * @var string
     */
    protected string $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected string $name = 'Aurore';

    /**
     * Logo
     * @var string
     */
    protected string $logo = 'aurore.png';

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
    protected string $brand = 'Aurore';

    /**
     * Countries
     * @var array
     */
    protected array $countries = [
        'FR' => [
            'popularity' => 100
        ],
    ];

    /**
     * Is Security Mandatory
     * @var bool
     */
    protected bool $is_security_mandatory = true;

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
