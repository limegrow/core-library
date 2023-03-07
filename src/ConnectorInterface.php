<?php

namespace IngenicoClient;

use IngenicoClient\PaymentMethod\PaymentMethod;

/**
 * Interface ConnectorInterface.
 */
interface ConnectorInterface
{
    /**
     * Returns Shopping Cart Extension Id.
     *
     * @return string
     */
    public function requestShoppingCartExtensionId(): string;

    /**
     * Returns activated Ingenico environment mode.
     * False for Test (transactions will go through the Ingenico sandbox).
     * True for Live (transactions will be real).
     *
     * @return bool
     */
    public function requestSettingsMode(): bool;

    /**
     * Returns the complete list of all settings as an array.
     *
     * @param bool $mode False for Test. True for Live.
     *
     * @return array
     */
    public function requestSettings(bool $mode): array;

    /**
     * Retrieves orderId from checkout session.
     *
     * @return mixed
     */
    public function requestOrderId(): mixed;

    /**
     * Retrieves Customer (buyer) ID on the platform side.
     * Zero for guests.
     * Needed for retrieving customer aliases (if saved any).
     *
     * @return int
     */
    public function requestCustomerId(): int;

    /**
     * Returns callback URLs where Ingenico must call after the payment processing.
     * Depends on the context of the callback.
     * Following cases are required:
     *  CONTROLLER_TYPE_PAYMENT
     *  CONTROLLER_TYPE_SUCCESS
     *  CONTROLLER_TYPE_ORDER_SUCCESS
     *  CONTROLLER_TYPE_ORDER_CANCELLED
     *
     * @param $type
     * @param array $params
     *
     * @return string
     */
    public function buildPlatformUrl($type, array $params = []): string;

    /**
     * This method is a generic callback gate.
     * Depending on the URI it redirects to the corresponding action which is done already on the CL level.
     * CL takes responsibility for the data processing and initiates rendering of the matching GUI
     * (template, page etc.).
     *
     * @return void
     */
    public function processSuccessUrls(): void;

    /**
     * Executed on the moment when a buyer submits checkout form with an intention to start the payment process.
     * Depending on the payment mode (Inline vs. Redirect) CL will initiate the right processes and render
     * the corresponding GUI.
     *
     * @return void
     */
    public function processPayment(): void;

    /**
     * Matches Ingenico payment statuses to the platform's order statuses.
     *
     * @param mixed $orderId
     * @param \IngenicoClient\Payment|string $paymentStatus
     * @param string|null $message
     * @return void
     */
    public function updateOrderStatus(mixed $orderId, Payment|string $paymentStatus, string $message = null): void;

    /**
     * Check if Shopping Cart has orders that were paid (via other payment integrations, i.e. PayPal module)
     * It's to cover the case where payment was initiated through Ingenico but at the end,
     * user went back and paid by other payment provider. In this case we know not to send order reminders etc.
     *
     * @param $orderId
     *
     * @return bool
     */
    public function isCartPaid($orderId): bool;

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
    public function submitOnboardingRequest(string $companyName, string $email, string $countryCode);

    /**
     * Returns an array with the order details in a standardised way for all connectors.
     * Matches platform specific fields to the fields that are understood by the CL.
     *
     * @param mixed|null $orderId
     * @return array
     */
    public function requestOrderInfo(mixed $orderId = null): array;

    /**
     * Get Payment Method Code of Order.
     *
     * @param mixed $orderId
     *
     * @return string|false
     */
    public function getOrderPaymentMethod(mixed $orderId): bool|string;

    /**
     * Get Payment Method Code of Quote/Cart.
     *
     * @param mixed|null $quoteId
     *
     * @return string|false
     */
    public function getQuotePaymentMethod(mixed $quoteId = null): bool|string;

    /**
     * Get Field Label
     *
     * @param string $field
     * @return string
     */
    public function getOrderFieldLabel(string $field): string;

    /**
     * Save Platform's setting (key-value couple depending on the mode).
     *
     * @param bool $mode
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function saveSetting(bool $mode, string $key, mixed $value): void;

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
     */
    public function sendMail(
        MailTemplate $template,
        string       $to,
        string       $toName,
        string       $from,
        string       $fromName,
        string       $subject,
        array        $attachedFiles = []
    ): bool|int;

    /**
     * Get the platform's actual locale code.
     * Returns code in a format: en_US.
     *
     * @param int|null $orderId
     * @return string
     */
    public function getLocale(int $orderId = null): string;

    /**
     * Adds cancelled amount to the order which is used for identifying full or partial operation.
     *
     * @param $orderId
     * @param $canceledAmount
     * @return void
     */
    public function addCancelledAmount($orderId, $canceledAmount): void;

    /**
     * Adds captured amount to the order which is used for identifying full or partial operation.
     *
     * @param $orderId
     * @param $capturedAmount
     * @return void
     */
    public function addCapturedAmount($orderId, $capturedAmount): void;

    /**
     * Adds refunded amount to the order which is used for identifying full or partial operation.
     *
     * @param $orderId
     * @param $refundedAmount
     * @return void
     */
    public function addRefundedAmount($orderId, $refundedAmount): void;

