<?php

namespace IngenicoClient\PaymentMethod;

class DirectEbankingCH extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'direct_ebankingch';

    /**
     * ID Code
     * @var string
     */
    protected string $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected string $name = 'Sofort Überweisung (CH)';

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
    protected string $pm = 'DirectEbankingCH';

    /**
     * Brand
     * @var string
     */
    protected string $brand = 'DirectEbankingCH';

    /**
     * Countries
     * @var array
     */
    protected array $countries = [
        'CH' => [
            'popularity' => 20
        ]
    ];

    /**
     * Is support Redirect only
     * @var bool
     */
    protected bool $is_redirect_only = true;
}
