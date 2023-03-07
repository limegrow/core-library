<?php

namespace IngenicoClient;

use Ogone\FlexCheckout\FlexCheckoutPaymentRequest;

interface FlexCheckoutInterface
{
    /**
     * Get Inline payment method URL
     *
     * @param $orderId
     * @param Alias $alias
     * @return string
     */
    public function getInlineIFrameUrl($orderId, Alias $alias): string;

    /**
     * Get Flex Checkout Payment Request Instance
     *
     */
    public function getFlexCheckoutPaymentRequest(Order $order, Alias $alias): FlexCheckoutPaymentRequest;
}
