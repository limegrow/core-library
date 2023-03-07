<?php

namespace IngenicoClient\PaymentMethod;

use IngenicoClient\PaymentMethod\Abstracts\Klarna as KlarnaAbstract;

class KlarnaPayNow extends KlarnaAbstract
{
    const CODE = 'klarna_paynow';

    /**
     * ID Code
     * @var string
     */
    protected string $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected string $name = 'Klarna Pay Now';

    /**
     * Logo
     * @var string
     */
    protected string $logo = 'https://x.klarnacdn.net/payment-method/assets/badges/generic/klarna.svg';

    /**
     * Category
     * @var string
     */
    protected string $category = 'klarna';

    /**
     * Payment Method
     * @var string
     */
    protected string $pm = 'KLARNA_PAYNOW';

    /**
     * Brand
     * @var string
     */
    protected string $brand = 'KLARNA_PAYNOW';

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
        'CH' => [
            'popularity' => 100
        ],
        'DE' => [
            'popularity' => 100
        ],
        'FI' => [
            'popularity' => 80
        ],
        'NL' => [
            'popularity' => 100
        ],
        'SE' => [
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
    protected bool $order_line_items_required = true;

    /**
     * Defines if this payment method requires additional data to be sent with the request.
     * @var bool
     */
    protected bool $additional_data_required = true;
}
