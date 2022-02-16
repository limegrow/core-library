<?php

namespace IngenicoClient;

use IngenicoClient\PaymentMethod\CarteBancaire;
use IngenicoClient\PaymentMethod\Afterpay;
use IngenicoClient\PaymentMethod\Klarna;
use IngenicoClient\PaymentMethod\KlarnaBankTransfer;
use IngenicoClient\PaymentMethod\KlarnaDirectDebit;
use IngenicoClient\PaymentMethod\KlarnaFinancing;
use IngenicoClient\PaymentMethod\KlarnaPayLater;
use IngenicoClient\PaymentMethod\KlarnaPayNow;
use IngenicoClient\PaymentMethod\FacilyPay3x;
use IngenicoClient\PaymentMethod\FacilyPay3xnf;
use IngenicoClient\PaymentMethod\FacilyPay4x;
use IngenicoClient\PaymentMethod\FacilyPay4xnf;
use VIISON\AddressSplitter\AddressSplitter;
use VIISON\AddressSplitter\Exceptions\SplittingException;
use IngenicoClient\Logger\AdapterInterface;
use IngenicoClient\Logger\MonologAdapter;
use IngenicoClient\Logger\FileAdapter;

class IngenicoCoreLibrary implements
    IngenicoCoreLibraryInterface,
    SessionInterface,
    OpenInvoiceInterface,
    HostedCheckoutInterface,
    DirectLinkPaymentInterface,
    FlexCheckoutInterface
{
    use HostedCheckout;
    use DirectLinkPayment;
    use FlexCheckout;
    use Session;
    use OpenInvoice;

    /**
     * Platforms
     */
    const PLATFORM_INGENICO = 'ingenico';
    const PLATFORM_BARCLAYS = 'barclays';
    const PLATFORM_POSTFINANCE = 'postfinance';
    const PLATFORM_KBC = 'kbc';
    const PLATFORM_CONCARDIS = 'concardis';
    const PLATFORM_VIVEUM = 'viveum';
    const PLATFORM_PAYGLOBE = 'payglobe';
    const PLATFORM_SANTANDER = 'santander';

    /**
     * Payment Statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_AUTHORIZED = 'authorized';
    const STATUS_CAPTURED = 'captured';
    const STATUS_CAPTURE_PROCESSING = 'capture_processing';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUND_PROCESSING = 'refund_processing';
    const STATUS_REFUND_REFUSED = 'refund_refused';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_ERROR = 'error';
    const STATUS_UNKNOWN = 'unknown';

    /**
     * Payment Modes
     */
    const PAYMENT_MODE_REDIRECT = 'REDIRECT';
    const PAYMENT_MODE_INLINE = 'INLINE';
    const PAYMENT_MODE_ALIAS = 'ALIAS';

    /**
     * Return States
     */
    const RETURN_STATE_ACCEPT = 'ACCEPT';
    const RETURN_STATE_DECLINE = 'DECLINE';
    const RETURN_STATE_CANCEL = 'CANCEL';
    const RETURN_STATE_EXCEPTION = 'EXCEPTION';
    const RETURN_STATE_BACK = 'BACK';

    /**
     * Platform Controllers type
     */
    const CONTROLLER_TYPE_PAYMENT = 'payment';
    const CONTROLLER_TYPE_SUCCESS = 'success';
    const CONTROLLER_TYPE_ORDER_SUCCESS = 'order_success';
    const CONTROLLER_TYPE_ORDER_CANCELLED = 'order_cancelled';

    /**
     * Parameters
     */
    const PARAM_NAME_OPEN_INVOICE_ORDER_ID = 'open_invoice_order_id';
    const PARAM_NAME_OPEN_INVOICE_CHECKOUT_INPUT = 'open_invoice_checkout_input';
    const PARAM_NAME_OPEN_INVOICE_FIELDS = 'open_invoice_additional_fields';

    /**
     * Aliases
     */
    const ALIAS_CREATE_NEW = 'new';

    /**
     * Alias response of FlexCheckout
     */
    const ALIAS_ID = 'Alias_AliasId';
    const ALIAS_ORDERID = 'Alias_OrderId';
    const ALIAS_STATUS = 'Alias_Status';
    const ALIAS_STOREPERMANENTLY = 'Alias_StorePermanently';
    const ALIAS_NCERROR = 'Alias_NCError';
    const ALIAS_NCERROR_CARD_NO = 'Alias_NCErrorCardNo';
    const CARD_BRAND = 'Card_Brand';
    const CARD_NUMBER = 'Card_CardNumber';
    const CARD_CN = 'Card_CardHolderName';
    const CARD_BIN = 'Card_Bin';
    const CARD_EXPIRY_DATE = 'Card_ExpiryDate';

    /**
     * Result of the alias creation
     */
    const ALIAS_STATUS_OK = 0;
    const ALIAS_STATUS_NOK = 1;
    const ALIAS_STATUS_UPDATED = 2;
    const ALIAS_STATUS_CANCELLED = 3;

    /**
     * 3DS Options
     */
    const WIN3DS_MAIN = 'MAINW';
    const WIN3DS_POPUP = 'POPUP';
    const WIN3DS_POPIX = 'POPIX';

    /**
     * Account creation link language mapping
     */
    public static $accountCreationLangCodes = [
        'en' => 1,
        'fr' => 2,
        'nl' => 3,
        'it' => 4,
        'de' => 5,
        'es' => 6
    ];

    /**
     * Allowed languages
     * @var array
     */
    public static $allowedLanguages = [
        'en_US' => 'English', 'cs_CZ' => 'Czech', 'de_DE' => 'German',
        'dk_DK' => 'Danish', 'el_GR' => 'Greek', 'es_ES' => 'Spanish',
        'fr_FR' => 'French', 'it_IT' => 'Italian', 'ja_JP' => 'Japanese',
        'nl_BE' => 'Flemish', 'nl_NL' => 'Dutch', 'no_NO' => 'Norwegian',
        'pl_PL' => 'Polish', 'pt_PT' => 'Portugese', 'ru_RU' => 'Russian',
        'se_SE' => 'Swedish', 'sk_SK' => 'Slovak', 'tr_TR' => 'Turkish',
    ];

    /**
     * @var ConnectorInterface
     */
    private $extension;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var string
     */
    private $mail_templates_directory;

    /**
     * @var string
     */
    public $api_ecommerce_test = 'https://ogone.test.v-psp.com/ncol/test/orderstandard_utf8.asp';

    /**
     * @var string
     */
    public $api_ecommerce_prod = 'https://secure.ogone.com/ncol/prod/orderstandard_utf8.asp';

    /**
     * @var string
     */
    public $api_flexcheckout_test = 'https://ogone.test.v-psp.com/Tokenization/HostedPage';

    /**
     * @var string
     */
    public $api_flexcheckout_prod = 'https://secure.ogone.com/Tokenization/HostedPage';

    /**
     * @var string
     */
    public $api_querydirect_test = 'https://secure.ogone.com/ncol/test/querydirect_utf8.asp';

    /**
     * @var string
     */
    public $api_querydirect_prod = 'https://secure.ogone.com/ncol/prod/querydirect_utf8.asp';

    /**
     * @var string
     */
    public $api_orderdirect_test = 'https://secure.ogone.com/ncol/test/orderdirect_utf8.asp';

    /**
     * @var string
     */
    public $api_orderdirect_prod = 'https://secure.ogone.com/ncol/prod/orderdirect_utf8.asp';

    /**
     * @var string
     */
    public $api_maintenancedirect_test = 'https://secure.ogone.com/ncol/test/maintenancedirect_utf8.asp';

    /**
     * @var string
     */
    public $api_maintenancedirect_prod = 'https://secure.ogone.com/ncol/prod/maintenancedirect_utf8.asp';

    /**
     * @var string
     */
    public $api_alias_test = 'https://secure.ogone.com/ncol/test/alias_gateway_utf8.asp';

    /**
     * @var string
     */
    public $api_alias_prod = 'https://secure.ogone.com/ncol/prod/alias_gateway_utf8.asp';

    /**
     * IngenicoCoreLibrary constructor.
     *
     * @param ConnectorInterface $extension
     */
    public function __construct(ConnectorInterface $extension)
    {
        $this->logger = new Logger(new FileAdapter(['file' => sys_get_temp_dir() . '/ingenico_core.log']));
        $this->extension = $extension;

        // Initialize settings
        $this->configuration = new Configuration($this->extension, $this);
        $this->configuration->load($this->extension->requestSettings($this->extension->requestSettingsMode()));

        $this->request = new Request($_REQUEST);

        // Load environment
        $env = parse_ini_file(__DIR__ . '/../environments.ini', true);
        $environment = $env[$extension->getPlatformEnvironment()];

        // Ecommerce API
        $this->api_ecommerce_test = $environment['ecommerce_test'];
        $this->api_ecommerce_prod = $environment['ecommerce_prod'];

        // Flexcheckout
        $this->api_flexcheckout_test = $environment['flexcheckout_test'];
        $this->api_flexcheckout_prod = $environment['flexcheckout_prod'];

        // Query Direct
        $this->api_querydirect_test = $environment['querydirect_test'];
        $this->api_querydirect_prod = $environment['querydirect_prod'];

        // Order Direct
        $this->api_orderdirect_test = $environment['orderdirect_test'];
        $this->api_orderdirect_prod = $environment['orderdirect_prod'];

        // Maintenance Direct
        $this->api_maintenancedirect_test = $environment['maintenancedirect_test'];
        $this->api_maintenancedirect_prod = $environment['maintenancedirect_prod'];

        // Alias
        $this->api_alias_test = $environment['alias_test'];
        $this->api_alias_prod = $environment['alias_prod'];
    }

    /**
     * Gets Logger.
     * @deprecated
     *
     * @return \Psr\Log\LoggerInterface|null
     */
    public function getLogger()
    {
        return null;
    }

    /**
     * Sets Logger.
     * @deprecated Use setLogAdapter() method instead of
     *
     * @param \Psr\Log\LoggerInterface|null $logger
     *
     * @return $this
     */
    public function setLogger($logger = null)
    {
        if ($logger) {
            return $this->setLogAdapter(new MonologAdapter([
                'logger' => $logger
            ]));
        }

        return $this;
    }

    /**
     * Set Log Adapter.
     *
     * @param AdapterInterface $adapter
     * @return $this
     */
    public function setLogAdapter(AdapterInterface $adapter)
    {
        $this->logger = new Logger($adapter);

        return $this;
    }

    /**
     * Translate string.
     *
     * @param $id
     * @param array $parameters
     * @param string|null $domain
     * @param string|null $locale
     * @return string
     */
    public function __($id, array $parameters = [], $domain = null, $locale = null)
    {
        if (!$locale) {
            $locale = 'en_US';
        }

        if (!$domain) {
            $domain = 'messages';
        }

        // Get current locale
        $currentLocale = setlocale(LC_MESSAGES, 0);

        putenv('LC_MESSAGES=' . $locale);
        $result = setlocale(LC_MESSAGES, $locale);
        if (!$result && !stristr(PHP_OS, 'WIN')) {
            // Unable to set locale, so unable to use gettext
            // Use failback mode
            $message = $id;
            $messages = $this->getAllTranslations($locale, $domain);
            if (isset($messages[$id])) {
                $message = $messages[$id];
            }
        } else {
            bindtextdomain($domain, realpath(__DIR__ . '/../translations'));
            bind_textdomain_codeset($domain, 'UTF-8');
            textdomain($domain);

            // Translate
            $message = gettext($id);

            // Set previous locale
            putenv('LC_MESSAGES=' . $currentLocale);
            setlocale(LC_ALL, $currentLocale);
        }

        if (count($parameters) > 0) {
            // Format
            $message = str_replace(array_keys($parameters), array_values($parameters), $id);
        }

        return $message;
    }

    /**
     * Get All Translations.
     *
     * @param string $locale
     * @param string|null $domain
     * @return array
     */
    public function getAllTranslations($locale, $domain = null)
    {
        if (!$domain) {
            $translations = [];

            // Scan po files
            $domains = glob(__DIR__ . '/../translations/' . $locale . '/LC_MESSAGES/*.po');
            foreach ($domains as $domain) {
                $domain = basename($domain, '.po');
                $translations = array_merge(
                    $translations,
                    $this->getAllTranslations($locale, $domain)
                );
            }

            return $translations;
        }

        // Parse po file and extract translations
        $messages = [];
        $file = __DIR__ . '/../translations/' . $locale . '/LC_MESSAGES/' . $domain . '.po';
        if (!file_exists($file)) {
            return $messages;
        }

        $id = null;
        $stream = fopen($file, 'r');
        while ($line = fgets($stream)) {
            $line = trim($line);

            if ('' === $line) {
                // Skip it
            } elseif ('#,' === substr($line, 0, 2)) {
                // Skip it
            } elseif ('msgid "' === substr($line, 0, 7)) {
                $id = substr($line, 7, -1);
            } elseif ('msgstr "' === substr($line, 0, 8)) {
                if ($id) {
                    $messages[$id] = substr($line, 8, -1);
                }

                $id = null;
            } elseif ('"' === $line[0]) {
                // Skip it
            } elseif ('msgid_plural "' === substr($line, 0, 14)) {
                // Skip it
            } elseif ('msgstr[' === substr($line, 0, 7)) {
                // Skip it
            }
        }

        fclose($stream);

        return $messages;
    }

    /**
     * Get Error Description.
     *
     * @param $errorCode
     * @return mixed|string
     */
    public static function getErrorDescription($errorCode)
    {
        $errorCodes = json_decode(
            file_get_contents(__DIR__ . '/../error-codes.json'),
            true
        );

        if (isset($errorCodes[$errorCode])) {
            return $errorCodes[$errorCode];
        }

        return 'Unknown';
    }

    /**
     * Get Default Settings.
     *
     * @return array
     */
    public function getDefaultSettings()
    {
        return $this->configuration->getDefault();
    }

    /**
     * Get Configuration instance.
     *
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Set Generic Merchant Country.
     *
     * @param $country
     * @return Configuration
     * @throws Exception
     */
    public function setGenericCountry($country)
    {
        return $this->configuration
            ->setData('generic_country', $country)
            ->save();
    }

    /**
     * Get Generic Merchant Country.
     * @return string|null
     */
    public function getGenericCountry()
    {
        if (method_exists($this->extension, 'getGenericCountry')) {
            return $this->extension->getGenericCountry();
        }

        // Use save generic country
        return $this->configuration->getData('generic_country');
    }

    /**
     * Set Mail Templates Directory
     * @param string $templates_directory
     * @return $this
     */
    public function setMailTemplatesDirectory($templates_directory)
    {
        $this->mail_templates_directory = $templates_directory;

        return $this;
    }

    /**
     * Get Mail Templates Directory
     *
     * @return string
     */
    public function getMailTemplatesDirectory()
    {
        return $this->mail_templates_directory;
    }

    /**
     * Returns array with cancel, accept,
     * exception and back url.
     *
     * @param mixed $orderId
     * @param string|null $paymentMode
     * @return ReturnUrl
     */
    private function requestReturnUrls($orderId, $paymentMode = null)
    {
        if (!$orderId) {
            $orderId = $this->extension->requestOrderId();
        }

        if (!$paymentMode) {
            $paymentMode = $this->configuration->getPaymentpageType();
        }

        return new ReturnUrl([
            ReturnUrl::ACCEPT_URL => $this->extension->buildPlatformUrl(self::CONTROLLER_TYPE_SUCCESS, [
                'order_id' => $orderId,
                'payment_mode' => $paymentMode,
                'return_state' => self::RETURN_STATE_ACCEPT,
            ]),
            ReturnUrl::DECLINE_URL => $this->extension->buildPlatformUrl(self::CONTROLLER_TYPE_SUCCESS, [
                'order_id' => $orderId,
                'payment_mode' => $paymentMode,
                'return_state' => self::RETURN_STATE_DECLINE,
            ]),
            ReturnUrl::EXCEPTION_URL => $this->extension->buildPlatformUrl(self::CONTROLLER_TYPE_SUCCESS, [
                'order_id' => $orderId,
                'payment_mode' => $paymentMode,
                'return_state' => self::RETURN_STATE_EXCEPTION,
            ]),
            ReturnUrl::CANCEL_URL => $this->extension->buildPlatformUrl(self::CONTROLLER_TYPE_SUCCESS, [
                'order_id' => $orderId,
                'payment_mode' => $paymentMode,
                'return_state' => self::RETURN_STATE_CANCEL,
            ]),
            ReturnUrl::BACK_URL => $this->extension->buildPlatformUrl(self::CONTROLLER_TYPE_SUCCESS, [
                'order_id' => $orderId,
                'payment_mode' => $paymentMode,
                'return_state' => self::RETURN_STATE_BACK,
            ]),
        ]);
    }

    /**
     * Process Return Urls.
     *
     * Execute when customer made payment. And payment gateway redirect customer back to Merchant shop.
     * We're should check payment status. And update order status.
     *
     * @return void
     * @throws Exception
     */
    public function processReturnUrls()
    {
        $paymentMode = isset($_REQUEST['payment_mode']) ? $_REQUEST['payment_mode'] : null;
        $returnState = isset($_REQUEST['return_state']) ? $_REQUEST['return_state'] : null;

        switch ($returnState) {
            case self::RETURN_STATE_ACCEPT:
                // When "Skip security check (CVV & 3D Secure)" is enabled then
                // we process saved Alias on the plugin (merchant) side.
                // If payment gateway requested 3DSecure then we should use common method (Redirect)
                // to pass 3DSecure validation.
                // Workaround for 3DSecure mode and Inline method
                if ($paymentMode === self::PAYMENT_MODE_INLINE && $this->request->hasComplus()) {
                    $paymentMode = self::PAYMENT_MODE_REDIRECT;
                }

                // Workaround for Inline method and Redirect failback.
                // Some payment methods don't support Inline payment so we are forcing Redirect return handler.
                if ($paymentMode === self::PAYMENT_MODE_INLINE && $this->request->getPayId() !== null) {
                    $paymentMode = self::PAYMENT_MODE_REDIRECT;
                }

                // Handle return
                // We're should check payment status. And update order status.
                if ($paymentMode === self::PAYMENT_MODE_REDIRECT) {
                    $this->processReturnRedirect();
                } else {
                    // Charge using Alias and final order payment validation.
                    // Uses for Alias payments and Inline (Flex Checkout).
                    $this->processReturnInline();
                }
                break;
            case self::RETURN_STATE_CANCEL:
            case self::RETURN_STATE_BACK:
                // Or customer wants cancel.
                $this->logger->debug(sprintf(
                    '%s %s Order #%s triggered to be cancelled by customer.',
                    __METHOD__,
                    __LINE__,
                    $_REQUEST['order_id']
                ));

                $this->extension->showCancellationTemplate(
                    [
                        Connector::PARAM_NAME_ORDER_ID => $_REQUEST['order_id'],
                        Connector::PARAM_NAME_MESSAGE => $this->__('checkout.payment_cancelled', [], 'messages')
                    ],
                    new Payment([
                        Payment::FIELD_ORDER_ID => $_REQUEST['order_id'],
                        Payment::FIELD_STATUS => 1 // Cancelled by customer
                    ])
                );
                break;
            case self::RETURN_STATE_DECLINE:
            case self::RETURN_STATE_EXCEPTION:
                // Error occurred
                $payment = new Payment($_REQUEST);

                // Error for Inline/FlexCheckout
                if (isset($_REQUEST[self::ALIAS_NCERROR])) {
                    $payment->setNcError($_REQUEST[self::ALIAS_NCERROR]);
                }

                // Error for Inline/FlexCheckout, CardError
                if (isset($_REQUEST[self::ALIAS_NCERROR_CARD_NO])) {
                    $payment->setNcError($_REQUEST[self::ALIAS_NCERROR_CARD_NO]);
                }

                // Cancel button press in Inline cc iframe - Payment is not created yet
                if (!$payment->getPayId()) {
                    $this->extension->showPaymentErrorTemplate(
                        [
                            Connector::PARAM_NAME_ORDER_ID => null,
                            Connector::PARAM_NAME_PAY_ID => null,
                            Connector::PARAM_NAME_MESSAGE => $this->__('checkout.payment_cancelled', [], 'messages')
                        ],
                        $payment
                    );

                    return;
                }

                // Debug log
                $this->logger->debug(sprintf(
                    '%s %s An error occurred. PaymentID: %s. Status: %s. Details: %s %s.',
                    __METHOD__,
                    __LINE__,
                    $payment->getPayId(),
                    $payment->getStatus(),
                    $payment->getErrorCode(),
                    $payment->getErrorMessage()
                ), [$payment->toArray(), $_GET, $_POST]);

                $this->extension->showPaymentErrorTemplate(
                    [
                        Connector::PARAM_NAME_ORDER_ID => $payment->getOrderId(),
                        Connector::PARAM_NAME_PAY_ID => $payment->getPayId(),
                        Connector::PARAM_NAME_MESSAGE => $this->__('checkout.error', [
                            '%payment_id%' => (int) $payment->getPayId(),
                            '%status%' => $payment->getStatus(),
                            '%code%' => $payment->getErrorCode(),
                            '%message%' => $payment->getErrorMessage()
                        ], 'messages')
                    ],
                    $payment
                );

                break;
        }
    }

    /**
     * Process Redirect payment return request.
     * Check transaction results. Finalize order status.
     * Result: Redirect to Order Success/Cancelled Page.
     *
     * @return void
     * @throws Exception
     */
    private function processReturnRedirect()
    {
        $valid = $this->validatePaymentResponse($_REQUEST);
        if ($_REQUEST['BRAND'] === 'Bancontact/Mister Cash') {
            // @todo Temporary bypass it. Strange, but validation failed for Bancontact.
            $valid = true;
        }

        if (!$valid) {
            throw new Exception('Validation of payment response is failed.');
        }

        // Workaround: Bancontact returns 'Bancontact/Mister Cash' as brand instead of BCMC
        if (isset($_REQUEST['BRAND']) && $_REQUEST['BRAND'] === 'Bancontact/Mister Cash') {
            $_REQUEST['BRAND'] = 'BCMC';
        }

        // Get Payment status
        $orderId = $_REQUEST['orderID'];
        $payId = $_REQUEST['PAYID'];
        $payIdSub = $_REQUEST['PAYIDSUB'] ?? null;
        $paymentResult = $this->getPaymentInfo($orderId, $payId, $payIdSub);

        // Save payment results and update order status
        $this->finaliseOrderPayment($orderId, $paymentResult);

        // Save Alias
        if ($this->configuration->getSettingsOneclick()) {
            $aliasData = [
                'ALIAS' => $_REQUEST[Payment::FIELD_ALIAS] ?? '',
                'BRAND' => $_REQUEST[Payment::FIELD_BRAND] ?? '',
                'CARDNO' => $_REQUEST[Payment::FIELD_CARD_NO] ?? '',
                'CN' => $_REQUEST[Payment::FIELD_CN] ?? '',
                'BIN' => $_REQUEST[Payment::FIELD_BIN] ?? '',
                'PM' => $_REQUEST[Payment::FIELD_PM] ?? '',
                'ED' => $_REQUEST[Payment::FIELD_ED] ?? '',
            ];

            // Patch Alias data for Carte Bancaire
            if ($this->extension->getOrderPaymentMethod($orderId) === CarteBancaire::CODE) {
                $aliasData['BRAND'] = 'CB';
            }

            $this->processAlias($orderId, $aliasData);
        }

        // Check is Payment Successful
        if ($paymentResult->isPaymentSuccessful()) {
            // Clean up OpenInvoice session values
            $session = $this->extension->getSessionValues();
            foreach ($session as $key => $value) {
                if (strpos($key, 'open_invoice_') !== false) {
                    $this->extension->unsetSessionValue($key);
                }
            }

            // Show "Order success" page
            $this->extension->showSuccessTemplate(
                [
                    'type' => IngenicoCoreLibrary::PAYMENT_MODE_REDIRECT,
                    'order_id' => $orderId,
                    'pay_id' => $payId,
                    'payment_status' => $paymentResult->getPaymentStatus(),
                    'is_show_warning' => $paymentResult->getPaymentStatus() === self::STATUS_AUTHORIZED &&
                        $this->configuration->isTestMode()
                ],
                $paymentResult
            );
        } elseif ($paymentResult->isPaymentCancelled()) {
            // Show "Order cancelled" page
            $this->extension->showCancellationTemplate(
                [
                    Connector::PARAM_NAME_ORDER_ID => $orderId,
                    Connector::PARAM_NAME_PAY_ID => $payId,
                    Connector::PARAM_NAME_MESSAGE => $this->__('checkout.payment_cancelled', [], 'messages')
                ],
                $paymentResult
            );
        } else {
            // Show "Payment error" page
            // Payment error or declined.
            $this->extension->showPaymentErrorTemplate(
                [
                    Connector::PARAM_NAME_ORDER_ID => $orderId,
                    Connector::PARAM_NAME_PAY_ID => $payId,
                    Connector::PARAM_NAME_MESSAGE => $this->__('checkout.error', [
                        '%payment_id%' => (int) $paymentResult->getPayId(),
                        '%status%' => $paymentResult->getStatus(),
                        '%code%' => $paymentResult->getErrorCode(),
                        '%message%' => $paymentResult->getErrorMessage()
                    ], 'messages')
                ],
                $paymentResult
            );
        }
    }

    /**
     * Process Inline payment return request.
     *
     * @return void
     * @throws Exception
     */
    private function processReturnInline()
    {
        $orderId = $_REQUEST[self::ALIAS_ORDERID];
        $aliasId = $_REQUEST[self::ALIAS_ID];
        if (empty($orderId) || empty($aliasId)) {
            throw new Exception('Validation error');
        }

        // Get Card Brand
        // Workaround: Bancontact returns 'Bancontact/Mister Cash' as brand instead of BCMC
        if (isset($_REQUEST[self::CARD_BRAND])) {
            if ($_REQUEST[self::CARD_BRAND] === 'Bancontact/Mister Cash') {
                $_REQUEST[self::CARD_BRAND] = 'BCMC';
            }
        } else {
            $_REQUEST[self::CARD_BRAND] = null;
        }

        // Save Alias
        if ($this->configuration->getSettingsOneclick()) {
            if (isset($_REQUEST[self::ALIAS_ID]) &&
                $_REQUEST[self::ALIAS_STOREPERMANENTLY] === 'Y' &&
                in_array($_REQUEST[self::ALIAS_STATUS], [self::ALIAS_STATUS_OK, self::ALIAS_STATUS_UPDATED])
            ) {
                $aliasData = [
                    'ALIAS' => $_REQUEST[self::ALIAS_ID] ?? '',
                    'BRAND' => $_REQUEST[self::CARD_BRAND] ?? '',
                    'CARDNO' => $_REQUEST[self::CARD_NUMBER] ?? '',
                    'CN' => $_REQUEST[self::CARD_CN] ?? '',
                    'BIN' => $_REQUEST[self::CARD_BIN] ?? '',
                    'PM' => 'CreditCard',
                    'ED' => $_REQUEST[self::CARD_EXPIRY_DATE] ?? '',
                ];

                // Patch Alias data for Carte Bancaire
                if ($this->extension->isOrderCreated($orderId)) {
                    if ($this->extension->getOrderPaymentMethod($orderId) === CarteBancaire::CODE) {
                        $aliasData['BRAND'] = 'CB';
                    }
                } else {
                    if ($this->extension->getQuotePaymentMethod(null) === CarteBancaire::CODE) {
                        $aliasData['BRAND'] = 'CB';
                    }
                }

                $this->processAlias($orderId, $aliasData);
            }
        }

        // Save Alias parameters to session to future usage in the "finishReturnInline" method
        // Try to load alias
        $alias = $this->getAlias($aliasId);

        // Build Alias if it is not exists
        if (!$alias->getAlias()) {
            $alias->setAlias($aliasId);
        }

        // Get PaymentMethod by Card Brand
        if (isset($_REQUEST[self::CARD_BRAND])) {
            $cardBrand = $_REQUEST[self::CARD_BRAND];
            $paymentMethod = $this->getPaymentMethodByBrand($cardBrand);
            if ($paymentMethod) {
                $alias->setPaymentId($paymentMethod->getId())
                    ->setPm($paymentMethod->getPM());
            }

            $alias->setBrand($cardBrand);
        }

        $alias->setCn($_REQUEST[self::CARD_CN] ?? null)
            ->setForceSecurity(true);

        // Save Alias in the session
        $this->setSessionValue('Alias_' . $aliasId, $alias);

        // Alias saved (or not if customer choose it). But we're should charge payment using Ajax.
        // Show loader
        $this->extension->showInlineLoaderTemplate(
            [
                Connector::PARAM_NAME_TYPE => IngenicoCoreLibrary::PAYMENT_MODE_INLINE,
                Connector::PARAM_NAME_ORDER_ID => $_REQUEST[self::ALIAS_ORDERID],
                Connector::PARAM_NAME_ALIAS_ID => $_REQUEST[self::ALIAS_ID],
                Connector::PARAM_CARD_BRAND => $_REQUEST[self::CARD_BRAND] ?? '',
                Connector::PARAM_CARD_CN => $_REQUEST[self::CARD_CN] ?? '',
                Connector::PARAM_DATA => $_REQUEST
            ]
        );
    }

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
    public function finishReturnInline($orderId, $cardBrand, $aliasId)
    {
        // Check the saved alias in the session
        $alias = $this->getSessionValue('Alias_' . $aliasId);

        if (is_object($alias) && $alias instanceof Alias) {
            // Destroy the alias in the session
            $this->unsetSessionValue('Alias_' . $aliasId);
        } else {
            // Try to load the saved alias
            $alias = $this->getAlias($aliasId);

            // Build Alias if it is not exists
            if (!$alias->getAlias()) {
                $alias->setAlias($aliasId);
            }

            // Get PaymentMethod by Card Brand
            if (isset($_REQUEST[self::CARD_BRAND])) {
                $cardBrand = $_REQUEST[self::CARD_BRAND];
                $paymentMethod = $this->getPaymentMethodByBrand($cardBrand);
                if ($paymentMethod) {
                    $alias->setPaymentId($paymentMethod->getId())
                        ->setPm($paymentMethod->getPM());
                }

                $alias->setBrand($cardBrand);
            }

            $alias->setForceSecurity(true);
        }

        // Charge payment using Alias
        $paymentResult = $this->executePayment($orderId, $alias);

        // 3DSecure Validation required
        if ($paymentResult->isSecurityCheckRequired()) {
            return [
                'status' => '3ds_required',
                'order_id' => $orderId,
                'pay_id' => $paymentResult->getPayId(),
                'html' => $paymentResult->getSecurityHTML(),
            ];
        }

        if (!$paymentResult->isTransactionSuccessful()) {
            $message = $this->__('checkout.error', [
                '%payment_id%' => $paymentResult->getPayId(),
                '%status%' => $paymentResult->getStatus(),
                '%code%' => $paymentResult->getErrorCode(),
                '%message%' => $paymentResult->getErrorMessage()
            ], 'messages');

            $this->logger->debug(
                sprintf(
                    '%s %s Error: An error occurred. PaymentID: %s. Status: %s. Details: %s %s.',
                    __METHOD__,
                    __LINE__,
                    $paymentResult->getPayId(),
                    $paymentResult->getStatus(),
                    $paymentResult->getErrorCode(),
                    $paymentResult->getErrorMessage()
                ),
                [$paymentResult->toArray(), $_GET, $_POST]
            );

            return [
                'status' => 'error',
                'message' => $message,
                'order_id' => $orderId,
                'pay_id' => $paymentResult->getPayId(),
                'redirect' => $this->extension->buildPlatformUrl(self::CONTROLLER_TYPE_ORDER_CANCELLED, [
                    'order_id' => $orderId
                ]),
            ];
        }

        // Get payment ID
        $payId = $paymentResult->getPayID();

        // Save payment results and update order status
        $this->finaliseOrderPayment($orderId, $paymentResult);

        // Check is Payment Successful
        if ($paymentResult->isPaymentSuccessful()) {
            $this->extension->emptyShoppingCart();

            return [
                'status' => 'success',
                'order_id' => $orderId,
                'pay_id' => $payId,
                'payment_status' => $paymentResult->getPaymentStatus(),
                'redirect' => $this->extension->buildPlatformUrl(self::CONTROLLER_TYPE_ORDER_SUCCESS, [
                    'order_id' => $orderId
                ]),
                'is_show_warning' => $paymentResult->getPaymentStatus() === self::STATUS_AUTHORIZED &&
                    $this->configuration->isTestMode()
            ];
        } elseif ($paymentResult->isPaymentCancelled()) {
            // Cancelled
            $this->extension->restoreShoppingCart();

            return [
                'status' => 'cancelled',
                'order_id' => $orderId,
                'pay_id' => $payId,
                'redirect' => $this->extension->buildPlatformUrl(self::CONTROLLER_TYPE_ORDER_CANCELLED, [
                    'order_id' => $orderId
                ])
            ];
        } else {
            // Payment error or declined.
            $this->extension->restoreShoppingCart();

            return [
                'status' => 'error',
                'order_id' => $orderId,
                'pay_id' => $payId,
                'message' => $this->__('checkout.error', [
                    '%payment_id%' => (int) $paymentResult->getPayId(),
                    '%status%' => $paymentResult->getStatus(),
                    '%code%' => $paymentResult->getErrorCode(),
                    '%message%' => $paymentResult->getErrorMessage()
                ], 'messages'),
                'redirect' => $this->extension->buildPlatformUrl(self::CONTROLLER_TYPE_ORDER_CANCELLED, [
                    'order_id' => $orderId
                ])
            ];
        }
    }

    /**
     * Process Payment Confirmation
     * Execute when customer submit checkout form.
     * We're should initialize payment and display payment form for customer.
     *
     * @param mixed $orderId
     * @param mixed $aliasId
     * @param bool $forceAliasSave
     *
     * @throws Exception
     * @return void
     */
    public function processPayment($orderId, $aliasId = null, $forceAliasSave = false)
    {
        // Get Payment Mode
        $payment_mode = $this->configuration->getPaymentpageType();

        // Check is Alias Payment mode
        // When "Skip security check (CVV & 3D Secure)" is enabled then process saved Alias on Merchant side.
        if ($this->configuration->getSettingsOneclick() &&
            $this->configuration->getSettingsSkipsecuritycheck() &&
            $aliasId && !empty($aliasId) && $aliasId !== self::ALIAS_CREATE_NEW) {
            $payment_mode = self::PAYMENT_MODE_ALIAS;
        }

        switch ($payment_mode) {
            case self::PAYMENT_MODE_REDIRECT:
                $this->processPaymentRedirect($orderId, $aliasId, $forceAliasSave);
                break;
            case self::PAYMENT_MODE_INLINE:
                $this->processPaymentInline($orderId, $aliasId, $forceAliasSave);
                break;
            case self::PAYMENT_MODE_ALIAS:
                $this->processPaymentAlias($orderId, $aliasId);
                break;
            default:
                throw new Exception('Unknown payment type');
        }
    }

    /**
     * Process Payment Confirmation: Redirect
     *
     * @param mixed $orderId
     * @param mixed $aliasId
     * @param bool $forceAliasSave
     * @throws Exception
     * @return void
     */
    public function processPaymentRedirect($orderId, $aliasId = null, $forceAliasSave = false)
    {
        if ($this->configuration->getSettingsOneclick()) {
            // Customer chose the saved alias
            $aliasUsage = $this->__('core.authorization_usage');
            if (!empty($aliasId) && $aliasId !== self::ALIAS_CREATE_NEW) {
                // Payment with Saved Alias
                $alias = $this->getAlias($aliasId);
                if (!$alias->getId()) {
                    throw new Exception($this->__('exceptions.alias_none'));
                }

                // Check Access
                if ($alias->getCustomerId() != $this->extension->requestCustomerId()) {
                    throw new Exception($this->__('exceptions.access_denied'));
                }

                $alias->setOperation(Alias::OPERATION_BY_PSP)
                    ->setUsage($aliasUsage);
            } else {
                // New alias will be saved
                $alias = new Alias();
                $alias->setIsShouldStoredPermanently(true)
                    ->setOperation(Alias::OPERATION_BY_PSP)
                    ->setUsage($aliasUsage);
            }
        } else {
            $alias = new Alias();
            $alias->setIsPreventStoring(true);
        }

        if ($forceAliasSave && !$alias->getIsShouldStoredPermanently()) {
             $alias->setIsShouldStoredPermanently($forceAliasSave);
        }

        // Initiate Redirect Payment
        $order = $this->getOrder($orderId);
        $paymentRequest = $this->getHostedCheckoutPaymentRequest($order, $alias);

        // Prepare the form fields
        $fields = $paymentRequest->toArray();
        $fields['SHASIGN'] = $paymentRequest->getShaSign();

        // Show page with list of payment methods
        $this->extension->showPaymentListRedirectTemplate([
            Connector::PARAM_NAME_ORDER_ID => $orderId,
            Connector::PARAM_NAME_URL => $paymentRequest->getOgoneUri(),
            Connector::PARAM_NAME_FIELDS => $fields
        ]);
    }

    /**
     * Process Payment Confirmation: Redirect with specified PM/Brand.
     *
     * @param mixed $orderId
     * @param mixed $aliasId
     * @param       $paymentMethod
     * @param       $brand
     *
     * @throws Exception
     * @return void
     */
    public function processPaymentRedirectSpecified($orderId, $aliasId, $paymentMethod, $brand)
    {
        // Initiate Redirect Payment
        $data = $this->getSpecifiedRedirectPaymentRequest($orderId, $aliasId, $paymentMethod, $brand);

        // Show page with list of payment methods
        $this->extension->showPaymentListRedirectTemplate([
            Connector::PARAM_NAME_ORDER_ID => $orderId,
            Connector::PARAM_NAME_URL => $data->getUrl(),
            Connector::PARAM_NAME_FIELDS => $data->getFields()
        ]);
    }

    /**
     * Process Payment Confirmation: Inline
     *
     * @param mixed $orderId
     * @param mixed $aliasId
     * @param bool $forceAliasSave
     * @return void
     * @throws Exception
     */
    public function processPaymentInline($orderId, $aliasId, $forceAliasSave = false)
    {
        // One Click Payments
        if ($this->configuration->getSettingsOneclick()) {
            // Customer chose the saved alias
            if (!empty($aliasId) && $aliasId !== self::ALIAS_CREATE_NEW) {
                // Payment with the saved alias
                $alias = $this->getAlias($aliasId);
                if (!$alias->getId()) {
                    throw new Exception($this->__('exceptions.alias_none'));
                }

                // Check Access
                if ($alias->getCustomerId() != $this->extension->requestCustomerId()) {
                    throw new Exception($this->__('exceptions.access_denied'));
                }
            } else {
                // New alias will be saved
                $alias = new Alias();
                $alias->setIsShouldStoredPermanently(true);
            }
        } else {
            // Single-use Alias
            $alias = new Alias();
            $alias->setIsShouldStoredPermanently(false);
        }

        if (!$alias->getIsShouldStoredPermanently()) {
            $alias->setIsShouldStoredPermanently($forceAliasSave);
        }

        // Get Inline Payment Methods
        $inlineMethods = $this->getInlinePaymentMethods($orderId, $alias);

        // Show page with list of payment methods
        $this->extension->showPaymentListInlineTemplate([
            Connector::PARAM_NAME_ORDER_ID => $orderId,
            Connector::PARAM_NAME_CATEGORIES => $this->getPaymentCategories(),
            Connector::PARAM_NAME_METHODS => $inlineMethods,
            Connector::PARAM_NAME_CC_URL => $this->getInlineIFrameUrl(
                $orderId,
                $alias->setPm('CreditCard')->setBrand('')
            )
        ]);
    }

    /**
     * Get Inline iFrame Urls For Selected Payment Methods
     *
     * @param $reservedOrderId
     */
    public function getCcIFrameUrlBeforePlaceOrder($reservedOrderId)
    {
        $alias = new Alias();
        $alias->setIsShouldStoredPermanently(true)
            ->setPm('CreditCard')
            ->setBrand('');

        // Initiate FlexCheckout Payment Request
        $order = $this->getOrderBeforePlaceOrder($reservedOrderId);

        $request = $this->getFlexCheckoutPaymentRequest($order, $alias);
        $request->setShaSign();
        $request->validate();

        return $request->getCheckoutUrl();
    }

    /**
     * Process Payment Confirmation: Alias
     *
     * @param mixed $orderId
     * @param mixed $aliasId
     * @return void
     * @throws \Exception
     */
    private function processPaymentAlias($orderId, $aliasId = null)
    {
        // Load Alias
        $alias = $this->getAlias($aliasId);
        if (!$alias->getId()) {
            throw new Exception($this->__('exceptions.alias_none'));
        }

        // Check Access
        if ($alias->getCustomerId() != $this->extension->requestCustomerId()) {
            throw new Exception($this->__('exceptions.access_denied'));
        }

        // We should use BY_MERCHANT for secondary transactions
        $alias->setOperation(Alias::OPERATION_BY_MERCHANT)
            ->setUsage($this->__('core.authorization_usage'));

        // Charge payment using Alias
        $paymentResult = $this->executePayment($orderId, $alias);

        // 3DSecure Validation required
        if ($paymentResult->isSecurityCheckRequired()) {
            $this->extension->showSecurityCheckTemplate(
                [
                    'html' => $paymentResult->getSecurityHTML()
                ],
                $paymentResult
            );
            return;
        }

        if (!$paymentResult->isTransactionSuccessful()) {
            $message = $this->__('checkout.error', [
                '%payment_id%' => $paymentResult->getPayId(),
                '%status%' => $paymentResult->getStatus(),
                '%code%' => $paymentResult->getErrorCode(),
                '%message%' => $paymentResult->getErrorMessage()
            ], 'messages');

            $this->logger->debug(
                sprintf(
                    '%s %s Error: An error occurred. PaymentID: %s. Status: %s. Details: %s %s.',
                    __METHOD__,
                    __LINE__,
                    $paymentResult->getPayId(),
                    $paymentResult->getStatus(),
                    $paymentResult->getErrorCode(),
                    $paymentResult->getErrorMessage()
                ),
                [$paymentResult->toArray(), $_GET, $_POST]
            );

            throw new Exception($message);
        }

        // Get payment ID
        $payId = $paymentResult->getPayID();

        // Save payment results and update order status
        $this->finaliseOrderPayment($orderId, $paymentResult);

        // Check is Payment Successful
        if ($paymentResult->isPaymentSuccessful()) {
            // Show "Order success" page
            $this->extension->showSuccessTemplate(
                [
                    Connector::PARAM_NAME_TYPE => IngenicoCoreLibrary::PAYMENT_MODE_INLINE,
                    Connector::PARAM_NAME_ORDER_ID => $orderId,
                    Connector::PARAM_NAME_PAY_ID => $payId,
                    Connector::PARAM_NAME_PAYMENT_STATUS => $paymentResult->getPaymentStatus(),
                    // @phpcs:ignore Generic.Files.LineLength.TooLong
                    Connector::PARAM_NAME_IS_SHOW_WARNING => $paymentResult->getPaymentStatus() === self::STATUS_AUTHORIZED &&
                        $this->configuration->isTestMode()
                ],
                $paymentResult
            );
        } elseif ($paymentResult->isPaymentCancelled()) {
            // Show "Order cancelled" page
            $this->extension->showCancellationTemplate(
                [
                    Connector::PARAM_NAME_ORDER_ID => $orderId,
                    Connector::PARAM_NAME_PAY_ID => $payId,
                    Connector::PARAM_NAME_MESSAGE => $this->__('checkout.payment_cancelled', [], 'messages')
                ],
                $paymentResult
            );
        } else {
            // Show "Payment error" page
            // Payment error or declined.
            $this->extension->showPaymentErrorTemplate(
                [
                    Connector::PARAM_NAME_ORDER_ID => $orderId,
                    Connector::PARAM_NAME_PAY_ID => $payId,
                    Connector::PARAM_NAME_MESSAGE => $this->__('checkout.error', [
                        '%payment_id%' => (int) $paymentResult->getPayId(),
                        '%status%' => $paymentResult->getStatus(),
                        '%code%' => $paymentResult->getErrorCode(),
                        '%message%' => $paymentResult->getErrorMessage()
                    ], 'messages')
                ],
                $paymentResult
            );
        }
    }

    /**
     * Get Inline Payment Methods.
     * Returns array with PaymentMethod instances.
     * Every PaymentMethod instance have getIFrameUrl() method.
     * We're use it to render iframes on checkout page.
     *
     * @param $orderId
     * @param Alias $alias
     * @return array
     */
    private function getInlinePaymentMethods($orderId, Alias $alias)
    {
        // Get selected payment methods
        $selectedPaymentMethods = $this->getSelectedPaymentMethods();

        // Get payment method by brand
        if ($alias->getBrand()) {
            try {
                $paymentMethod = $alias->getPaymentMethod();
                if ($paymentMethod) {
                    $selectedPaymentMethods = [
                        $paymentMethod->getId() => $paymentMethod
                    ];
                }
            } catch (\Exception $e) {
                // Silence is golden
            }
        }

        /**
         * @var PaymentMethod\PaymentMethod $paymentMethod
         */
        foreach ($selectedPaymentMethods as $key => $paymentMethod) {
            if (!$paymentMethod->isRedirectOnly()) {
                // Configure Alias's Payment Method and Brand
                $_alias = clone $alias;
                $_alias->setPm($paymentMethod->getPM())
                    ->setBrand($paymentMethod->getBrand());

                $url = $this->getInlineIFrameUrl($orderId, $_alias);
            } else {
                // Validate Order data for Payment Methods which require additional data
                if ($paymentMethod->getAdditionalDataRequired()) {
                    $additionalFields = $this->validateOpenInvoiceCheckoutAdditionalFields($orderId, $paymentMethod);

                    // Save missing fields
                    $paymentMethod->setMissingFields($additionalFields);
                }

                // @todo Use Id of PaymentMethod only. Remove PM and Brand.
                $url = $this->extension->buildPlatformUrl(self::CONTROLLER_TYPE_PAYMENT, [
                    Connector::PARAM_NAME_PAYMENT_ID => $paymentMethod->getId(),
                    Connector::PARAM_NAME_PM => $paymentMethod->getPM(),
                    Connector::PARAM_NAME_BRAND => $paymentMethod->getBrand()
                ]);
            }

            // Set iframe Url
            $selectedPaymentMethods[$key]->setIFrameUrl($url);
        }

        return $selectedPaymentMethods;
    }

    /**
     * Get payment status.
     *
     * @param $orderId
     * @param $payId
     * @param $payIdSub
     *
     * @return Payment
     */
    public function getPaymentInfo($orderId, $payId = null, $payIdSub = null)
    {
        $directLink = new DirectLink();
        $directLink->setLogger($this->getLogger());

        $paymentResult = $directLink->createStatusRequest($this->configuration, $orderId, $payId, $payIdSub);
        if ($paymentResult) {
            // Set payment status using IngenicoCoreLibarary::getPaymentStatus()
            $paymentResult->setPaymentStatus(
                $this->getPaymentStatus($paymentResult->getBrand(), $paymentResult->getStatus())
            );
        }

        return $paymentResult;
    }



    /**
     * Handle incoming requests by Webhook.
     * Update order's statuses by incoming request from Ingenico.
     * This method should returns http status 200/400.
     *
     * @return void
     */
    public function webhookListener()
    {
        // Implements Transaction feedback
        $this->logger->debug('Incoming POST:', $_POST);

        try {
            // Validate
            if (!$this->validatePaymentResponse($_POST)) {
                throw new Exception('WebHook: Validation failed');
            }

            // Ingenico now returns empty NCERROR if no errors found
            if (!empty($_POST['NCERROR'])) {
                $details = isset($_POST['NCERRORPLUS']) ? $_POST['NCERRORPLUS'] : '';
                throw new Exception(sprintf('NCERROR: %s. NCERRORPLUS: %s', $_POST['NCERROR'], $details));
            }

            $orderId = $_POST['orderID'];
            $payId = $_POST['PAYID'];

            // Get current order information
            $order = $this->getOrder($orderId);
            if (!$order) {
                throw new Exception(sprintf('WebHook: OrderId %s isn\'t exists.', $orderId));
            }

            // Get Payment Status
            $paymentResult = new Payment($_POST);
            $paymentStatus = $this->getPaymentStatus($paymentResult->getBrand(), $paymentResult->getStatus());

            // Process Order status
            switch ($paymentStatus) {
                case self::STATUS_REFUNDED:
                    try {
                        $refundAmount = isset($_POST['amount']) ? $_POST['amount'] : null;
                        if (!$this->canRefund($orderId, $payId, $refundAmount)) {
                            throw new Exception($this->__('exceptions.refund_unavailable'));
                        }

                        // Save payment results and update order status
                        $this->finaliseOrderPayment($orderId, $paymentResult);
                    } catch (\Exception $e) {
                        // No refund possible
                        $this->logger->debug(
                            sprintf('%s %s %s',  __METHOD__, __LINE__, $e->getMessage())
                        );
                    }
                    break;

                default:
                    try {
                        // Save payment results and update order status
                        $this->finaliseOrderPayment($orderId, $paymentResult);
                    } catch (\Exception $e) {
                        $this->logger->debug(
                            sprintf('%s %s %s', __METHOD__, __LINE__, $e->getMessage())
                        );
                    }

                    // Process Alias if payment is successful
                    if ($this->configuration->getSettingsOneclick() &&
                        $paymentResult->isPaymentSuccessful()
                    ) {
                        $aliasData = [
                            'ALIAS' => $paymentResult->getAlias(),
                            'BRAND' => $paymentResult->getBrand(),
                            'CARDNO' => $paymentResult->getCardNo(),
                            'CN' => $paymentResult->getCn(),
                            'BIN' => $paymentResult->getBin(),
                            'PM' => $paymentResult->getPm(),
                            'ED' => $paymentResult->getEd(),
                        ];

                        // Patch Alias data for Carte Bancaire
                        if ($this->extension->getOrderPaymentMethod($orderId) === CarteBancaire::CODE) {
                            $aliasData['BRAND'] = 'CB';
                        }

                        $this->processAlias($orderId, $aliasData);
                    }

                    // Notify that order status changed from "cancelled" to "paid" order
                    if (self::STATUS_CANCELLED === $order->getStatus()) {
                        $this->extension->sendOrderPaidCustomerEmail($orderId);
                        $this->extension->sendOrderPaidAdminEmail($orderId);
                    }
                    break;
            }

            http_response_code(200);
            $this->logger->debug(sprintf(
                'WebHook: Success. OrderID: %s. Status: %s',
                $orderId,
                $paymentResult->getStatus()
            ));
        } catch (\Exception $e) {
            http_response_code(400);
            $this->logger->debug(sprintf('WebHook: Error: %s', $e->getMessage()));
        }
    }

    /**
     * Get Order.
     *
     * @param mixed $orderId
     * @param array $fields Additional fields to override
     *
     * @return Order|false
     */
    private function getOrder($orderId, array $fields = [])
    {
        if (!$this->extension->isOrderCreated($orderId)) {
            return $this->getOrderBeforePlaceOrder($orderId, $fields);
        }

        $info = $this->extension->requestOrderInfo($orderId);
        if (!$info) {
            return false;
        }

        // Override order data
        if (count($fields) > 0) {
            $info = array_merge($info, $fields);
        }

        // Substitute street number
        if (in_array($this->extension->getOrderPaymentMethod($orderId), [
            Klarna::CODE,
            Afterpay::CODE,
            KlarnaBankTransfer::CODE,
            KlarnaDirectDebit::CODE,
            KlarnaFinancing::CODE,
            KlarnaPayLater::CODE,
            KlarnaPayNow::CODE,
            FacilyPay3x::CODE,
            FacilyPay3xnf::CODE,
            FacilyPay4x::CODE,
            FacilyPay4xnf::CODE,
        ])) {
            if (empty($info[OrderField::BILLING_STREET_NUMBER]) && !empty($info[OrderField::BILLING_ADDRESS1])) {
                $info[OrderField::BILLING_ADDRESS1] = preg_replace(
                    '/\s+/',
                    ' ',
                    $info[OrderField::BILLING_ADDRESS1]
                );

                // Split address automatically
                try {
                    $result = AddressSplitter::splitAddress($info[OrderField::BILLING_ADDRESS1]);
                    $info[OrderField::BILLING_ADDRESS1] = trim(sprintf('%s %s %s',
                        $result['additionToAddress1'],
                        $result['streetName'],
                        $result['additionToAddress2']
                    ));

                    $info[OrderField::BILLING_STREET_NUMBER] = trim($result['houseNumber']);
                } catch (SplittingException $e) {
                    // Ignore it
                }
            }

            // Substitute street number
            if (empty($info[OrderField::SHIPPING_STREET_NUMBER]) && !empty($info[OrderField::SHIPPING_ADDRESS1])) {
                $info[OrderField::SHIPPING_ADDRESS1] = preg_replace(
                    '/\s+/',
                    ' ',
                    $info[OrderField::SHIPPING_ADDRESS1]
                );

                // Split address automatically
                try {
                    $result = AddressSplitter::splitAddress($info[OrderField::SHIPPING_ADDRESS1]);
                    $info[OrderField::SHIPPING_ADDRESS1] = trim(sprintf('%s %s %s',
                        $result['additionToAddress1'],
                        $result['streetName'],
                        $result['additionToAddress2']
                    ));

                    $info[OrderField::SHIPPING_STREET_NUMBER] = trim($result['houseNumber']);
                } catch (SplittingException $e) {
                    // Ignore it
                }
            }
        }

        // Word-wrap of street address
        if (mb_strlen($info[OrderField::BILLING_ADDRESS1], 'UTF-8') > 35) {
            $billingAddress1 = $info[OrderField::BILLING_ADDRESS1];
            $info[OrderField::BILLING_ADDRESS1] = mb_substr($billingAddress1, 0, 35, 'UTF-8');
            $info[OrderField::BILLING_ADDRESS2] = mb_substr(trim(
                mb_substr($billingAddress1, 35, null, 'UTF-8') . ' ' . $info[OrderField::BILLING_ADDRESS2]
            ), 0, 35, 'UTF-8');
        }

        if (mb_strlen($info[OrderField::SHIPPING_ADDRESS1], 'UTF-8') > 35) {
            $shippingAddress1 = $info[OrderField::SHIPPING_ADDRESS1];
            $info[OrderField::SHIPPING_ADDRESS1] = mb_substr($shippingAddress1, 0, 35, 'UTF-8');
            $info[OrderField::SHIPPING_ADDRESS2] = mb_substr(trim(
                mb_substr($shippingAddress1, 35, null, 'UTF-8') . ' ' . $info[OrderField::SHIPPING_ADDRESS2]
            ), 0, 35, 'UTF-8');
        }

        return new Order($info);
    }
    /**
     * Get IngenicoClient's Order Before The Actual Order Is Created.
     * This Is Necessary To Show CreditCard iFrame In Checkout
     *
     * @param mixed $reservedOrderId
     * @param array $fields
     *
     * @return Order|false
     */
    private function getOrderBeforePlaceOrder($reservedOrderId, array $fields = [])
    {
        $info = $this->extension->requestOrderInfoBeforePlaceOrder($reservedOrderId);
        if (!$info) {
            return false;
        }

        // Override order data
        if (count($fields) > 0) {
            $info = array_merge($info, $fields);
        }

        // Substitute street number
        if (in_array($this->extension->getQuotePaymentMethod(null), [
            Klarna::CODE,
            Afterpay::CODE,
            KlarnaBankTransfer::CODE,
            KlarnaDirectDebit::CODE,
            KlarnaFinancing::CODE,
            KlarnaPayLater::CODE,
            KlarnaPayNow::CODE,
            FacilyPay3x::CODE,
            FacilyPay3xnf::CODE,
            FacilyPay4x::CODE,
            FacilyPay4xnf::CODE,
        ])) {
            if (empty($info[OrderField::BILLING_STREET_NUMBER]) && !empty($info[OrderField::BILLING_ADDRESS1])) {
                $info[OrderField::BILLING_ADDRESS1] = preg_replace(
                    '/\s+/',
                    ' ',
                    $info[OrderField::BILLING_ADDRESS1]
                );

                // Split address automatically
                try {
                    $result = AddressSplitter::splitAddress($info[OrderField::BILLING_ADDRESS1]);
                    $info[OrderField::BILLING_ADDRESS1] = trim(sprintf('%s %s %s',
                        $result['additionToAddress1'],
                        $result['streetName'],
                        $result['additionToAddress2']
                    ));

                    $info[OrderField::BILLING_STREET_NUMBER] = trim($result['houseNumber']);
                } catch (SplittingException $e) {
                    // Ignore it
                }
            }

            // Substitute street number
            if (empty($info[OrderField::SHIPPING_STREET_NUMBER]) && !empty($info[OrderField::SHIPPING_ADDRESS1])) {
                $info[OrderField::SHIPPING_ADDRESS1] = preg_replace(
                    '/\s+/',
                    ' ',
                    $info[OrderField::SHIPPING_ADDRESS1]
                );

                // Split address automatically
                try {
                    $result = AddressSplitter::splitAddress($info[OrderField::SHIPPING_ADDRESS1]);
                    $info[OrderField::SHIPPING_ADDRESS1] = trim(sprintf('%s %s %s',
                        $result['additionToAddress1'],
                        $result['streetName'],
                        $result['additionToAddress2']
                    ));

                    $info[OrderField::SHIPPING_STREET_NUMBER] = trim($result['houseNumber']);
                } catch (SplittingException $e) {
                    // Ignore it
                }
            }
        }

        // Word-wrap of street address
        if (mb_strlen($info[OrderField::BILLING_ADDRESS1], 'UTF-8') > 35) {
            $billingAddress1 = $info[OrderField::BILLING_ADDRESS1];
            $info[OrderField::BILLING_ADDRESS1] = mb_substr($billingAddress1, 0, 35, 'UTF-8');
            $info[OrderField::BILLING_ADDRESS2] = mb_substr(trim(
                mb_substr($billingAddress1, 35, null, 'UTF-8') . ' ' . $info[OrderField::BILLING_ADDRESS2]
            ), 0, 35, 'UTF-8');
        }

        if (mb_strlen($info[OrderField::SHIPPING_ADDRESS1], 'UTF-8') > 35) {
            $shippingAddress1 = $info[OrderField::SHIPPING_ADDRESS1];
            $info[OrderField::SHIPPING_ADDRESS1] = mb_substr($shippingAddress1, 0, 35, 'UTF-8');
            $info[OrderField::SHIPPING_ADDRESS2] = mb_substr(trim(
                mb_substr($shippingAddress1, 35, null, 'UTF-8') . ' ' . $info[OrderField::SHIPPING_ADDRESS2]
            ), 0, 35, 'UTF-8');
        }

        return new Order($info);
    }

    /**
     * Get Locale.
     *
     * @param $orderId
     *
     * @return string
     */
    private function getLocale($orderId)
    {
        $locale = $this->extension->getLocale($orderId);
        if (!in_array($locale, array_keys(self::$allowedLanguages))) {
            $locale = 'en_US';
        }

        return $locale;
    }

    /**
     * Validate Hosted Checkout return request.
     *
     * @param $request
     *
     * @return mixed
     */
    private function validatePaymentResponse($request)
    {
        return $this->validateHostedCheckoutResponse($request);
    }

    /**
     * Get Country By ISO Code
     *
     * @param $isoCode
     * @return string
     */
    public static function getCountryByCode($isoCode)
    {
        $country = (new \League\ISO3166\ISO3166)->alpha2($isoCode);
        return $country['name'];
    }

    /**
     * Get Categories of Payment Methods
     * @return array
     */
    public function getPaymentCategories()
    {
        $categories = PaymentMethod::getPaymentCategories();

        // Translate categories
        foreach ($categories as $categoryId => $label) {
            $categories[$categoryId] = $this->__($label, [], 'messages');
        }

        return $categories;
    }

    /**
     * Get Countries of Payment Methods.
     * Returns array like ['DE' => 'Germany']
     *
     * @return array
     */
    public function getAllCountries()
    {
        $countries = PaymentMethod::getAllCountries();

        // Translate categories
        foreach ($countries as $code => $label) {
            $countries[$code] = $this->__($label, [], 'messages');
        }


        return $countries;
    }

    /**
     * Get all payment methods.
     *
     * @return array
     */
    public function getPaymentMethods()
    {
        $paymentMethods = PaymentMethod::getPaymentMethods();

        // Filter Payment Methods
        /** @var PaymentMethod\PaymentMethod $paymentMethod */
        foreach ($paymentMethods as $key => $paymentMethod) {
            if ($paymentMethod->isHidden()) {
                unset($paymentMethods[$key]);
            }

            // Add branding for Generic method
            if ($paymentMethod->getId() === \IngenicoClient\PaymentMethod\Ingenico::CODE) {
                $paymentMethod->setName($this->getWhiteLabelsData()->getPlatform());
                $paymentMethod->setLogo('white-labels/' . $this->getWhiteLabelsData()->getLogo());

                $paymentMethods[$key] = $paymentMethod;
            }

            // This Payment Method don't support Inline
            // Use special page for "Redirect" payment
            if (in_array($paymentMethod->getId(), [Afterpay::CODE, Klarna::CODE])) {
                // Workaround for Afterpay and Klarna
                // Use save generic country
                $genericCountry = $this->getGenericCountry();
                if ($genericCountry) {
                    $pm = $paymentMethod->getPMByCountry($genericCountry);
                    $brand = $paymentMethod->getBrandByCountry($genericCountry);
                } else {
                    // Use DE as failback
                    $pm = $paymentMethod->getPMByCountry('DE');
                    $brand = $paymentMethod->getBrandByCountry('DE');
                }

                // Override PM/Brand
                $paymentMethod->setPM($pm)
                    ->setBrand($brand);
            }
        }

        return $paymentMethods;
    }

    /**
     * @deprecated
     * @return array
     */
    public static function getCountriesPaymentMethods()
    {
        $paymentMethods = new PaymentMethod();

        return $paymentMethods->getCountriesPaymentMethods();
    }

    /**
     * Get Payment Method by Brand.
     *
     * @param $brand
     *
     * @return PaymentMethod\PaymentMethod|false
     */
    public function getPaymentMethodByBrand($brand)
    {
        $paymentMethods = PaymentMethod::getPaymentMethodByBrand($brand, $this);

        // Add branding for Generic method
        /** @var PaymentMethod\PaymentMethod $paymentMethod */
        foreach ($paymentMethods as $key => $paymentMethod) {
            if ($paymentMethod->getId() === \IngenicoClient\PaymentMethod\Ingenico::CODE) {
                $paymentMethod->setName($this->getWhiteLabelsData()->getPlatform());
                $paymentMethod->setLogo('white-labels/' . $this->getWhiteLabelsData()->getLogo());

                $paymentMethods[$key] = $paymentMethod;
            }
        }

        return $paymentMethods;
    }

    /**
     * Get payment methods by Category
     *
     * @param $category
     * @return array
     */
    public function getPaymentMethodsByCategory($category)
    {
        $paymentMethods = PaymentMethod::getPaymentMethodsByCategory($category);

        // Filter Payment Methods
        /** @var PaymentMethod\PaymentMethod $paymentMethod */
        foreach ($paymentMethods as $key => $paymentMethod) {
            if ($paymentMethod->isHidden()) {
                unset($paymentMethods[$key]);
            }

            // Add branding for Generic method
            if ($paymentMethod->getId() === \IngenicoClient\PaymentMethod\Ingenico::CODE) {
                $paymentMethod->setName($this->getWhiteLabelsData()->getPlatform());
                $paymentMethod->setLogo('white-labels/' . $this->getWhiteLabelsData()->getLogo());

                $paymentMethods[$key] = $paymentMethod;
            }
        }

        return $paymentMethods;
    }

    /**
     * Get Selected Payment Methods
     *
     * @return array
     */
    public function getSelectedPaymentMethods()
    {
        $selected = $this->configuration->getSelectedPaymentMethods();
        if (count($selected) === 0) {
            return [];
        }

        // Get All Payment Methods
        $paymentMethods = $this->getPaymentMethods();

        // Filter Payment Methods
        /** @var PaymentMethod\PaymentMethod $paymentMethod */
        foreach ($paymentMethods as $key => $paymentMethod) {
            if (!in_array($paymentMethod->getId(), $selected)) {
                unset($paymentMethods[$key]);
            }

            // Add branding for Generic method
            if ($paymentMethod->getId() === \IngenicoClient\PaymentMethod\Ingenico::CODE) {
                $paymentMethod->setName($this->getWhiteLabelsData()->getPlatform());
                $paymentMethod->setLogo('white-labels/' . $this->getWhiteLabelsData()->getLogo());

                $paymentMethods[$key] = $paymentMethod;
            }
        }

        return $paymentMethods;
    }

    /**
     * Get Unused Payment Methods.
     *
     * @return array
     */
    public function getUnusedPaymentMethods()
    {
        $result = [];
        $methods = self::getPaymentMethods();
        $selected = $this->configuration->getSelectedPaymentMethods();

        /** @var PaymentMethod\PaymentMethod $method */
        foreach ($methods as $method) {
            if (!in_array($method->getId(), $selected)) {
                $result[] = $method;
            }
        }

        return $result;
    }

    /**
     * Get Payment Methods by Country ISO code
     * And merge with current list of Payment methods.
     *
     * @param array $countries
     *
     * @return array
     */
    public function getAndMergeCountriesPaymentMethods(array $countries)
    {
        // Get IDs for selected Payment Methods
        $selectedIDs = $this->configuration->getSelectedPaymentMethods();

        // Get Payment methods by Country
        $paymentMethods = self::getPaymentMethods();
        /** @var PaymentMethod\PaymentMethod $method */
        foreach ($paymentMethods as $method) {
            $pmCountries = array_keys($method->getCountries());
            foreach ($countries as $country) {
                if (in_array($country, $pmCountries)) {
                    $selectedIDs[] = $method->getId();
                }
            }
        }

        return array_unique($selectedIDs);
    }

    /**
     * Process Onboarding data and dispatch email to the corresponding Ingenico sales representative.
     *
     * @param string $companyName
     * @param string $email
     * @param string $countryCode
     * @param string $eCommercePlatform
     * @param string $pluginVersion
     * @param $shopName
     * @param $shopLogo
     * @param $shopUrl
     * @param $ingenicoLogo
     * @param string $locale
     *
     * @throws Exception
     */
    public function submitOnboardingRequest(
        $companyName,
        $email,
        $countryCode,
        $eCommercePlatform,
        $pluginVersion,
        $shopName,
        $shopLogo,
        $shopUrl,
        $ingenicoLogo,
        $locale = 'en_US'
    ) {
        $onboarding = new Onboarding($this->extension, $this);
        if (!$saleEmails = $onboarding->getOnboardingEmailsByCountry($countryCode)) {
            throw new Exception(sprintf('%s country is not found', $countryCode));
        }

        foreach ($saleEmails as $saleEmail) {
            $this->sendMailNotificationOnboardingRequest(
                $saleEmail,
                null,
                null,
                null,
                $this->__('onboarding_request.subject',
                    [
                        '%platform%' => $eCommercePlatform,
                        '%country%' => $countryCode
                    ],
                    'email',
                    $locale
                ),
                [
                    Connector::PARAM_NAME_EPLATFORM => $eCommercePlatform,
                    Connector::PARAM_NAME_COMPANY => $companyName,
                    Connector::PARAM_NAME_EMAIL => $email,
                    Connector::PARAM_NAME_COUNTRY => $countryCode,
                    Connector::PARAM_NAME_REQUEST_TIME => new \DateTime('now'),
                    Connector::PARAM_NAME_VERSION_NUM => $pluginVersion,
                    Connector::PARAM_NAME_SHOP_NAME => $shopName,
                    Connector::PARAM_NAME_SHOP_LOGO => $shopLogo,
                    Connector::PARAM_NAME_SHOP_URL => $shopUrl,
                    Connector::PARAM_NAME_INGENICO_LOGO => $ingenicoLogo
                ],
                $locale
            );
        }
    }

    /**
     * Get Payment Status by Status Code.
     *
     * @param $statusCode
     *
     * @return string
     */
    public static function getStatusByCode($statusCode)
    {
        switch ($statusCode) {
            case 1:
            case 6:
            case 61:
            case 62:
                // 1 - Cancelled by customer
                // 6 - Authorised and cancelled
                // 61 - Author. deletion waiting
                // 62 - Author. deletion uncertain
                return self::STATUS_CANCELLED;
            case 5:
            case 50:
            case 51:
            case 52:
            case 59:
                // 5 - Authorised
                // 50 - Authorized waiting external result
                // 51 - Authorisation waiting
                // 52 - Authorisation not known
                // 59 - Authorization to be requested manually
                return self::STATUS_AUTHORIZED;
            case 8:
            case 84:
            case 85:
            case 7:
                // 7 - Payment deleted
                // 8 - Refund
                // 84 - Refund
                // 85 - Refund handled by merchant
                return self::STATUS_REFUNDED;
            case 81:
                // 81 - Refund pending
                return self::STATUS_REFUND_PROCESSING;
            case 82:
                // 82 - Refund uncertain
                return self::STATUS_ERROR;
            case 83:
                // 83 - Refund Refused
                return self::STATUS_REFUND_REFUSED;
            case 9:
            case 95:
                // 9 - Payment requested
                // 95 - Payment handled by merchant (Direct Debit uses this)
                return self::STATUS_CAPTURED;
            case 91:
                // 91 - Capture processing
                return self::STATUS_CAPTURE_PROCESSING;
            case 92:
                // 92 - Payment uncertain
                return self::STATUS_ERROR;
            case 41:
            case 46:
                // 46 - waiting for identification
                return self::STATUS_PENDING;
            default:
                // 0 - Invalid or incomplete
                return self::STATUS_ERROR;
        }
    }

    /**
     * Get Payment Status.
     *
     * @param string $brand
     * @param int $statusCode
     * @return string
     */
    public function getPaymentStatus($brand, $statusCode)
    {
        $paymentMethod = PaymentMethod::getPaymentMethodByBrand($brand, $this);
        if ($paymentMethod) {
            $status = self::getStatusByCode($statusCode);

            // Twint doesn't support the Two phase flow. So if status is "authorized" then assume "captured"
            if ($brand === 'TWINT' && $status == self::STATUS_AUTHORIZED) {
                return self::STATUS_CAPTURED;
            }

            if (in_array($statusCode, $paymentMethod->getAuthModeSuccessCode())) {
                return self::STATUS_AUTHORIZED;
            }

            if (in_array($statusCode, $paymentMethod->getDirectSalesSuccessCode())) {
                return self::STATUS_CAPTURED;
            }

            return $status;
        }

        return self::getStatusByCode($statusCode);
    }

    /**
     * Finalise Payment and Update order status.
     * Returns payment status as string.
     *
     * @param $orderId
     * @param Payment $paymentResult
     * @return string
     */
    public function finaliseOrderPayment($orderId, Payment &$paymentResult)
    {
        // Log Payment
        $this->extension->logIngenicoPayment($orderId, $paymentResult);

        // Payment result must have status
        if (!$paymentResult->getStatus()) {
            // There's can be problems if wrong credentials of DirectLink user.
            $this->logger->debug(__METHOD__ . ' No status field.', $paymentResult->toArray());
            $message = 'An error occurred. Please try to place the order again.';
            $error = $paymentResult->getNcErrorPlus();
            if (empty($error)) {
                $message .= ' (' . $error . ')';
            }

            throw new Exception($message);
        }

        // Get Payment Status depend on Brand and Status Number
        $paymentStatus = $this->getPaymentStatus($paymentResult->getBrand(), $paymentResult->getStatus());
        $paymentResult->setPaymentStatus($paymentStatus);

        // Process order
        switch ($paymentStatus) {
            case self::STATUS_AUTHORIZED:
                $this->updateOrderStatus($orderId, $paymentResult);
                if ($this->configuration->getDirectSaleEmailOption()) {
                    // Send notifications
                    $this->extension->sendNotificationAuthorization($orderId);
                    $this->extension->sendNotificationAdminAuthorization($orderId);
                }
                break;
            case self::STATUS_CAPTURE_PROCESSING:
                $this->updateOrderStatus($orderId, $paymentResult);
                break;
            case self::STATUS_CAPTURED:
                $this->extension->addCapturedAmount($orderId, $paymentResult->getAmount());
                $this->updateOrderStatus($orderId, $paymentResult);
                break;
            case self::STATUS_REFUND_PROCESSING:
                $this->updateOrderStatus($orderId, $paymentResult);
                break;
            case self::STATUS_REFUND_REFUSED:
                $this->updateOrderStatus($orderId, $paymentResult);
                $this->extension->sendRefundFailedCustomerEmail($orderId);
                $this->extension->sendRefundFailedAdminEmail($orderId);
                break;
            case self::STATUS_REFUNDED:
                $this->extension->addRefundedAmount($orderId, $paymentResult->getAmount());
                $this->updateOrderStatus($orderId, $paymentResult);
                break;
            case self::STATUS_CANCELLED:
                $this->extension->addCancelledAmount($orderId, $paymentResult->getAmount());
                $this->updateOrderStatus($orderId, $paymentResult);
                break;
            case self::STATUS_ERROR:
                $message = $this->__('checkout.error', [
                    '%payment_id%' => $paymentResult->getPayId(),
                    '%status%' => $paymentResult->getStatus(),
                    '%code%' => $paymentResult->getErrorCode(),
                    '%message%' => $paymentResult->getErrorMessage()
                ], 'messages');

                $paymentResult->setMessage($message);
                $this->logger->debug(__METHOD__ . ' Error: ' . $message, $paymentResult->toArray());
                break;
            case self::STATUS_UNKNOWN:
                $this->logger->debug(__METHOD__ . ' Unknown status', $paymentResult->toArray());
                break;
        }

        return $paymentStatus;
    }

    /**
     * Update order status
     *
     * @param $type
     * @param $orderId
     * @param $paymentResult
     *
     * @return null
     */
    public function updateOrderStatus($orderId, $paymentResult)
    {
        $this->extension->updateOrderStatus(
            $orderId,
            $paymentResult,
            $this->__('checkout.payment_info', [
                '%status%' => $paymentResult->getPaymentStatus(),
                '%status_code%' => $paymentResult->getStatus(),
                '%payment_id%' => $paymentResult->getPayId(),
            ], 'messages')
        );

        return null;
    }

    /**
     * Check void availability
     *
     * @param $orderId
     * @param $payId
     * @param $cancelAmount
     *
     * @return bool
     */
    public function canVoid($orderId, $payId, $cancelAmount = null)
    {
        $order = $this->getOrder($orderId);

        if (!$cancelAmount) {
            $cancelAmount = $order->getAmount();
        }

        $cancelAmount = (float) bcdiv($cancelAmount, 1, 2);
        if ($cancelAmount > $order->getAvailableAmountForCancel()) {
            return false;
        }

        $statusCode = $this->getPaymentInfo($orderId, $payId)->getStatus();
        return self::STATUS_AUTHORIZED === $this->getStatusByCode($statusCode);
    }

    /**
     * Check capture availability.
     *
     * @param $orderId
     * @param $payId
     * @param $captureAmount
     *
     * @return bool
     */
    public function canCapture($orderId, $payId, $captureAmount = null)
    {
        $order = $this->getOrder($orderId);

        if (!$captureAmount) {
            $captureAmount = $order->getAmount();
        }

        $captureAmount = (float) bcdiv($captureAmount, 1, 2);
        if ($captureAmount > $order->getAvailableAmountForCapture()) {
            return false;
        }

        $statusCode = $this->getPaymentInfo($orderId, $payId)->getStatus();
        return self::STATUS_AUTHORIZED === $this->getStatusByCode($statusCode);
    }

    /**
     * Check refund availability.
     *
     * @param $orderId
     * @param $payId
     * @param $refundAmount
     *
     * @return bool
     */
    public function canRefund($orderId, $payId, $refundAmount = null)
    {
        $order = $this->getOrder($orderId);

        if (!$refundAmount) {
            $refundAmount = $order->getAmount();
        }

        $refundAmount = (float)bcdiv($refundAmount, 1, 2);
        if ($refundAmount > $order->getAvailableAmountForRefund()) {
            return false;
        }

        // Check if payment can't support refunds
        try {
            $paymentResult = $this->getPaymentInfo($orderId, $payId);
            if ($paymentResult->isTransactionSuccessful() && in_array($paymentResult->getBrand(), ['Intersolve'])) {
                return false;
            }
        } catch (\Exception $e) {
            //
        }

        //$statusCode = $this->getPaymentInfo($orderId, $payId)->getStatus();
        //return self::STATUS_CAPTURED === $this->getStatusByCode($statusCode);
        return true;
    }

    /**
     * Cancel.
     *
     * @param $orderId
     * @param string $payId
     * @param int    $amount
     *
     * @return Payment
     * @throws Exception
     */
    public function cancel($orderId, $payId = null, $amount = null)
    {
        $order = $this->getOrder($orderId);

        $orderAmount = $order->getAmount();
        if (!$amount) {
            $amount = $orderAmount;
        }

        if (!$this->canVoid($orderId, $payId, $amount)) {
            throw new Exception($this->__('exceptions.cancellation_unavailable', [], 'messages'));
        }

        $isPartially = false;
        if ($amount < $order->getAvailableAmountForCancel()) {
            $isPartially = true;
        }

        $directLink = new DirectLink();
        $directLink->setLogger($this->getLogger());

        $response = $directLink->createVoid($this->configuration, $orderId, $payId, $amount, $isPartially);
        if (!$response->isTransactionSuccessful()) {
            throw new Exception(
                $this->__('exceptions.cancellation_failed', [
                    '%code%' => $response->getErrorCode(),
                    '%message%' => $response->getErrorMessage()
                ], 'messages'),
                $response->getErrorCode()
            );
        }

        // Save payment results and update order status
        $this->finaliseOrderPayment($orderId, $response);

        return $response;
    }

    /**
     * Capture.
     *
     * @param $orderId
     * @param string $payId
     * @param int    $amount
     *
     * @return Payment
     * @throws Exception
     */
    public function capture($orderId, $payId = null, $amount = null)
    {
        $order = $this->getOrder($orderId);

        if (!$amount) {
            $amount = $order->getAmount();
        }

        if (!$this->canCapture($orderId, $payId, $amount)) {
            throw new Exception($this->__('exceptions.capture_unavailable', [], 'messages'));
        }

        $isPartially = false;
        if ($amount < $order->getAvailableAmountForCapture()) {
            $isPartially = true;
        }

        $directLink = new DirectLink();
        $directLink->setLogger($this->getLogger());

        $response = $directLink->createCapture($this->configuration, $orderId, $payId, $amount, $isPartially);
        if (!$response->isTransactionSuccessful()) {
            throw new Exception(
                $this->__('exceptions.capture_failed', [
                    '%code%' => $response->getErrorCode(),
                    '%message%' => $response->getErrorMessage()
                ], 'messages'),
                $response->getErrorCode()
            );
        }

        // Save payment results and update order status
        $this->finaliseOrderPayment($orderId, $response);

        return $response;
    }

    /**
     * Refund.
     *
     * @param $orderId
     * @param string $payId
     * @param int    $amount
     *
     * @return Payment
     * @throws Exception
     */
    public function refund($orderId, $payId = null, $amount = null)
    {
        $order = $this->getOrder($orderId);

        if (!$amount) {
            $amount = $order->getAmount();
        }

        if (!$this->canRefund($orderId, $payId, $amount)) {
            throw new Exception($this->__('exceptions.refund_unavailable'));
        }

        $isPartially = false;
        if ($amount < $order->getAvailableAmountForRefund()) {
            $isPartially = true;
        }

        $directLink = new DirectLink();
        $directLink->setLogger($this->getLogger());

        $response = $directLink->createRefund($this->configuration, $orderId, $payId, $amount, $isPartially);
        if (!$response->isTransactionSuccessful()) {
            throw new Exception(
                $this->__('exceptions.refund_failed', [
                    '%code%' => $response->getErrorCode(),
                    '%message%' => $response->getErrorMessage()
                ], 'messages'),
                $response->getErrorCode()
            );
        }

        // Save payment results and update order status
        $this->finaliseOrderPayment($orderId, $response);

        return $response;
    }

    /**
     * Process Alias Save
     * @param $orderId
     * @param array $data
     *
     * @return void
     * @throws Exception
     */
    private function processAlias($orderId, array $data)
    {
        if (empty($data['ALIAS'])) {
            return;
        }

        $order = $this->extension->isOrderCreated($orderId) ?
            $this->getOrder($orderId) : $this->getOrderBeforePlaceOrder($orderId);

        // Build Alias instance and save
        $alias = new Alias($data);
        $alias->setCustomerId($order->getCustomerId());
        $this->saveAlias($alias);
    }

    /**
     * @param MailTemplate $template
     * @param string       $to
     * @param string       $toName
     * @param string       $from
     * @param string       $fromName
     * @param string       $subject
     * @param array $attachedFiles Array like [[
     *                             'name' => 'attached.txt',
     *                             'mime' => 'plain/text',
     *                             'content' => 'Body'
     *                             ]]
     *
     * @return bool
     *
     * @throws Exception
     */
    private function sendMail(
        $template,
        $to,
        $toName,
        $from,
        $fromName,
        $subject,
        array $attachedFiles = []
    ) {
        if (!$template instanceof MailTemplate) {
            throw new Exception('Template variable must be instance of MailTemplate');
        }

        return $this->extension->sendMail(
            $template,
            $to,
            $toName,
            $from,
            $fromName,
            $subject,
            $attachedFiles
        );
    }

    /**
     * Get MailTemplate instance of Reminder.
     *
     * @param $to
     * @param $toName
     * @param $from
     * @param $fromName
     * @param $subject
     * @param array $fields
     * @param string $locale
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
        $fields = array(),
        $locale = null
    ) {
        return $this->sendMail(
            (new MailTemplate(
                $locale ?: $this->extension->getLocale(),
                MailTemplate::LAYOUT_DEFAULT,
                MailTemplate::MAIL_TEMPLATE_REMINDER,
                $fields
            ))->setTemplatesDirectory($this->getMailTemplatesDirectory()),
            $to,
            $toName,
            $from,
            $fromName,
            $subject
        );
    }

    /**
     * Get MailTemplate instance of "Refund Failed".
     *
     * @param $to
     * @param $toName
     * @param $from
     * @param $fromName
     * @param $subject
     * @param array $fields
     * @param string $locale
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
        $fields = array(),
        $locale = null
    ) {
        return $this->sendMail(
            (new MailTemplate(
                $locale ?: $this->extension->getLocale(),
                MailTemplate::LAYOUT_DEFAULT,
                MailTemplate::MAIL_TEMPLATE_REFUND_FAILED,
                $fields
            ))->setTemplatesDirectory($this->getMailTemplatesDirectory()),
            $to,
            $toName,
            $from,
            $fromName,
            $subject
        );
    }

    /**
     * Get MailTemplate instance of "Refund Failed".
     *
     * @param $to
     * @param $toName
     * @param $from
     * @param $fromName
     * @param $subject
     * @param array $fields
     * @param string $locale
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
        $fields = array(),
        $locale = null
    ) {
        $fields['platform_name'] = $this->getWhiteLabelsData()->getPlatform();

        return $this->sendMail(
            (new MailTemplate(
                $locale ?: $this->extension->getLocale(),
                MailTemplate::LAYOUT_INGENICO,
                MailTemplate::MAIL_TEMPLATE_ADMIN_REFUND_FAILED,
                $fields
            ))->setTemplatesDirectory($this->getMailTemplatesDirectory()),
            $to,
            $toName,
            $from,
            $fromName,
            $subject
        );
    }

    /**
     * Get MailTemplate instance of "Order Paid".
     *
     * @param $to
     * @param $toName
     * @param $from
     * @param $fromName
     * @param $subject
     * @param array $fields
     * @param string $locale
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
        $fields = array(),
        $locale = null
    ) {
        return $this->sendMail(
            (new MailTemplate(
                $locale ?: $this->extension->getLocale(),
                MailTemplate::LAYOUT_DEFAULT,
                MailTemplate::MAIL_TEMPLATE_PAID_ORDER,
                $fields
            ))->setTemplatesDirectory($this->getMailTemplatesDirectory()),
            $to,
            $toName,
            $from,
            $fromName,
            $subject
        );
    }

    /**
     * Get MailTemplate instance of "Admin Order Paid".
     *
     * @param $to
     * @param $toName
     * @param $from
     * @param $fromName
     * @param $subject
     * @param array $fields
     * @param string $locale
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
        $fields = array(),
        $locale = null
    ) {
        $fields['platform_name'] = $this->getWhiteLabelsData()->getPlatform();

        return $this->sendMail(
            (new MailTemplate(
                $locale ?: $this->extension->getLocale(),
                MailTemplate::LAYOUT_INGENICO,
                MailTemplate::MAIL_TEMPLATE_ADMIN_PAID_ORDER,
                $fields
            ))->setTemplatesDirectory($this->getMailTemplatesDirectory()),
            $to,
            $toName,
            $from,
            $fromName,
            $subject
        );
    }

    /**
     * Get MailTemplate instance of "Authorization".
     *
     * @param $to
     * @param $toName
     * @param $from
     * @param $fromName
     * @param $subject
     * @param array $fields
     * @param string $locale
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
        $fields = array(),
        $locale = null
    ) {
        return $this->sendMail(
            (new MailTemplate(
                $locale ?: $this->extension->getLocale(),
                MailTemplate::LAYOUT_DEFAULT,
                MailTemplate::MAIL_TEMPLATE_AUTHORIZATION,
                $fields
            ))->setTemplatesDirectory($this->getMailTemplatesDirectory()),
            $to,
            $toName,
            $from,
            $fromName,
            $subject
        );
    }

    /**
     * Get MailTemplate instance of "Admin Authorization".
     *
     * @param $to
     * @param $toName
     * @param $from
     * @param $fromName
     * @param $subject
     * @param array $fields
     * @param string $locale
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
        $fields = array(),
        $locale = null
    ) {
        $fields['platform_name'] = $this->getWhiteLabelsData()->getPlatform();

        return $this->sendMail(
            (new MailTemplate(
                $locale ?: $this->extension->getLocale(),
                MailTemplate::LAYOUT_INGENICO,
                MailTemplate::MAIL_TEMPLATE_ADMIN_AUTHORIZATION,
                $fields
            ))->setTemplatesDirectory($this->getMailTemplatesDirectory()),
            $to,
            $toName,
            $from,
            $fromName,
            $subject
        );
    }

    /**
     * Get MailTemplate instance of "Onboarding request".
     *
     * @param $to
     * @param $toName
     * @param $from
     * @param $fromName
     * @param $subject
     * @param array $fields
     * @param string $locale
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
        $fields = array(),
        $locale = null
    ) {
        $fields['platform_name'] = $this->getWhiteLabelsData()->getPlatform();

        return $this->sendMail(
            (new MailTemplate(
                $locale ?: $this->extension->getLocale(),
                MailTemplate::LAYOUT_INGENICO,
                MailTemplate::MAIL_TEMPLATE_ONBOARDING_REQUEST,
                $fields
            ))->setTemplatesDirectory($this->getMailTemplatesDirectory()),
            $to,
            $toName,
            $from,
            $fromName,
            $subject
        );
    }

    /**
     * Get MailTemplate instance of "Ingenico Support".
     *
     * @param $to
     * @param $toName
     * @param $from
     * @param $fromName
     * @param $subject
     * @param array $fields
     * @param string $locale
     * @param array $attachedFiles Array like [['name' => 'attached.txt', 'mime' => 'plain/text', 'content' => 'Body']]
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function sendMailSupport(
        $to,
        $toName,
        $from,
        $fromName,
        $subject,
        $fields = array(),
        $locale = null,
        array $attachedFiles = []
    ) {
        return $this->sendMail(
            (new MailTemplate(
                $locale ?: $this->extension->getLocale(),
                MailTemplate::LAYOUT_DEFAULT,
                MailTemplate::MAIL_TEMPLATE_SUPPORT,
                $fields
            ))->setTemplatesDirectory($this->getMailTemplatesDirectory()),
            $to,
            $toName,
            $from,
            $fromName,
            $subject,
            $attachedFiles
        );
    }

    /**
     * Returns WhiteLabels Data.
     * It allows to customize data like support name etc.
     *
     * @return WhiteLabels
     */
    public function getWhiteLabelsData()
    {
        return (new WhiteLabels($this->extension, $this));
    }

    /**
     * Get Alias
     * @param mixed $aliasId
     * @return Alias
     */
    public function getAlias($aliasId)
    {
        $data = $this->extension->getAlias($aliasId);
        if (!is_array($data)) {
            $data = [];
        }

        return new Alias($data);
    }

    /**
     * Get Aliases by CustomerId
     * @param $customerId
     * @return array
     */
    public function getCustomerAliases($customerId)
    {
        $aliases = [];
        $data = $this->extension->getCustomerAliases($customerId);
        foreach ($data as $value) {
            $aliases[] = new Alias($value);
        }

        return $aliases;
    }

    /**
     * Save Alias
     * @param Alias $alias
     * @return bool
     */
    public function saveAlias(Alias $alias)
    {
        // Don't save aliases for some brands
        if (in_array(
            $alias->getBrand(),
            [
                'PostFinance Card',
                'Direct Debits NL',
                'Direct Debits DE',
                'Direct Debit AT',
                'Dankor',
                'UATP',
                'AIRPLUS',
                'Split Payment',
                'Open Invoice DE',
                'Open Invoice NL'
            ]
        )) {
            return true;
        }

        return $this->extension->saveAlias($alias->getCustomerId(), [
            'ALIAS' => $alias->getAlias(),
            'BRAND' => $alias->getBrand(),
            'CARDNO' => $alias->getCardno(),
            'CN' => $alias->getCn(),
            'BIN' => $alias->getBin(),
            'PM' => $alias->getPm(),
            'ED' => $alias->getEd()
        ]);
    }

    /**
     * Cron Handler.
     * Send Reminders.
     * Actualise Order's statuses.
     * We're ask payment gateway and get payment status.
     * And update Platform's order status.
     *
     * @return void
     */
    public function cronHandler()
    {
        // Process Reminder notifications
        if ($this->configuration->getSettingsReminderemail()) {
            // Get Settings
            $days = abs($this->configuration->getSettingsReminderemailDays());

            // Send reminders
            foreach ($this->extension->getPendingReminders() as $orderId) {
                $order = $this->getOrder($orderId);
                if (!$order) {
                    continue;
                }

                // Calculate trigger time
                $triggerTime = strtotime($order->getCreatedAt()) + ($days * 24 * 60 * 60);
                if (time() >= $triggerTime) {
                    // Send Reminder
                    try {
                        $this->extension->sendReminderNotificationEmail($orderId);
                    } catch (\Exception $e) {
                        $this->logger->critical('sendReminderNotificationEmail failure',
                            [
                                $orderId,
                                $e->getMessage(),
                                $e->getTraceAsString()
                            ]
                        );
                    }

                    $this->extension->setReminderSent($orderId);
                }
            }

            // Get Orders for reminding
            $orders = $this->extension->getOrdersForReminding();
            foreach ($orders as $orderId) {
                $order = $this->getOrder($orderId);
                if (!$order) {
                    continue;
                }

                if (self::STATUS_PENDING === $order->getStatus()) {
                    // Get Payment Status from Ingenico
                    $paymentResult = $this->getPaymentInfo($orderId, $order->getPayId());

                    // Check if Payment is unpaid
                    if (!$paymentResult->isTransactionSuccessful() &&
                        (in_array($paymentResult->getErrorCode(), ['50001130', '50001131']) ||
                            $paymentResult->getNcStatus() === 'none'
                        )
                    ) {
                        // Payment Status is failed. Error: 50001130 unknown orderid 691 for merchant
                        // Payment Status is failed. unknown payid/orderID 3046675410/300062 for merchant
                        // Enqueue Reminder
                        $this->extension->enqueueReminder($orderId);
                    }
                }

                // Get cancelled orders in latest 2 days
                if (self::STATUS_CANCELLED === $order->getStatus() &&
                    ((strtotime($order->getCreatedAt()) >= time()) &&
                     (strtotime($order->getCreatedAt()) <= strtotime(sprintf('-%s days', $days))))
                ) {
                    if (!$this->extension->isCartPaid($orderId)) {
                        $this->extension->enqueueReminder($orderId);
                    }
                }
            }
        }
    }
}
