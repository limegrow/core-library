<?php

namespace IngenicoClient;

use IngenicoClient\PaymentMethod\PaymentMethodInterface;

interface OpenInvoiceInterface
{
    /**
     * Get Missing or Invalid Order's fields.
     *
     * @param mixed $orderId Order Id
     * @param PaymentMethod\PaymentMethod $paymentMethod PaymentMethod Instance
     * @param array $fields Order fields
     * @return array
     */
    public function getMissingOrderFields(mixed $orderId, PaymentMethodInterface $paymentMethod, array $fields = []): array;

    /**
     * Validate OpenInvoice Additional Fields on Checkout Session
     *
     * @param $orderId
     * @return array
     * @throws Exception
     */
    public function validateOpenInvoiceCheckoutAdditionalFields($orderId, PaymentMethodInterface $paymentMethod): array;

    /**
     * Initiate Open Invoice Payment
     *
     * @throws \Exception
     */
    public function initiateOpenInvoicePayment(mixed $orderId, Alias $alias, array $fields = []);
}
