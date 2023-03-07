<?php

namespace IngenicoClient;

use Ogone\Ecommerce\EcommercePaymentRequest;
use Psr\Log\LoggerInterface;

interface IngenicoCoreLibraryInterface
{
    /**
     * Get Default Settings.
     *
     * @return array
     */
    public function getDefaultSettings(): array;

    /**
     * Get Configuration instance.
     *
     * @return Configuration
     */
    public function getConfiguration(): Configuration;

    /**
     * Set Generic Merchant Country.
     *
     * @param $country
     * @return Configuration
     * @throws Exception
     */
    public function setGenericCountry($country): Configuration;

    /**
     * Get Generic Merchant Country.
     * @return string|null
     */
    public function getGenericCountry(): ?string;

    /**
     * Translate string.
     *
     * @param $id
     * @param string|null $domain
     * @param string|null $locale
     * @return string
     */
    public function __($id, array $parameters = [], string $domain = null, string $locale = null): string;

    /**
     * Get All Translations.
     *
     * @param string|null $domain
     * @return array
     */
    public function getAllTranslations(string $locale, string $domain = null): array;

    /**
     * Get Inline payment method URL
     *
     * @param $orderId
     * @return string
     */
    public function getInlineIFrameUrl($orderId, Alias $alias): string;

    /**
     * Get payment status.
     *
     * @param $orderId
     * @param $payId
     * @param $payIdSub
     *
     * @return Payment
     */
    public function getPaymentInfo($orderId, $payId = null, $payIdSub = null): Payment;

    /**
     * Create Direct Link payment request.
     *
     * Returns Payment info with transactions results.
     *
     * @param $orderId
     * @param Alias $alias
     *
     * @return Payment
     */
    public function executePayment($orderId, Alias $alias): Payment;

    /**
     * Process Return Urls.
     * Execute when customer made payment. And payment gateway redirect customer back to Merchant shop.
     * We're should check payment status. And update order status.
     *
     * @return void
     */
    public function processReturnUrls(): void;

    /**
     * Process Payment Confirmation
     * Execute when customer submit checkout form.
     * We should initialize payment and display payment form for customer.
     *
     * @param mixed|null $aliasId
     *
     * @return void
     *@throws Exception
     */
    public function processPayment(mixed $orderId, mixed $aliasId = null, bool $forceAliasSave = false): void;

    /**
     * Process Payment Confirmation: Redirect
     *
     * @param mixed|null $aliasId
     * @return void
     *@throws Exception
     */
    public function processPaymentRedirect(mixed $orderId, mixed $aliasId = null, bool $forceAliasSave = false): void;

    /**
     * Process Payment Confirmation: Redirect with specified PM/Brand.
     *
     * @param       $paymentMethod
     * @param       $brand
     * @throws Exception
     * @return void
     */
    public function processPaymentRedirectSpecified(mixed $orderId, mixed $aliasId, $paymentMethod, $brand): void;

    /**
     * Process Payment Confirmation: Inline
     *
     * @return void
     * @throws Exception
     */
    public function processPaymentInline(mixed $orderId, mixed $aliasId, bool $forceAliasSave = false): void;

    /**
     * Executed on the moment when customer's alias saved, and we're should charge payment.
     * Used in Inline payment mode.
     *
     * @param $orderId
     * @param $cardBrand
     * @param $aliasId
     *
     * @return array
     */
    public function finishReturnInline($orderId, $cardBrand, $aliasId): array;

    /**
     * Handle incoming requests by Webhook.
     * Update order's statuses by incoming request from Ingenico.
     * This method should return http status 200/400.
     *
     * @return void
     */
    public function webhookListener(): void;

    /**
     * Get Hosted Checkout parameters to generate the payment form.
     * @deprecated Use IngenicoCoreLibrary::getHostedCheckoutPaymentRequest() instead of
     *
     * @param $orderId
     * @return Data
     */
    public function initiateRedirectPayment($orderId, Alias $alias): Data;

    /**
     * Get Hosted Checkout Payment Request
     *
     * @return EcommercePaymentRequest
     * @throws \Exception
     */
    public function getHostedCheckoutPaymentRequest(Order $order, Alias $alias): EcommercePaymentRequest;

