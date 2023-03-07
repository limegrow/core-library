<?php

namespace IngenicoClient\PaymentMethod;

class DirectEbankingAT extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'direct_ebankingat';

    /**
     * ID Code
     * @var string
     */
    protected string $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected string $name = 'Sofort Überweisung (AT)';

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
    protected string $pm = 'DirectEbankingAT';

    /**
     * Brand
     * @var string
     */
    protected string $brand = 'DirectEbankingAT';

    /**
     * Countries
     * @var array
     */
    protected array $countries = [
        'AT' => [
            'popularity' => 20
        ]
    ];

    /**
     * Is support Redirect only
     * @var bool
     */
    protected bool $is_redirect_only = true;
}
