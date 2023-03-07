<?php

namespace IngenicoClient\PaymentMethod;

class CarteBancaire extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'cb';

    /**
     * ID Code
     * @var string
     */
    protected string $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected string $name = 'Carte Bancaire';

    /**
     * Logo
     * @var string
     */
    protected string $logo = 'carte_bancaire.svg';

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
    protected string $brand = 'CB';

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
     * Is Security Mandatory
     * @var bool
     */
    protected bool $is_security_mandatory = true;
}