    /**
     * Send "Order paid" email to the buyer (customer).
     *
     * @param $orderId
     * @return bool
     */
    public function sendOrderPaidCustomerEmail($orderId): bool;

    /**
     * Send "Order paid" email to the merchant.
     *
     * @param $orderId
     * @return bool
     */
    public function sendOrderPaidAdminEmail($orderId): bool;

    /**
     * Send "Payment Authorized" email to the buyer (customer).
     *
     * @param $orderId
     * @return bool
     */
    public function sendNotificationAuthorization($orderId): bool;

    /**
     * Send "Payment Authorized" email to the merchant.
     *
     * @param $orderId
     * @return bool
     */
    public function sendNotificationAdminAuthorization($orderId): bool;

    /**
     * Sends payment reminder email to the buyer (customer).
     *
     * @param $orderId
     * @return bool
     */
    public function sendReminderNotificationEmail($orderId): bool;

    /**
     * Send "Refund failed" email to the buyer (customer).
     *
     * @param $orderId
     * @return bool
     */
    public function sendRefundFailedCustomerEmail($orderId): bool;

    /**
     * Send "Refund failed" email to the merchant.
     *
     * @param $orderId
     * @return bool
     */
    public function sendRefundFailedAdminEmail($orderId): bool;

    /**
     * Send "Request Support" email to Ingenico Support
     * @param $email
     * @param $subject
     * @param array $fields
     * @param null $file
     * @return bool
     */
    public function sendSupportEmail(
        $email,
        $subject,
        array $fields = [],
        $file = null
    ): bool;

    /**
     * Save Payment data.
     * This data helps to avoid constant pinging of Ingenico to get PAYID and other information
     *
     * @param $orderId
     * @param \IngenicoClient\Payment $data
     *
     * @return bool
     */
    public function logIngenicoPayment($orderId, Payment $data): bool;

    /**
     * Retrieves payment log for the specified order ID.
     *
     * @param $orderId
     *
     * @return \IngenicoClient\Payment
     */
    public function getIngenicoPaymentLog($orderId): Payment;

    /**
     * Retrieves payment log entry by the specified Pay ID (PAYID).
     *
     * @param $payId
     *
     * @return \IngenicoClient\Payment
     */
    public function getIngenicoPaymentById($payId): Payment;

    /**
     * Retrieves Ingenico Pay ID by the specified platform order ID.
     *
     * @param $orderId
     * @return string|false
     */
    public function getIngenicoPayIdByOrderId($orderId): bool|string;

    /**
     * Retrieves buyer (customer) aliases by the platform's customer ID.
     *
     * @param $customerId
     * @return array
     */
    public function getCustomerAliases($customerId): array;

    /**
     * Retrieves an Alias object with the fields as an array by the Alias ID (platform's entity identifier).
     * Fields list: alias_id, customer_id, ALIAS, ED, BRAND, CARDNO, BIN, PM.
     *
     * @param $aliasId
     * @return array|false
     */
    public function getAlias($aliasId): bool|array;

    /**
     * Saves the buyer (customer) Alias entity.
     * Important fields that are provided by Ingenico: ALIAS, BRAND, CARDNO, BIN, PM, ED.
     *
     * @param int $customerId
     * @param array $data
     * @return bool
     */
    public function saveAlias(int $customerId, array $data): bool;

    /**
     * Renders the template of the payment success page.
     *
     * @param array $fields
     * @param Payment $payment
     *
     * @return void
     */
    public function showSuccessTemplate(array $fields, Payment $payment): void;

    /**
     * Renders the template with 3Ds Security Check.
     *
     * @param array $fields
     * @param Payment $payment
     *
     * @return void
     */
    public function showSecurityCheckTemplate(array $fields, Payment $payment): void;

    /**
     * Renders the template with the order cancellation.
     *
     * @param array $fields
     * @param Payment $payment
     *
     * @return void
     */
    public function showCancellationTemplate(array $fields, Payment $payment): void;

    /**
     * Renders page with Inline's Loader template.
     * This template should include code that allow charge payment asynchronous.
     *
     * @param array $fields
     * @return void
     */
    public function showInlineLoaderTemplate(array $fields): void;

    /**
     * In case of error, display error page.
     *
     * @param $message
     * @return void
     */
    public function setOrderErrorPage($message): void;

    /**
     * Renders the template with the payment error.
     *
     * @param array $fields
     * @param Payment $payment
     *
     * @return void
     */
    public function showPaymentErrorTemplate(array $fields, Payment $payment): void;

    /**
     * Renders the template of payment methods list for the redirect mode.
     *
     * @param array $fields
     *
     * @return void
     */
    public function showPaymentListRedirectTemplate(array $fields): void;

    /**
     * Renders the template with the payment methods list for the inline mode.
     *
     * @param array $fields
     *
     * @return void
     */
    public function showPaymentListInlineTemplate(array $fields): void;

    /**
     * Renders the template with the payment methods list for the alias selection.
     * It does require by CoreLibrary.
     *
     * @param array $fields
     *
     * @return void
     */
    public function showPaymentListAliasTemplate(array $fields): void;

