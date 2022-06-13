<?php

use IngenicoClient\IngenicoCoreLibrary;
use IngenicoClient\ConnectorInterface;
use IngenicoClient\OrderField;
use IngenicoClient\OrderItem;
use IngenicoClient\PaymentMethod\PaymentMethod;
use IngenicoClient\Payment;
use IngenicoClient\Connector;

class TestConnector extends Connector implements ConnectorInterface
{
    /** @var string live|test */
    public $mode;

    /**
     * Connector constructor.
     */
    public function __construct()
    {
        $this->mode = $this->requestSettingsMode() ? 'live' : 'test';
    }

    /**
     * Returns Shopping Cart Extension Id.
     *
     * @return string
     */
    public function requestShoppingCartExtensionId()
    {
        return sprintf(
            'TEST_APP%sV%s',
            str_replace('.', '', '1.0.0'),
            str_replace('.', '', '1.0.0')
        );
    }

    /**
     * Returns activated Ingenico environment mode.
     * False for Test (transactions will go through the Ingenico sandbox).
     * True for Live (transactions will be real).
     *
     * @return bool
     */
    public function requestSettingsMode()
    {
        // Always should be Test mode
        return false;
    }

    /**
     * Returns the complete list of all settings as an array.
     *
     * @param bool $mode False for Test. True for Live.
     *
     * @return array
     */
    public function requestSettings($mode)
    {
        return \IngenicoClient\Configuration::getDefault();
    }

