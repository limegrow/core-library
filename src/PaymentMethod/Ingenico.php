<?php

namespace IngenicoClient\PaymentMethod;

class Ingenico extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'ingenico';

    /**
     * ID Code
     * @var string
     */
    protected string $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected string $name = 'Ingenico ePayments';

    /**
     * Logo
     * @var string
     */
    protected string $logo = 'ingenico.gif';

    /**
     * Category
     * @var string
     */
    protected string $category = 'real_time_banking';

    /**
     * Payment Method
     * @var string
     */
    protected string $pm = '';

    /**
     * Brand
     * @var string
     */
    protected string $brand = '';

    /**
     * Countries
     * @var array
     */
    protected array $countries = [
        'AT' => [
            'popularity' => 100
        ],
        'BE' => [
            'popularity' => 100
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
     * Defines if this payment method requires order line items to be sent with the request
     * @var bool
     */
    protected bool $order_line_items_required = false;
}