    /**
     * Retrieves the list of orders that have no payment status at all or have an error payment status.
     * Used for the cron job that is proactively updating orders statuses.
     * Returns an array with order IDs.
     *
     * @return array
     */
    public function getNonactualisedOrdersPaidWithIngenico(): array;

    /**
     * Sets PaymentStatus.Actualised Flag.
     * Used for the cron job that is proactively updating orders statuses.
     *
     * @param $orderId
     * @param bool $value
     * @return bool
     */
    public function setIsPaymentStatusActualised($orderId, bool $value): bool;

    /**
     * Retrieves the list of orders for the reminder email.
     *
     * @return array
     */
    public function getPendingReminders(): array;

    /**
     * Sets order reminder flag as "Sent".
     *
     * @param $orderId
     *
     * @return void
     */
    public function setReminderSent($orderId): void;

    /**
     * Enqueues the reminder for the specified order.
     * Used for the cron job that is sending payment reminders.
     *
     * @param mixed $orderId
     * @return void
     */
    public function enqueueReminder(mixed $orderId): void;

    /**
     * Initiates payment page from the reminder email link.
     *
     * @return void
     */
    public function showReminderPayOrderPage(): void;

    /**
     * Retrieves the list of orders that are candidates for the reminder email.
     * Returns an array with orders IDs.
     *
     * @return array
     */
    public function getOrdersForReminding(): array;

    /**
     * Returns categories of the payment methods.
     *
     * @return array
     */
    public function getPaymentCategories(): array;

    /**
     * Returns all payment methods with the indicated category.
     *
     * @param $category
     * @return array
     */
    public function getPaymentMethodsByCategory($category): array;

    /**
     * Returns all supported countries with their popular payment methods mapped
     * Returns array like ['DE' => 'Germany']
     *
     * @return array
     */
    public function getAllCountries(): array;

    /**
     * Returns all payment methods as PaymentMethod objects.
     *
     * @return array
     */
    public function getPaymentMethods(): array;

    /**
     * Get Unused Payment Methods (not selected ones).
     * Returns an array with PaymentMethod objects.
     * Used in the modal window in the plugin Settings in order to list Payment methods that are not yet added.
     *
     * @return array
     */
    public function getUnusedPaymentMethods(): array;

    /**
     * Filters countries based on the search string.
     *
     * @param $query
     * @param $selected_countries array of selected countries iso codes
     * @return array
     */
    public function filterCountries($query, array $selected_countries): array;

    /**
     * Filters payment methods based on the search string.
     *
     * @param $query
     * @return array
     */
    public function filterPaymentMethods($query): array;

    /**
     * Retrieves payment method by Brand value.
     *
     * @param $brand
     * @return PaymentMethod|false
     */
    public function getPaymentMethodByBrand($brand): bool|PaymentMethod;

    /**
     * Delegates cron jobs handling to the CL.
     *
     * @return void
     */
    public function cronHandler(): void;

    /**
     * Handles incoming requests from Ingenico.
     * Passes execution to CL.
     * From there it updates order's statuses.
     * This method must return HTTP status 200/400.
     *
     * @return void
     */
    public function webhookListener(): void;

    /**
     * Empty Shopping Cart and reset session.
     *
     * @return void
     */
    public function emptyShoppingCart(): void;

    /**
     * Restore Shopping Cart.
     */
    public function restoreShoppingCart();

    /**
     * Process OpenInvoice Payment.
     *
     * @param mixed $orderId
     * @param \IngenicoClient\Alias $alias
     * @param array $fields Form fields
     * @return void
     */
    public function processOpenInvoicePayment(mixed $orderId, \IngenicoClient\Alias $alias, array $fields = []): void;

    /**
     * Process if have invalid fields of OpenInvoice.
     *
     * @param $orderId
     * @param \IngenicoClient\Alias $alias
     * @param array $fields
     */
    public function clarifyOpenInvoiceAdditionalFields($orderId, \IngenicoClient\Alias $alias, array $fields);

    /**
     * Get all Session values in a key => value format
     *
     * @return array
     */
    public function getSessionValues(): array;

    /**
     * Get value from Session.
     *
     * @param string $key
     * @return mixed
     */
    public function getSessionValue(string $key): mixed;

    /**
     * Store value in Session.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setSessionValue(string $key, mixed $value): void;

    /**
     * Remove value from Session.
     *
     * @param $key
     * @return void
     */
    public function unsetSessionValue($key): void;

    /**
     * Check whether an order with given ID is created in Magento
     *
     * @param $orderId
     * @return bool
     */
    public function isOrderCreated($orderId): bool;

    /**
     * Same As requestOrderInfo()
     * But Order Object Cannot Be Used To Fetch The Required Info
     *
     * @param mixed $reservedOrderId
     * @return array
     */
    public function requestOrderInfoBeforePlaceOrder(mixed $reservedOrderId): array;

    /**
     * Get Platform Environment.
     *
     * @return string
     */
    public function getPlatformEnvironment(): string;
}