    /**
     * Get "Redirect" Payment Request with specified PaymentMethod and Brand.
     * @param mixed|null $aliasId
     * @param string|null $paymentId
     *
     * @return Data Data with url and fields keys
     * @throws Exception
     *@see \IngenicoClient\PaymentMethod\PaymentMethod
     *
     */
    public function getSpecifiedRedirectPaymentRequest(
        mixed  $orderId,
        mixed  $aliasId,
        string $paymentMethod,
        string $brand,
        string $paymentId = null
    ): Data;

    /**
     * Get Country By ISO Code
     *
     * @param $isoCode
     * @return string
     */
    public static function getCountryByCode($isoCode): string;

    /**
     * Get Categories of Payment Methods
     * @return array
     */
    public function getPaymentCategories(): array;

    /**
     * Get Countries of Payment Methods
     * @return array
     */
    public function getAllCountries(): array;

    /**
     * Get all payment methods.
     *
     * @return array
     */
    public function getPaymentMethods(): array;

    /**
     * @deprecated
     * @return array
     */
    public static function getCountriesPaymentMethods(): array;

    /**
     * Get Payment Method by Brand.
     *
     * @param $brand
     *
     * @return PaymentMethod\PaymentMethod|false
     */
    public function getPaymentMethodByBrand($brand): bool|PaymentMethod\PaymentMethod;

    /**
     * Get payment methods by Category
     *
     * @param $category
     * @return array
     */
    public function getPaymentMethodsByCategory($category): array;

    /**
     * Get Selected Payment Methods
     *
     * @return array
     */
    public function getSelectedPaymentMethods(): array;

    /**
     * Get Unused Payment Methods.
     *
     * @return array
     */
    public function getUnusedPaymentMethods(): array;

    /**
     * Get Payment Methods by Country ISO code
     * And merge with current list of Payment methods.
     *
     *
     * @return array
     */
    public function getAndMergeCountriesPaymentMethods(array $countries): array;

    /**
     * process Onboarding data and dispatch email to the corresponding Ingenico sales representative.
     *
     * @param $shopName
     * @param $shopLogo
     * @param $shopUrl
     * @param $ingenicoLogo
     */
    public function submitOnboardingRequest(
        string $companyName,
        string $email,
        string $countryCode,
        string $eCommercePlatform,
        string $pluginVersion,
               $shopName,
               $shopLogo,
               $shopUrl,
               $ingenicoLogo
    );

    /**
     * Refund.
     *
     * @param $orderId
     * @param string|null $payId
     * @param int|null $amount
     *
     * @return Payment
     * @throws Exception
     */
    public function refund($orderId, string $payId = null, int $amount = null): Payment;

    /**
     * Capture.
     *
     * @param $orderId
     * @param string|null $payId
     * @param int|null $amount
     *
     * @return Payment
     * @throws Exception
     */
    public function capture($orderId, string $payId = null, int $amount = null): Payment;

    /**
     * Cancel.
     *
     * @param $orderId
     * @param string|null $payId
     * @param int|null $amount
     *
     * @return Payment
     * @throws Exception
     */
    public function cancel($orderId, string $payId = null, int $amount = null): Payment;

    /**
     * Get Status by Status Code.
     *
     * @param $statusCode
     *
     * @return string
     */
    public static function getStatusByCode($statusCode): string;

    /**
     * Get Payment Status.
     *
     * @return string
     */
    public function getPaymentStatus(string $brand, int $statusCode): string;

    /**
     * Finalise Payment and Update order status.
     * Returns payment status as string.
     *
     * @param $orderId
     * @return string
     */
    public function finaliseOrderPayment($orderId, Payment &$paymentResult): string;

    /**
     * Check void availability
     *
     * @param $orderId
     * @param $payId
     * @param $cancelAmount
     *
     * @return bool
     */
    public function canVoid($orderId, $payId, $cancelAmount): bool;

    /**
     * Check capture availability.
     *
     * @param $orderId
     * @param $payId
     * @param $captureAmount
     *
     * @return bool
     */
    public function canCapture($orderId, $payId, $captureAmount): bool;

    /**
     * Check refund availability.
     *
     * @param $orderId
     * @param $payId
     * @param $refundAmount
     *
     * @return bool
     */
    public function canRefund($orderId, $payId, $refundAmount): bool;