    /**
     * Returns an array with the order details in a standardised way for all connectors.
     * Matches platform specific fields to the fields that are understood by the CL.
     *
     * @param mixed $orderId
     * @return array
     */
    public function requestOrderInfo($orderId = null)
    {
        // Get order items
        $items = [];
        $items[] = [
            OrderItem::ITEM_TYPE => OrderItem::TYPE_PRODUCT,
            OrderItem::ITEM_ID => 'test',
            OrderItem::ITEM_NAME => 'Test name',
            OrderItem::ITEM_DESCRIPTION => 'Test description',
            OrderItem::ITEM_UNIT_PRICE => 125,
            OrderItem::ITEM_QTY => 1,
            OrderItem::ITEM_UNIT_VAT => 25,
            OrderItem::ITEM_VATCODE => 25,
            OrderItem::ITEM_VAT_INCLUDED => 1 // VAT included
        ];

        // @codingStandardsIgnoreStart
        return [
            OrderField::ORDER_ID => $this->requestOrderId(),
            OrderField::PAY_ID => $this->getIngenicoPayIdByOrderId($this->requestOrderId()),
            OrderField::AMOUNT => 125,
            OrderField::TOTAL_CAPTURED => 0,
            OrderField::TOTAL_REFUNDED => 0,
            OrderField::TOTAL_CANCELLED => 0,
            OrderField::CURRENCY => 'EUR',
            OrderField::STATUS => IngenicoCoreLibrary::STATUS_AUTHORIZED,
            OrderField::CREATED_AT => (new \DateTime())->format('Y-m-d H:i:s'), // Y-m-d H:i:s
            OrderField::BILLING_CUSTOMER_TITLE => 'Mr.',
            OrderField::BILLING_COUNTRY => 'France',
            OrderField::BILLING_COUNTRY_CODE => 'FR',
            OrderField::BILLING_ADDRESS1 => '74 rue de la Mare aux Carats',
            OrderField::BILLING_ADDRESS2 => null,
            OrderField::BILLING_ADDRESS3 => null,
            OrderField::BILLING_STREET_NUMBER => null,
            OrderField::BILLING_CITY => 'Montpellier',
            OrderField::BILLING_STATE => 'Languedoc-Roussillon',
            OrderField::BILLING_POSTCODE => '34070',
            OrderField::BILLING_PHONE => '0440575930',
            OrderField::BILLING_EMAIL => 'test@example.com',
            OrderField::BILLING_FIRST_NAME => 'Test',
            OrderField::BILLING_LAST_NAME => 'Test',
            OrderField::BILLING_FAX => null,
            OrderField::IS_SHIPPING_SAME => false,
            OrderField::SHIPPING_CUSTOMER_TITLE => 'Mr.',
            OrderField::SHIPPING_COUNTRY => 'France',
            OrderField::SHIPPING_COUNTRY_CODE => 'FR',
            OrderField::SHIPPING_ADDRESS1 => '74 rue de la Mare aux Carats',
            OrderField::SHIPPING_ADDRESS2 => null,
            OrderField::SHIPPING_ADDRESS3 => null,
            OrderField::SHIPPING_STREET_NUMBER => null,
            OrderField::SHIPPING_CITY => 'Montpellier',
            OrderField::SHIPPING_STATE => 'Languedoc-Roussillon',
            OrderField::SHIPPING_POSTCODE => '34070',
            OrderField::SHIPPING_PHONE => '0440575930',
            OrderField::SHIPPING_EMAIL => 'test@example.com',
            OrderField::SHIPPING_FIRST_NAME => 'Test',
            OrderField::SHIPPING_LAST_NAME => 'Test',
            OrderField::SHIPPING_FAX => null,
            OrderField::CUSTOMER_ID => 1,
            OrderField::CUSTOMER_IP => '127.0.0.1',
            OrderField::CUSTOMER_DOB => (new \DateTime(strtotime('-20 years')))->getTimestamp(), //null or timestamp
            OrderField::IS_VIRTUAL => false,
            OrderField::ITEMS => $items,
            OrderField::LOCALE => 'en_US',
            OrderField::SHIPPING_METHOD => 'Standard delivery',
            OrderField::SHIPPING_AMOUNT => 125,
            OrderField::SHIPPING_TAX_AMOUNT => 25,
            OrderField::SHIPPING_TAX_CODE => 25,
            OrderField::COMPANY_NAME => '',
            OrderField::COMPANY_VAT => null,
            OrderField::CHECKOUT_TYPE => \IngenicoClient\Checkout::TYPE_B2C,
            OrderField::SHIPPING_COMPANY => 'One day shipping',
            OrderField::CUSTOMER_CIVILITY => null,
            OrderField::CUSTOMER_GENDER => 'M', // M or F or null
            OrderField::ADDITIONAL_DATA => []
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * Same As requestOrderInfo()
     * But Order Object Cannot Be Used To Fetch The Required Info
     *
     * @param mixed $reservedOrderId
     * @return array
     */
    public function requestOrderInfoBeforePlaceOrder($reservedOrderId)
    {
        return $this->requestOrderInfo(1);
    }

    /**
     * Save Platform's setting (key-value couple depending on the mode).
     *
     * @param bool $mode
     * @param string $key
     * @param mixed $value
     * @return void
     * @SuppressWarnings("all")
     */
    public function saveSetting($mode, $key, $value)
    {
        //
    }

    /**
     * Retrieves orderId from checkout session.
     *
     * @return mixed
     */
    public function requestOrderId()
    {
        return uniqid('test_');
    }

    /**
     * Retrieves Customer (buyer) ID on the platform side.
     * Zero for guests.
     * Needed for retrieving customer aliases (if saved any).
     *
     * @return int
     */
    public function requestCustomerId()
    {
        return 1; // Logged-in user
    }

    /**
     * Returns callback URLs where Ingenico must call after the payment processing. Depends on the context of the callback.
     * Following cases are required:
     *  CONTROLLER_TYPE_PAYMENT
     *  CONTROLLER_TYPE_SUCCESS
     *  CONTROLLER_TYPE_ORDER_SUCCESS
     *  CONTROLLER_TYPE_ORDER_CANCELLED
     *
     * @param $type
     * @param array $params
     * @return string
     */
    public function buildPlatformUrl($type, array $params = [])
    {
        switch ($type) {
            case IngenicoCoreLibrary::CONTROLLER_TYPE_PAYMENT:
                return 'https://example.com?act=ingenico_payment';
            case IngenicoCoreLibrary::CONTROLLER_TYPE_SUCCESS:
                return 'https://example.com?act=ingenico_success';
            case IngenicoCoreLibrary::CONTROLLER_TYPE_ORDER_SUCCESS:
                return 'https://example.com?act=checkout_success';
            case IngenicoCoreLibrary::CONTROLLER_TYPE_ORDER_CANCELLED:
                return 'https://example.com?act=ingenico_cancel';
            default:
                throw new \Exception('Unknown page type.');
        }
    }

    /**
     * This method is a generic callback gate.
     * Depending on the URI it redirects to the corresponding action which is done already on the CL level.
     * CL takes responsibility for the data processing and initiates rendering of the matching GUI (template, page etc.).
     *
     * @return void
     */
    public function processSuccessUrls()
    {
        //
    }

    /**
     * Executed on the moment when a buyer submits checkout form with an intention to start the payment process.
     * Depending on the payment mode (Inline vs. Redirect) CL will initiate the right processes and render the corresponding GUI.
     *
     * @return void
     */
    public function processPayment()
    {
        //
    }

    /**
     * Executed on the moment when customer's alias saved, and we're should charge payment.
     * Used in Inline payment mode.
     *
     * @return array
     */
    public function finishReturnInline()
    {
        //
    }

    /**
     * Matches Ingenico payment statuses to the platform's order statuses.
     *
     * @param mixed $orderId
     * @param string $paymentStatus
     * @param string|null $message
     * @return void
     * @throws \Exception
     */
    public function updateOrderStatus($orderId, $paymentStatus, $message = null)
    {
        //
    }

    /**
     * Check if Shopping Cart has orders that were paid (via other payment integrations, i.e. PayPal module)
     * It's to cover the case where payment was initiated through Ingenico but at the end, user went back and paid by other
     * payment provider. In this case we know not to send order reminders etc.
     *
     * @param $orderId
     * @return bool
     */
    public function isCartPaid($orderId)
    {
        return true;
    }

    /**
     * Sends an e-mail using platform's email engine.
     *
     * @param \IngenicoClient\MailTemplate $template
     * @param string $to
     * @param string $toName
     * @param string $from
     * @param string $fromName
     * @param string $subject
     * @param array $attachedFiles Array like [['name' => 'attached.txt', 'mime' => 'plain/text', 'content' => 'Body']]
     * @return bool|int
     * @throws \Exception
     */
    public function sendMail(
        $template,
        $to,
        $toName,
        $from,
        $fromName,
        $subject,
        array $attachedFiles = []
    ) {
        if (!$template instanceof \IngenicoClient\MailTemplate) {
            throw new \Exception('Template variable must be instance of MailTemplate');
        }

        return true;
    }

    /**
     * Get the platform's actual locale code.
     * Returns code in a format: en_US.
     *
     * @param int|null $orderId
     * @return string
     */
    public function getLocale($orderId = null)
    {
        // Obtain customer locale by Order
        return 'en_US';
    }

    /**
     * Adds cancelled amount to the order which is used for identifying full or partial operation.
     *
     * @param $orderId
     * @param $canceledAmount
     * @return void
     */
    public function addCancelledAmount($orderId, $canceledAmount)
    {
        //
    }

    /**
     * Adds captured amount to the order which is used for identifying full or partial operation.
     *
     * @param $orderId
     * @param $capturedAmount
     * @return void
     */
    public function addCapturedAmount($orderId, $capturedAmount)
    {
        //
    }

    /**
     * Adds refunded amount to the order which is used for identifying full or partial operation.
     *
     * @param $orderId
     * @param $refundedAmount
     * @return void
     */
    public function addRefundedAmount($orderId, $refundedAmount)
    {
        //
    }

    /**
     * Send "Order paid" email to the buyer (customer).
     *
     * @param $orderId
     * @return bool
     */
    public function sendOrderPaidCustomerEmail($orderId)
    {
        return true;
    }

    /**
     * Send "Order paid" email to the merchant.
     *
     * @param $orderId
     * @return bool
     */
    public function sendOrderPaidAdminEmail($orderId)
    {
        return true;
    }

    /**
     * Send "Payment Authorized" email to the buyer (customer).
     *
     * @param $orderId
     * @return bool
     */
    public function sendNotificationAuthorization($orderId)
    {
        return true;
    }

    /**
     * Send "Payment Authorized" email to the merchant.
     *
     * @param $orderId
     * @return bool
     */
    public function sendNotificationAdminAuthorization($orderId)
    {
        return true;
    }

    /**
     * Sends payment reminder email to the buyer (customer).
     *
     * @param $orderId
     * @return bool
     */
    public function sendReminderNotificationEmail($orderId)
    {
        return true;
    }

    /**
     * Send "Refund failed" email to the buyer (customer).
     *
     * @param $orderId
     * @return bool
     */
    public function sendRefundFailedCustomerEmail($orderId)
    {
        return true;
    }

    /**
     * Send "Refund failed" email to the merchant.
     *
     * @param $orderId
     * @return bool
     */
    public function sendRefundFailedAdminEmail($orderId)
    {
        return true;
    }

    /**
     * Send "Request Support" email to Ingenico Support
     * @param $email
     * @param $subject
     * @param array $fields
     * @param null $file
     * @return bool
     * @throws Exception
     */
    public function sendSupportEmail(
        $email,
        $subject,
        array $fields = [],
        $file = null
    ) {
        return true;
    }


    /**
     * Returns categories of the payment methods.
     *
     * @return array
     */
    public function getPaymentCategories()
    {
        return [];
    }

    /**
     * Returns all payment methods with the indicated category
     *
     * @param $category
     * @return array
     */
    public function getPaymentMethodsByCategory($category)
    {
        return [];
    }

    /**
     * Returns all supported countries with their popular payment methods mapped
     * Returns array like ['DE' => 'Germany']
     *
     * @return array
     */
    public function getAllCountries()
    {
        return [];
    }

    /**
     * Get Country by Code.
     *
     * @param $code
     * @return string|false
     */
    public function getCountryByCode($code)
    {
        return $code;
    }

    /**
     * Returns all payment methods as PaymentMethod objects.
     *
     * @return array
     */
    public function getPaymentMethods()
    {
        return [];
    }

    /**
     * Get Unused Payment Methods (not selected ones).
     * Returns an array with PaymentMethod objects.
     * Used in the modal window in the plugin Settings in order to list Payment methods that are not yet added.
     *
     * @return array
     */
    public function getUnusedPaymentMethods()
    {
        return [];
    }

    /**
     * Filters countries based on the search string.
     *
     * @param $query
     * @param $selected_countries array of selected countries iso codes
     * @return array
     */
    public function filterCountries($query, $selected_countries)
    {
        return [];
    }

    /**
     * Filters payment methods based on the search string.
     *
     * @param $query
     * @return array
     */
    public function filterPaymentMethods($query)
    {
        return [];
    }

    /**
     * Retrieves payment method by Brand value.
     *
     * @param $brand
     * @return PaymentMethod|false
     */
    public function getPaymentMethodByBrand($brand)
    {
        //return $this->coreLibrary->getPaymentMethodByBrand($brand);
    }

    /**
     * Save Payment data.
     * This data helps to avoid constant pinging of Ingenico to get PAYID and other information
     *
     * @param $orderId
     * @param \IngenicoClient\Payment $data
     *
     * @return bool
     */
    public function logIngenicoPayment($orderId, \IngenicoClient\Payment $data)
    {
        return true;
    }

    /**
     * Retrieves payment log for the specified order ID.
     *
     * @param $orderId
     *
     * @return \IngenicoClient\Payment
     */
    public function getIngenicoPaymentLog($orderId)
    {
        return new \IngenicoClient\Payment([]);
    }

    /**
     * Retrieves payment log entry by the specified Pay ID (PAYID).
     *
     * @param $payId
     *
     * @return \IngenicoClient\Payment
     */
    public function getIngenicoPaymentById($payId)
    {
        return new \IngenicoClient\Payment([]);
    }

    /**
     * Retrieves Ingenico Pay ID by the specified platform order ID.
     *
     * @param $orderId
     * @return string|false
     */
    public function getIngenicoPayIdByOrderId($orderId)
    {
        return false;
    }

    /**
     * Retrieves buyer (customer) aliases by the platform's customer ID.
     *
     * @param $customerId
     * @return array
     */
    public function getCustomerAliases($customerId)
    {
        return [];
    }

    /**
     * Retrieves an Alias object with the fields as an array by the Alias ID (platform's entity identifier).
     * Fields list: alias_id, customer_id, ALIAS, ED, BRAND, CARDNO, BIN, PM.
     *
     * @param $aliasId
     * @return array|false
     */
    public function getAlias($aliasId)
    {
        return false;
    }

    /**
     * Saves the buyer (customer) Alias entity.
     * Important fields that are provided by Ingenico: ALIAS, BRAND, CARDNO, BIN, PM, ED.
     *
     * @param int $customerId
     * @param array $data
     * @return bool
     */
    public function saveAlias($customerId, array $data)
    {
        return true;
    }

    /**
     * Delegates cron jobs handling to the CL.
     *
     * @return void
     */
    public function cronHandler()
    {
        $this->coreLibrary->cronHandler();
    }

    /**
     * Retrieves the list of orders that have no payment status at all or have an error payment status.
     * Used for the cron job that is proactively updating orders statuses.
     * Returns an array with order IDs.
     *
     * @return array
     */
    public function getNonactualisedOrdersPaidWithIngenico()
    {
        return [];
    }

    /**
     * Sets PaymentStatus.Actualised Flag.
     * Used for the cron job that is proactively updating orders statuses.
     *
     * @param $orderId
     * @param bool $value
     * @return bool
     */
    public function setIsPaymentStatusActualised($orderId, $value)
    {
        return true;
    }

    /**
     * Checks if PaymentStatus is actualised (up to date)
     *
     * @param $orderId
     * @return bool
     */
    private function isPaymentStatusActualised($orderId)
    {
        return true;
    }

    /**
     * Retrieves the list of orders for the reminder email.
     *
     * @return array
     */
    public function getPendingReminders()
    {
        return [];
    }

    /**
     * Sets order reminder flag as "Sent".
     *
     * @param $orderId
     *
     * @return void
     */
    public function setReminderSent($orderId)
    {
        //
    }

    /**
     * Enqueues the reminder for the specified order.
     * Used for the cron job that is sending payment reminders.
     *
     * @param mixed $orderId
     * @return void
     */
    public function enqueueReminder($orderId)
    {
        //
    }

    /**
     * Retrieves the list of orders that are candidates for the reminder email.
     * Returns an array with orders IDs.
     *
     * @return array
     */
    public function getOrdersForReminding()
    {
        return [];
    }

    /**
     * Delegates to the CL the complete processing of the onboarding data and dispatching email to the corresponding
     *  Ingenico sales representative.
     *
     * @param string $companyName
     * @param string $email
     * @param string $countryCode
     *
     * @throws \IngenicoClient\Exception
     */
    public function submitOnboardingRequest($companyName, $email, $countryCode)
    {
        //
    }

    /**
     * Renders page with Inline's Loader template.
     * This template should include code that allow charge payment asynchronous.
     *
     * @param array $fields
     * @return void
     */
    public function showInlineLoaderTemplate(array $fields)
    {
        //
    }

    /**
     * Renders the template of the payment success page.
     *
     * @param array $fields
     * @param Payment $payment
     *
     * @return void
     */
    public function showSuccessTemplate(array $fields, Payment $payment)
    {
        //
    }

    /**
     * Renders the template with 3Ds Security Check.
     *
     * @param array $fields
     * @param Payment $payment
     *
     * @return void
     */
    public function showSecurityCheckTemplate(array $fields, Payment $payment)
    {
        //
    }

    /**
     * Renders the template with the order cancellation.
     *
     * @param array $fields
     * @param Payment $payment
     *
     * @return void
     */
    public function showCancellationTemplate(array $fields, Payment $payment)
    {
        //
    }

    /**
     * Renders the template with the payment error.
     *
     * @param array $fields
     * @param Payment $payment
     *
     * @return void
     */
    public function showPaymentErrorTemplate(array $fields, Payment $payment)
    {
        //
    }

    /**
     * Renders the template of payment methods list for the redirect mode.
     *
     * @param array $fields
     *
     * @return void
     */
    public function showPaymentListRedirectTemplate(array $fields)
    {
        //
    }

    /**
     * Renders the template with the payment methods list for the inline mode.
     *
     * @param array $fields
     *
     * @return void
     */
    public function showPaymentListInlineTemplate(array $fields)
    {
        //
    }

    /**
     * Renders the template with the payment methods list for the alias selection.
     * It does require by CoreLibrary.
     *
     * @param array $fields
     *
     * @return void
     */
    public function showPaymentListAliasTemplate(array $fields)
    {
        //
    }

    /**
     * In case of error, display error page.
     *
     * @param $message
     * @return void
     */
    public function setOrderErrorPage($message)
    {
        //
    }

    /**
     * Handles incoming requests from Ingenico.
     * Passes execution to CL.
     * From there it updates order's statuses.
     * This method must return HTTP status 200/400.
     *
     * @return void
     */
    public function webhookListener()
    {
        //
    }

    /**
     * Initiates payment page from the reminder email link.
     *
     *
     * @return void
     */
    public function showReminderPayOrderPage()
    {
        //
    }

    /**
     * Empty Shopping Cart and reset session.
     *
     * @return void
     */
    public function emptyShoppingCart()
    {
        //
    }

    /**
     * Restore Shopping Cart.
     */
    public function restoreShoppingCart()
    {
        //
    }

    /**
     * Restore Cart
     * @param $orderId
     */
    private function restoreCart($orderId)
    {
        //
    }

    /**
     * Retrieve Missing or Invalid Order's fields
     * @param mixed $orderId
     * @param PaymentMethod $pm
     * @return array
     */
    public function retrieveMissingFields($orderId, PaymentMethod $pm)
    {
        //
    }

    /**
     * Get Payment Method Code of Order.
     *
     * @param mixed $orderId
     *
     * @return string|false
     */
    public function getOrderPaymentMethod($orderId)
    {
        return false;
    }

    /**
     * Get Payment Method Code of Quote/Cart.
     *
     * @param mixed $quoteId
     *
     * @return string|false
     */
    public function getQuotePaymentMethod($quoteId = null)
    {
        return false;
    }

    /**
     * Get all Session values in a key => value format
     *
     * @return array
     */
    public function getSessionValues()
    {
        return [];
    }

    /**
     * Get value from Session.
     *
     * @param string $key
     * @return mixed
     */
    public function getSessionValue($key)
    {
        return false;
    }

    /**
     * Store value in Session.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setSessionValue($key, $value)
    {
        //
    }

    /**
     * Remove value from Session.
     *
     * @param $key
     * @return void
     */
    public function unsetSessionValue($key)
    {
        //
    }

    /**
     * Get Field Label
     *
     * @param string $field
     * @return string
     */
    public function getOrderFieldLabel($field)
    {
        switch ($field) {
            case OrderField::CUSTOMER_DOB:
                return 'Date of Birth';
            default:
                return ucfirst(str_replace('_', ' ', $field));
        }
    }

    /**
     * Process OpenInvoice Payment.
     *
     * @param mixed $orderId
     * @param \IngenicoClient\Alias $alias
     * @param array $fields Form fields
     * @return void
     */
    public function processOpenInvoicePayment($orderId, \IngenicoClient\Alias $alias, array $fields = [])
    {
        // @see Connector::showPaymentListRedirectTemplate()
        // @see Connector::clarifyOpenInvoiceAdditionalFields()
    }

    /**
     * Process if have invalid fields of OpenInvoice.
     *
     * @param $orderId
     * @param \IngenicoClient\Alias $alias
     * @param array $fields
     */
    public function clarifyOpenInvoiceAdditionalFields($orderId, \IngenicoClient\Alias $alias, array $fields)
    {
        //
    }

    /**
     * Get Platform Environment.
     *
     * @return string
     */
    public function getPlatformEnvironment()
    {
        return \IngenicoClient\IngenicoCoreLibrary::PLATFORM_INGENICO;
    }

    /**
     * Check whether an order with given ID is created in Magento
     *
     * @param $orderId
     * @return bool
     */
    public function isOrderCreated($orderId)
    {
        return true;
    }
}
