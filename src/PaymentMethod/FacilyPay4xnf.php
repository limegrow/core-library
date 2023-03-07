<?php

namespace IngenicoClient\PaymentMethod;

use IngenicoClient\PaymentMethod\Abstracts\Oney;

class FacilyPay4xnf extends Oney implements PaymentMethodInterface
{
    const CODE = 'facilypay4xnf';

    /**
     * ID Code
     * @var string
     */
    protected string $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected string $name = 'FacilyPay 4x sans frais';

    /**
     * Logo
     * @var string
     */
    protected string $logo = 'oney.png';

    /**
     * Category
     * @var string
     */
    protected string $category = 'real_time_banking';

    /**
     * Payment Method
     * @var string
     */
    protected string $pm = 'FACILYPAY4XNF';

    /**
     * Brand
     * @var string
     */
    protected string $brand = 'FACILYPAY4XNF';

    /**
     * Countries
     * @var array
     */
    protected array $countries = [
        'FR' => [
            'popularity' => 20
        ],
    ];

    /**
     * Is support Three phase flow.
     * 3-step payment (waiting+authorisation+debit)
     * @var bool
     */
    protected bool $three_phase_flow = true;

    /**
     * Is support Redirect only
     * @var bool
     */
    protected bool $is_redirect_only = true;

    /**
     * Defines if this payment method requires additional data to be sent with the request.
     * @var bool
     */
    protected bool $additional_data_required = true;

    /**
     * Defines if this payment method requires order line items to be sent with the request
     * @var bool
     */
    protected bool $order_line_items_required = true;
}
