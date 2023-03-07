<?php

namespace IngenicoClient\PaymentMethod;

class InterSolve extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'intersolve';

    /**
     * ID Code
     * @var string
     */
    protected string $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected string $name = 'InterSolve';

    /**
     * Logo
     * @var string
     */
    protected string $logo = 'intersolve.png';

    /**
     * Category
     * @var string
     */
    protected string $category = 'prepaid_vouchers';

    /**
     * Payment Method
     * @var string
     */
    protected string $pm = 'Intersolve';

    /**
     * Brand
     * @var string
     */
    protected string $brand = 'Intersolve';

    /**
     * Countries
     * @var array
     */
    protected array $countries = [
        'NL' => [
            'popularity' => 20
        ],
    ];

    /**
     * Is support Redirect only
     * @var bool
     */
    protected bool $is_redirect_only = true;
}
