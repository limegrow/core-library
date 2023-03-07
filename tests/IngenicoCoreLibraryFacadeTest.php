<?php

use PHPUnit\Framework\TestCase;

class IngenicoCoreLibraryFacadeTest extends TestCase
{
    /**
     * @var \IngenicoClient\IngenicoCoreLibrary
     */
    private readonly \IngenicoClient\IngenicoCoreLibrary $coreLibraryFacade;

    public function testOrderStatusRequest()
    {
        $configuration = $this->getConfiguration();
        $payId = '1';
        $payment = new \IngenicoClient\Payment();
        $response = $payment->createStatusRequest($configuration, null, $payId);

        $this->assertEquals($payId, $response->getParam('PAYID'));
    }

    public function testDirectLinkPaymentCreation()
    {
        $configuration = $this->getConfiguration();
        $order = $this->getTestOrder();
        $alias = $this->getTestAlias();
        $urls = $this->requestReturnUrls();
        $directLink = new \IngenicoClient\DirectLink();
        $directLinkResponse = $directLink->createDirectLinkRequest($configuration, $order, $alias, null, $urls);

        $this->assertTrue($directLinkResponse->isSuccessful());
    }

    public function testOperationsCheck()
    {
        $payId = 1;
        $response = $this->getCoreLibrary()->getPaymentInfo(null, $payId);
        $this->assertEquals($payId, $response->getParam('PAYID'));

        $result = $this->getCoreLibrary()->canVoid($response);
        $this->assertInternalType('bool', $result);

        $result = $this->getCoreLibrary()->canCapture($response);
        $this->assertInternalType('bool', $result);

        $result = $this->getCoreLibrary()->canRefund($response);
        $this->assertInternalType('bool', $result);
    }

    /**
     * @throws \IngenicoClient\Exception
     */
    public function testOperation()
    {
        $orderId = 1;
        $payId = 1;
        $directLinkResponse = $this->getCoreLibrary()->refund($orderId, $payId, 100);
        $this->assertInstanceOf(\Ogone\DirectLink\DirectLinkMaintenanceResponse::class, $directLinkResponse);

        $directLinkResponse = $this->getCoreLibrary()->capture($orderId, $payId, 100);
        $this->assertInstanceOf(\Ogone\DirectLink\DirectLinkMaintenanceResponse::class, $directLinkResponse);

        $directLinkResponse = $this->getCoreLibrary()->cancel($orderId, $payId);
        $this->assertInstanceOf(\Ogone\DirectLink\DirectLinkMaintenanceResponse::class, $directLinkResponse);
    }

    /**
     * @covers \IngenicoClient\IngenicoCoreLibrary::getPaymentMethods
     */
    public function testGetPaymentMethods()
    {
        $result = \IngenicoClient\IngenicoCoreLibrary::getPaymentMethods();
        static::assertCount(19, $result);
    }

    /**
     * @covers \IngenicoClient\IngenicoCoreLibrary::getAllCountries
     */
    public function testGetAllCountries()
    {
        $result = \IngenicoClient\IngenicoCoreLibrary::getAllCountries();
        static::assertCount(10, $result);
    }

    /**
     * @covers \IngenicoClient\IngenicoCoreLibrary::getCountriesPaymentMethods
     */
    public function testGetCountriesPaymentMethods()
    {
        $result = \IngenicoClient\IngenicoCoreLibrary::getCountriesPaymentMethods();
        static::assertCount(10, $result);
    }


    public function getConfiguration(): \IngenicoClient\Configuration
    {
        return new \IngenicoClient\Configuration(
            PSPID,
            USER,
            PASSWORD,
            PASSPHRASE,
            'sha512'
        );
    }

    public function getTestOrder(): \IngenicoClient\Order
    {
        $order = new \IngenicoClient\Order();
        $order->setOrderid(123);
        $order->setAmount(1000);
        $order->setCurrency('EUR');

        return $order;
    }

    public function getTestAlias(): \Ogone\DirectLink\Alias
    {
        return new \Ogone\DirectLink\Alias('test');
    }

    /**
     * Get CoreLibrary Instance
     */
    public function getCoreLibrary(): \IngenicoClient\IngenicoCoreLibrary
    {

        return $this->coreLibraryFacade;
    }

    /**
     * Returns settings array
     */
    public function requestSettings(): array
    {
        return [
            'mode' => 'test',
            'pspid_test' => PSPID,
            'user_test' => USER,
            'password_test' => PASSWORD,
            'signature_test' => PASSPHRASE,
            'pspid_live' => PSPID,
            'user_live' => USER,
            'password_live' => PASSWORD,
            'signature_live' => PASSPHRASE
        ];
    }

    /**
     * Returns array with cancel, accept,
     * exception and back url
     */
    public function requestReturnUrls(): array
    {
        return [
            'accept' => 'http://example.com',
            'exception' => 'http://example.com',
            'cancel' => 'http://example.com',
            'back' => 'http://example.com',
        ];
    }

    /**
     * Update order status
     *
     * @param $order_id
     * @param $statusCode
     */
    public function updateOrderStatus($order_id, $statusCode)
    {
    }

    /**
     * Returns Ogone Amount in cents
     *
     * @param $orderId
     */
    public function requestOrderAmount($orderId): int
    {
        return 1000;
    }

    /**
     * Returns currency ISO code
     *
     * @param $orderId
     */
    public function requestOrderCurrency($orderId): string
    {
        return 'EUR';
    }
}
