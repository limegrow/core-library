<?php

namespace IngenicoClient\PaymentMethod;

interface PaymentMethodInterface
{
    /**
     * Get ID
     * @return string
     */
    public function getId(): string;

    /**
     * Get Name
     * @return string
     */
    public function getName(): string;

    /**
     * Get Category
     * @return string
     */
    public function getCategory(): string;

    /**
     * Get Category Name
     * @return string
     */
    public function getCategoryName(): string;

    /**
     * Get PM
     * @return string
     */
    public function getPM(): string;

    /**
     * Get Brand
     * @return string
     */
    public function getBrand(): string;

    /**
     * Get Countries
     * @return array
     */
    public function getCountries(): array;

    /**
     * Is Security Mandatory
     * @return bool
     */
    public function isSecurityMandatory(): bool;

    /**
     * Get Credit Debit Flag
     * @return string
     */
    public function getCreditDebit(): string;

    /**
     * Is support Redirect only
     * @return bool
     */
    public function isRedirectOnly(): bool;
}