    /**
     * Get MailTemplate instance of Reminder.
     *
     * @param $to
     * @param $toName
     * @param $from
     * @param $fromName
     * @param $subject
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function sendMailNotificationReminder(
        $to,
        $toName,
        $from,
        $fromName,
        $subject,
        array $fields = [],
        string $locale = ''
    ): bool;

    /**
     * Get MailTemplate instance of "Refund Failed".
     *
     * @param $to
     * @param $toName
     * @param $from
     * @param $fromName
     * @param $subject
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function sendMailNotificationRefundFailed(
        $to,
        $toName,
        $from,
        $fromName,
        $subject,
        array $fields = [],
        string $locale = ''
    ): bool;

    /**
     * Get MailTemplate instance of "Refund Failed".
     *
     * @param $to
     * @param $toName
     * @param $from
     * @param $fromName
     * @param $subject
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function sendMailNotificationAdminRefundFailed(
        $to,
        $toName,
        $from,
        $fromName,
        $subject,
        array $fields = [],
        string $locale = ''
    ): bool;

    /**
     * Get MailTemplate instance of "Order Paid".
     *
     * @param $to
     * @param $toName
     * @param $from
     * @param $fromName
     * @param $subject
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function sendMailNotificationPaidOrder(
        $to,
        $toName,
        $from,
        $fromName,
        $subject,
        array $fields = [],
        string $locale = ''
    ): bool;

    /**
     * Get MailTemplate instance of "Admin Order Paid".
     *
     * @param $to
     * @param $toName
     * @param $from
     * @param $fromName
     * @param $subject
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function sendMailNotificationAdminPaidOrder(
        $to,
        $toName,
        $from,
        $fromName,
        $subject,
        array $fields = [],
        string $locale = ''
    ): bool;

    /**
     * Get MailTemplate instance of "Authorization".
     *
     * @param $to
     * @param $toName
     * @param $from
     * @param $fromName
     * @param $subject
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function sendMailNotificationAuthorization(
        $to,
        $toName,
        $from,
        $fromName,
        $subject,
        array $fields = [],
        string $locale = ''
    ): bool;

    /**
     * Get MailTemplate instance of "Admin Authorization".
     *
     * @param $to
     * @param $toName
     * @param $from
     * @param $fromName
     * @param $subject
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function sendMailNotificationAdminAuthorization(
        $to,
        $toName,
        $from,
        $fromName,
        $subject,
        array $fields = [],
        string $locale = ''
    ): bool;

    /**
     * Get MailTemplate instance of "Onboarding request".
     *
     * @param $to
     * @param $toName
     * @param $from
     * @param $fromName
     * @param $subject
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function sendMailNotificationOnboardingRequest(
        $to,
        $toName,
        $from,
        $fromName,
        $subject,
        array $fields = [],
        string $locale = ''
    ): bool;

    /**
     * Get MailTemplate instance of "Ingenico Support".
     *
     * @param $to
     * @param $toName
     * @param $from
     * @param $fromName
     * @param $subject
     * @param string|null $locale
     * @param array $attachedFiles Array like [['name' => 'attached.txt', 'mime' => 'plain/text', 'content' => 'Body']]
     *
     * @return bool
     * @throws \Exception
     */
    public function sendMailSupport(
        $to,
        $toName,
        $from,
        $fromName,
        $subject,
        array $fields = [],
        string $locale = null,
        array $attachedFiles = []
    ): bool;

    /**
     * Get Alias
     * @param $aliasId
     * @return Alias
     */
    public function getAlias($aliasId): Alias;

    /**
     * Get Aliases by CustomerId
     * @param $customerId
     * @return array
     */
    public function getCustomerAliases($customerId): array;

    /**
     * Save Alias
     * @return bool
     */
    public function saveAlias(Alias $alias): bool;

    /**
     * Cron Handler.
     * Send Reminders.
     * Actualise Order's statuses.
     * We're ask payment gateway and get payment status.
     * And update Platform's order status.
     *
     * @return void
     */
    public function cronHandler(): void;

    /**
     * Set Logger.
     *
     * @param LoggerInterface|null $logger
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $logger = null): static;

    /**
     * Gets Logger.
     *
     * @return LoggerInterface|null
     */
    public function getLogger(): ?LoggerInterface;
}
