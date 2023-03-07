<?php

namespace IngenicoClient\PaymentMethod;

class BankTransfer extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'bank_transfer';

    /**
     * ID Code
     * @var string
     */
    protected string $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected string $name = 'Bank Transfer';

    /**
     * Logo
     * @var string
     */
    protected string $logo = 'bank_transfer.svg';

    /**
     * Category
     * @var string
     */
    protected string $category = 'real_time_banking';

    /**
     * Payment Method
     * @var string
     */
    protected string $pm = 'Bank transfer';

    /**
     * Brand
     * @var string
     */
    protected string $brand = 'Bank transfer';

    /**
     * Countries
     * @var array
     */
    protected array $countries = [
        'AT' => [
            'popularity' => 40
        ],
        'BE' => [
            'popularity' => 40
        ],
        'DE' => [
            'popularity' => 40
        ],
        'FR' => [
            'popularity' => 40
        ],
        'NL' => [
            'popularity' => 40
        ]
    ];

    /**
     * Is support Redirect only
     * @var bool
     */
    protected bool $is_redirect_only = true;

    /**
     * Transaction codes that indicate capturing.
     * @var array
     */
    protected array $direct_sales_success_code = [];

    /**
     * Transaction codes that indicate authorization.
     * @var array
     */
    protected array $auth_mode_success_code = [];
}
