<?php

namespace IngenicoClient\PaymentMethod;

class Giropay extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'giropay';

    /**
     * ID Code
     * @var string
     */
    protected string $id = 'giropay';

    /**
     * Name
     * @var string
     */
    protected string $name = 'Giropay';

    /**
     * Logo
     * @var string
     */
    protected string $logo = 'giropay.svg';

    /**
     * Category
     * @var string
     */
    protected string $category = 'real_time_banking';

    /**
     * Payment Method
     * @var string
     */
    protected string $pm = 'giropay';

    /**
     * Brand
     * @var string
     */
    protected string $brand = 'giropay';

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
