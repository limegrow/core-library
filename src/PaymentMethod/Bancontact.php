<?php

namespace IngenicoClient\PaymentMethod;

class Bancontact extends PaymentMethod implements PaymentMethodInterface
{
    const CODE = 'bancontact';

    /**
     * ID Code
     * @var string
     */
    protected string $id = self::CODE;

    /**
     * Name
     * @var string
     */
    protected string $name = 'Bancontact';

    /**
     * Logo
     * @var string
     */
    protected string $logo = 'bancontact.svg';

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
    // @todo Bancontact returns BRAND="Bancontact/Mister Cash". Expects: "BCMC"
    protected string $brand = 'BCMC';

    /**
     * Countries
     * @var array
     */
    protected array $countries = [
        'BE' => [
            'popularity' => 100
        ]
    ];

    /**
     * Is Security Mandatory
     * @var bool
     */
    protected bool $is_security_mandatory = true;

    /**
     * Is support Two phase flow
     * @var bool
     */
    protected bool $two_phase_flow = false;
}
