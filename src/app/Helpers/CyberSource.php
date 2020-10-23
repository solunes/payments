<?php 

namespace Solunes\Payments\App\Helpers;

use Validator;

class CyberSource {

    /* Process a Payment */
    public static function SimpleAuthorizationInternet($code, $card_number, $card_exp_month, $cart_exp_year, $amount) {
        if (isset($flag) && $flag == "true") {
            $capture = true;
        } else {
            $capture = false;
        }
        
        $clientReferenceInformationArr = [
                "code" => $code
        ];
        $clientReferenceInformation = new CyberSource\Model\Ptsv2paymentsClientReferenceInformation($clientReferenceInformationArr);

        $processingInformationArr = [
                "capture" => $capture
        ];
        $processingInformation = new CyberSource\Model\Ptsv2paymentsProcessingInformation($processingInformationArr);

        $paymentInformationCardArr = [
                "number" => $card_number,
                "expirationMonth" => $card_exp_month,
                "expirationYear" => $cart_exp_year
        ];
        $paymentInformationCard = new CyberSource\Model\Ptsv2paymentsPaymentInformationCard($paymentInformationCardArr);

        $paymentInformationArr = [
                "card" => $paymentInformationCard
        ];
        $paymentInformation = new CyberSource\Model\Ptsv2paymentsPaymentInformation($paymentInformationArr);

        $orderInformationAmountDetailsArr = [
                "totalAmount" => $amount,
                "currency" => "USD"
        ];
        $orderInformationAmountDetails = new CyberSource\Model\Ptsv2paymentsOrderInformationAmountDetails($orderInformationAmountDetailsArr);

        $orderInformationBillToArr = [
                "firstName" => "John",
                "lastName" => "Doe",
                "address1" => "1 Market St",
                "locality" => "san francisco",
                "administrativeArea" => "CA",
                "postalCode" => "94105",
                "country" => "US",
                "email" => "test@cybs.com",
                "phoneNumber" => "4158880000"
        ];
        $orderInformationBillTo = new CyberSource\Model\Ptsv2paymentsOrderInformationBillTo($orderInformationBillToArr);

        $orderInformationArr = [
                "amountDetails" => $orderInformationAmountDetails,
                "billTo" => $orderInformationBillTo
        ];
        $orderInformation = new CyberSource\Model\Ptsv2paymentsOrderInformation($orderInformationArr);

        $requestObjArr = [
                "clientReferenceInformation" => $clientReferenceInformation,
                "processingInformation" => $processingInformation,
                "paymentInformation" => $paymentInformation,
                "orderInformation" => $orderInformation
        ];
        $requestObj = new CyberSource\Model\CreatePaymentRequest($requestObjArr);


        $commonElement = new CyberSource\ExternalConfiguration();
        $config = $commonElement->ConnectionHost();
        $merchantConfig = $commonElement->merchantConfigObject();

        $api_client = new CyberSource\ApiClient($config, $merchantConfig);
        $api_instance = new CyberSource\Api\PaymentsApi($api_client);

        try {
            $apiResponse = $api_instance->createPayment($requestObj);
            print_r(PHP_EOL);
            print_r($apiResponse);

            return $apiResponse;
        } catch (Cybersource\ApiException $e) {
            print_r($e->getResponseBody());
            print_r($e->getMessage());
        }
    }

    /* Process a Payment Update for new charges */
    public static function IncrementalAuthorization($id, $code_id, $amount) {
        //$id = AuthorizationForIncrementalAuthorizationFlow()[0]['id'];

        $clientReferenceInformationArr = [
                "code" => $code_id
        ];
        $clientReferenceInformation = new CyberSource\Model\Ptsv2paymentsidClientReferenceInformation($clientReferenceInformationArr);

        $processingInformationAuthorizationOptionsInitiatorArr = [
                "storedCredentialUsed" => true
        ];
        $processingInformationAuthorizationOptionsInitiator = new CyberSource\Model\Ptsv2paymentsidProcessingInformationAuthorizationOptionsInitiator($processingInformationAuthorizationOptionsInitiatorArr);

        $processingInformationAuthorizationOptionsArr = [
                "initiator" => $processingInformationAuthorizationOptionsInitiator
        ];
        $processingInformationAuthorizationOptions = new CyberSource\Model\Ptsv2paymentsidProcessingInformationAuthorizationOptions($processingInformationAuthorizationOptionsArr);

        $processingInformationArr = [
                "authorizationOptions" => $processingInformationAuthorizationOptions
        ];
        $processingInformation = new CyberSource\Model\Ptsv2paymentsidProcessingInformation($processingInformationArr);

        $orderInformationAmountDetailsArr = [
                "additionalAmount" => $amount,
                "currency" => "USD"
        ];
        $orderInformationAmountDetails = new CyberSource\Model\Ptsv2paymentsidOrderInformationAmountDetails($orderInformationAmountDetailsArr);

        $orderInformationArr = [
                "amountDetails" => $orderInformationAmountDetails
        ];
        $orderInformation = new CyberSource\Model\Ptsv2paymentsidOrderInformation($orderInformationArr);

        $merchantInformationArr = [
                "transactionLocalDateTime" => "20191002080000"
        ];
        $merchantInformation = new CyberSource\Model\Ptsv2paymentsidMerchantInformation($merchantInformationArr);

        $travelInformationArr = [
                "duration" => "4"
        ];
        $travelInformation = new CyberSource\Model\Ptsv2paymentsidTravelInformation($travelInformationArr);

        $requestObjArr = [
                "clientReferenceInformation" => $clientReferenceInformation,
                "processingInformation" => $processingInformation,
                "orderInformation" => $orderInformation,
                "merchantInformation" => $merchantInformation,
                "travelInformation" => $travelInformation
        ];
        $requestObj = new CyberSource\Model\IncrementAuthRequest($requestObjArr);


        $commonElement = new CyberSource\ExternalConfiguration();
        $config = $commonElement->ConnectionHost();
        $merchantConfig = $commonElement->merchantConfigObject();

        $api_client = new CyberSource\ApiClient($config, $merchantConfig);
        $api_instance = new CyberSource\Api\PaymentsApi($api_client);

        try {
            $apiResponse = $api_instance->incrementAuth($id, $requestObj);
            print_r(PHP_EOL);
            print_r($apiResponse);

            return $apiResponse;
        } catch (Cybersource\ApiException $e) {
            print_r($e->getResponseBody());
            print_r($e->getMessage());
        }
    }

    /* Create a new customer */
    public static function CreateCustomer($internal_customer_id, $customer_email, $reference_code, $customer_name) {
        $buyerInformationArr = [
                "merchantCustomerID" => $internal_customer_id,
                "email" => $customer_email
        ];
        $buyerInformation = new CyberSource\Model\Tmsv2customersBuyerInformation($buyerInformationArr);

        $clientReferenceInformationArr = [
                "code" => $reference_code
        ];
        $clientReferenceInformation = new CyberSource\Model\Tmsv2customersClientReferenceInformation($clientReferenceInformationArr);

        $merchantDefinedInformation = array();
        $merchantDefinedInformation_0 = [
                "name" => "name",
                "value" => $customer_name
        ];
        $merchantDefinedInformation[0] = new CyberSource\Model\Tmsv2customersMerchantDefinedInformation($merchantDefinedInformation_0);

        $requestObjArr = [
                "buyerInformation" => $buyerInformation,
                "clientReferenceInformation" => $clientReferenceInformation,
                "merchantDefinedInformation" => $merchantDefinedInformation
        ];
        $requestObj = new CyberSource\Model\PostCustomerRequest($requestObjArr);


        $commonElement = new CyberSource\ExternalConfiguration();
        $config = $commonElement->ConnectionHost();
        $merchantConfig = $commonElement->merchantConfigObject();

        $api_client = new CyberSource\ApiClient($config, $merchantConfig);
        $api_instance = new CyberSource\Api\CustomerApi($api_client);

        try {
            $apiResponse = $api_instance->postCustomer($requestObj, null);
            print_r(PHP_EOL);
            print_r($apiResponse);

            return $apiResponse;
        } catch (Cybersource\ApiException $e) {
            print_r($e->getResponseBody());
            print_r($e->getMessage());
        }
    }

    /* Retrieve a customer */
    public static function RetrieveCustomer($customerTokenId) {
        $commonElement = new CyberSource\ExternalConfiguration();
        $config = $commonElement->ConnectionHost();
        $merchantConfig = $commonElement->merchantConfigObject();

        $api_client = new CyberSource\ApiClient($config, $merchantConfig);
        $api_instance = new CyberSource\Api\CustomerApi($api_client);

        try {
            $apiResponse = $api_instance->getCustomer($customerTokenId, null);
            print_r(PHP_EOL);
            print_r($apiResponse);

            return $apiResponse;
        } catch (Cybersource\ApiException $e) {
            print_r($e->getResponseBody());
            print_r($e->getMessage());
        }
    }

    /* Update a customer */
    public static function UpdateCustomer($customerTokenId, $internal_customer_id, $customer_email, $reference_code, $customer_name) {
        $buyerInformationArr = [
                "merchantCustomerID" => $internal_customer_id,
                "email" => $customer_email
        ];
        $buyerInformation = new CyberSource\Model\Tmsv2customersBuyerInformation($buyerInformationArr);

        $clientReferenceInformationArr = [
                "code" => $reference_code
        ];
        $clientReferenceInformation = new CyberSource\Model\Tmsv2customersClientReferenceInformation($clientReferenceInformationArr);

        $merchantDefinedInformation = array();
        $merchantDefinedInformation_0 = [
                "name" => "name",
                "value" => $customer_name
        ];
        $merchantDefinedInformation[0] = new CyberSource\Model\Tmsv2customersMerchantDefinedInformation($merchantDefinedInformation_0);

        $requestObjArr = [
                "buyerInformation" => $buyerInformation,
                "clientReferenceInformation" => $clientReferenceInformation,
                "merchantDefinedInformation" => $merchantDefinedInformation
        ];
        $requestObj = new CyberSource\Model\PatchCustomerRequest($requestObjArr);


        $commonElement = new CyberSource\ExternalConfiguration();
        $config = $commonElement->ConnectionHost();
        $merchantConfig = $commonElement->merchantConfigObject();

        $api_client = new CyberSource\ApiClient($config, $merchantConfig);
        $api_instance = new CyberSource\Api\CustomerApi($api_client);

        try {
            $apiResponse = $api_instance->patchCustomer($customerTokenId, $requestObj, null, null);
            print_r(PHP_EOL);
            print_r($apiResponse);

            return $apiResponse;
        } catch (Cybersource\ApiException $e) {
            print_r($e->getResponseBody());
            print_r($e->getMessage());
        }
    }

    /* Delete a customer */
    public static function DeleteCustomer($customerTokenId) {
        $commonElement = new CyberSource\ExternalConfiguration();
        $config = $commonElement->ConnectionHost();
        $merchantConfig = $commonElement->merchantConfigObject();

        $api_client = new CyberSource\ApiClient($config, $merchantConfig);
        $api_instance = new CyberSource\Api\CustomerApi($api_client);

        try {
            $apiResponse = $api_instance->deleteCustomer($customerTokenId, null);
            print_r(PHP_EOL);
            print_r($apiResponse);

            return $apiResponse;
        } catch (Cybersource\ApiException $e) {
            print_r($e->getResponseBody());
            print_r($e->getMessage());
        }
    }

    /* Flex Microfilm */
    public static function GenerateKey($origin) {
                //"targetOrigin" => "https://www.test.com"
        $requestObjArr = [
                "encryptionType" => "RsaOaep",
                "targetOrigin" => $origin
        ];
        $requestObj = new CyberSource\Model\GeneratePublicKeyRequest($requestObjArr);

        $format = "JWT";

        $commonElement = new CyberSource\ExternalConfiguration();
        $config = $commonElement->ConnectionHost();
        $merchantConfig = $commonElement->merchantConfigObject();

        $api_client = new CyberSource\ApiClient($config, $merchantConfig);
        $api_instance = new CyberSource\Api\KeyGenerationApi($api_client);

        try {
            $apiResponse = $api_instance->generatePublicKey($format, $requestObj);
            print_r(PHP_EOL);
            print_r($apiResponse);

            return $apiResponse;
        } catch (Cybersource\ApiException $e) {
            print_r($e->getResponseBody());
            print_r($e->getMessage());
        }
    }

    /* Create Customer Payment Instrument */
    public static function CreateCustomerPaymentInstrumentCard($customerTokenId, $) {
        $cardArr = [
                "expirationMonth" => "12",
                "expirationYear" => "2031",
                "type" => "001"
        ];
        $card = new CyberSource\Model\Tmsv2customersEmbeddedDefaultPaymentInstrumentCard($cardArr);

        $billToArr = [
                "firstName" => "John",
                "lastName" => "Doe",
                "company" => "CyberSource",
                "address1" => "1 Market St",
                "locality" => "San Francisco",
                "administrativeArea" => "CA",
                "postalCode" => "94105",
                "country" => "US",
                "email" => "test@cybs.com",
                "phoneNumber" => "4158880000"
        ];
        $billTo = new CyberSource\Model\Tmsv2customersEmbeddedDefaultPaymentInstrumentBillTo($billToArr);

        $instrumentIdentifierArr = [
                "id" => "7010000000016241111"
        ];
        $instrumentIdentifier = new CyberSource\Model\Tmsv2customersEmbeddedDefaultPaymentInstrumentInstrumentIdentifier($instrumentIdentifierArr);

        $requestObjArr = [
                "card" => $card,
                "billTo" => $billTo,
                "instrumentIdentifier" => $instrumentIdentifier
        ];
        $requestObj = new CyberSource\Model\PostCustomerPaymentInstrumentRequest($requestObjArr);


        $commonElement = new CyberSource\ExternalConfiguration();
        $config = $commonElement->ConnectionHost();
        $merchantConfig = $commonElement->merchantConfigObject();

        $api_client = new CyberSource\ApiClient($config, $merchantConfig);
        $api_instance = new CyberSource\Api\CustomerPaymentInstrumentApi($api_client);

        try {
            $apiResponse = $api_instance->postCustomerPaymentInstrument($customerTokenId, $requestObj, null);
            print_r(PHP_EOL);
            print_r($apiResponse);

            return $apiResponse;
        } catch (Cybersource\ApiException $e) {
            print_r($e->getResponseBody());
            print_r($e->getMessage());
        }
    }

    /* List Customer Payment Instruments */
    public static function ListPaymentInstrumentsForCustomer($customerTokenId) {
        $profileid = null;
        $offset = null;
        $limit = null;

        $commonElement = new CyberSource\ExternalConfiguration();
        $config = $commonElement->ConnectionHost();
        $merchantConfig = $commonElement->merchantConfigObject();

        $api_client = new CyberSource\ApiClient($config, $merchantConfig);
        $api_instance = new CyberSource\Api\CustomerPaymentInstrumentApi($api_client);

        try {
            $apiResponse = $api_instance->getCustomerPaymentInstrumentsList($customerTokenId, $profileid, $offset, $limit);
            print_r(PHP_EOL);
            print_r($apiResponse);

            return $apiResponse;
        } catch (Cybersource\ApiException $e) {
            print_r($e->getResponseBody());
            print_r($e->getMessage());
        }
    }

    /* Get a Customer Payment Instruments */
    public static function RetrieveCustomerPaymentInstrument($customerTokenId, $paymentInstrumentTokenId) {
        $commonElement = new CyberSource\ExternalConfiguration();
        $config = $commonElement->ConnectionHost();
        $merchantConfig = $commonElement->merchantConfigObject();

        $api_client = new CyberSource\ApiClient($config, $merchantConfig);
        $api_instance = new CyberSource\Api\CustomerPaymentInstrumentApi($api_client);

        try {
            $apiResponse = $api_instance->getCustomerPaymentInstrument($customerTokenId, $paymentInstrumentTokenId, null);
            print_r(PHP_EOL);
            print_r($apiResponse);

            return $apiResponse;
        } catch (Cybersource\ApiException $e) {
            print_r($e->getResponseBody());
            print_r($e->getMessage());
        }
    }

    /* Delete a Customer Payment Instruments */
    public static function DeleteCustomerPaymentInstrument($customerTokenId, $paymentInstrumentTokenId) {
        $commonElement = new CyberSource\ExternalConfiguration();
        $config = $commonElement->ConnectionHost();
        $merchantConfig = $commonElement->merchantConfigObject();

        $api_client = new CyberSource\ApiClient($config, $merchantConfig);
        $api_instance = new CyberSource\Api\CustomerPaymentInstrumentApi($api_client);

        try {
            $apiResponse = $api_instance->deleteCustomerPaymentInstrument($customerTokenId, $paymentInstrumentTokenId, null);
            print_r(PHP_EOL);
            print_r($apiResponse);

            return $apiResponse;
        } catch (Cybersource\ApiException $e) {
            print_r($e->getResponseBody());
            print_r($e->getMessage());
        }
    }

    /* Risk Management: BasicDMTransaction */
    public static function BasicDMTransaction($customerTokenId) {
        $clientReferenceInformationArr = [
                "code" => "54323007"
        ];
        $clientReferenceInformation = new CyberSource\Model\Riskv1decisionsClientReferenceInformation($clientReferenceInformationArr);

        $paymentInformationCardArr = [
                "number" => "4444444444444448",
                "expirationMonth" => "12",
                "expirationYear" => "2020"
        ];
        $paymentInformationCard = new CyberSource\Model\Riskv1decisionsPaymentInformationCard($paymentInformationCardArr);

        $paymentInformationArr = [
                "card" => $paymentInformationCard
        ];
        $paymentInformation = new CyberSource\Model\Riskv1decisionsPaymentInformation($paymentInformationArr);

        $orderInformationAmountDetailsArr = [
                "currency" => "USD",
                "totalAmount" => "144.14"
        ];
        $orderInformationAmountDetails = new CyberSource\Model\Riskv1decisionsOrderInformationAmountDetails($orderInformationAmountDetailsArr);

        $orderInformationBillToArr = [
                "address1" => "96, powers street",
                "administrativeArea" => "NH",
                "country" => "US",
                "locality" => "Clearwater milford",
                "firstName" => "James",
                "lastName" => "Smith",
                "phoneNumber" => "7606160717",
                "email" => "test@visa.com",
                "postalCode" => "03055"
        ];
        $orderInformationBillTo = new CyberSource\Model\Riskv1decisionsOrderInformationBillTo($orderInformationBillToArr);

        $orderInformationArr = [
                "amountDetails" => $orderInformationAmountDetails,
                "billTo" => $orderInformationBillTo
        ];
        $orderInformation = new CyberSource\Model\Riskv1decisionsOrderInformation($orderInformationArr);

        $requestObjArr = [
                "clientReferenceInformation" => $clientReferenceInformation,
                "paymentInformation" => $paymentInformation,
                "orderInformation" => $orderInformation
        ];
        $requestObj = new CyberSource\Model\CreateBundledDecisionManagerCaseRequest($requestObjArr);


        $commonElement = new CyberSource\ExternalConfiguration();
        $config = $commonElement->ConnectionHost();
        $merchantConfig = $commonElement->merchantConfigObject();

        $api_client = new CyberSource\ApiClient($config, $merchantConfig);
        $api_instance = new CyberSource\Api\DecisionManagerApi($api_client);

        try {
            $apiResponse = $api_instance->createBundledDecisionManagerCase($requestObj);
            print_r(PHP_EOL);
            print_r($apiResponse);

            return $apiResponse;
        } catch (Cybersource\ApiException $e) {
            print_r($e->getResponseBody());
            print_r($e->getMessage());
        }
    }


    /* Risk Management: BasicDMTransaction */
    public static function BasicDMTransaction($customerTokenId) {
        $clientReferenceInformationArr = [
                "code" => "54323007"
        ];
        $clientReferenceInformation = new CyberSource\Model\Riskv1decisionsClientReferenceInformation($clientReferenceInformationArr);

        $paymentInformationCardArr = [
                "number" => "4444444444444448",
                "expirationMonth" => "12",
                "expirationYear" => "2020"
        ];
        $paymentInformationCard = new CyberSource\Model\Riskv1decisionsPaymentInformationCard($paymentInformationCardArr);

        $paymentInformationArr = [
                "card" => $paymentInformationCard
        ];
        $paymentInformation = new CyberSource\Model\Riskv1decisionsPaymentInformation($paymentInformationArr);

        $orderInformationAmountDetailsArr = [
                "currency" => "USD",
                "totalAmount" => "144.14"
        ];
        $orderInformationAmountDetails = new CyberSource\Model\Riskv1decisionsOrderInformationAmountDetails($orderInformationAmountDetailsArr);

        $orderInformationBillToArr = [
                "address1" => "96, powers street",
                "administrativeArea" => "NH",
                "country" => "US",
                "locality" => "Clearwater milford",
                "firstName" => "James",
                "lastName" => "Smith",
                "phoneNumber" => "7606160717",
                "email" => "test@visa.com",
                "postalCode" => "03055"
        ];
        $orderInformationBillTo = new CyberSource\Model\Riskv1decisionsOrderInformationBillTo($orderInformationBillToArr);

        $orderInformationArr = [
                "amountDetails" => $orderInformationAmountDetails,
                "billTo" => $orderInformationBillTo
        ];
        $orderInformation = new CyberSource\Model\Riskv1decisionsOrderInformation($orderInformationArr);

        $requestObjArr = [
                "clientReferenceInformation" => $clientReferenceInformation,
                "paymentInformation" => $paymentInformation,
                "orderInformation" => $orderInformation
        ];
        $requestObj = new CyberSource\Model\CreateBundledDecisionManagerCaseRequest($requestObjArr);


        $commonElement = new CyberSource\ExternalConfiguration();
        $config = $commonElement->ConnectionHost();
        $merchantConfig = $commonElement->merchantConfigObject();

        $api_client = new CyberSource\ApiClient($config, $merchantConfig);
        $api_instance = new CyberSource\Api\DecisionManagerApi($api_client);

        try {
            $apiResponse = $api_instance->createBundledDecisionManagerCase($requestObj);
            print_r(PHP_EOL);
            print_r($apiResponse);

            return $apiResponse;
        } catch (Cybersource\ApiException $e) {
            print_r($e->getResponseBody());
            print_r($e->getMessage());
        }
    }

    /* Decision Manager Add To List */
    public static function AddDataToList($customerTokenId) {
        $type = 'negative';
        $orderInformationAddressArr = [
                "address1" => "1234 Sample St.",
                "address2" => "Mountain View",
                "locality" => "California",
                "country" => "US",
                "administrativeArea" => "CA",
                "postalCode" => "94043"
        ];
        $orderInformationAddress = new CyberSource\Model\Riskv1liststypeentriesOrderInformationAddress($orderInformationAddressArr);

        $orderInformationBillToArr = [
                "firstName" => "John",
                "lastName" => "Doe",
                "email" => "test@example.com"
        ];
        $orderInformationBillTo = new CyberSource\Model\Riskv1liststypeentriesOrderInformationBillTo($orderInformationBillToArr);

        $orderInformationArr = [
                "address" => $orderInformationAddress,
                "billTo" => $orderInformationBillTo
        ];
        $orderInformation = new CyberSource\Model\Riskv1liststypeentriesOrderInformation($orderInformationArr);

        $paymentInformationArr = [
        ];
        $paymentInformation = new CyberSource\Model\Riskv1liststypeentriesPaymentInformation($paymentInformationArr);

        $clientReferenceInformationArr = [
                "code" => "54323007"
        ];
        $clientReferenceInformation = new CyberSource\Model\Riskv1decisionsClientReferenceInformation($clientReferenceInformationArr);

        $riskInformationMarkingDetailsArr = [
                "action" => "add"
        ];
        $riskInformationMarkingDetails = new CyberSource\Model\Riskv1liststypeentriesRiskInformationMarkingDetails($riskInformationMarkingDetailsArr);

        $riskInformationArr = [
                "markingDetails" => $riskInformationMarkingDetails
        ];
        $riskInformation = new CyberSource\Model\Riskv1liststypeentriesRiskInformation($riskInformationArr);

        $requestObjArr = [
                "orderInformation" => $orderInformation,
                "paymentInformation" => $paymentInformation,
                "clientReferenceInformation" => $clientReferenceInformation,
                "riskInformation" => $riskInformation
        ];
        $requestObj = new CyberSource\Model\AddNegativeListRequest($requestObjArr);


        $commonElement = new CyberSource\ExternalConfiguration();
        $config = $commonElement->ConnectionHost();
        $merchantConfig = $commonElement->merchantConfigObject();

        $api_client = new CyberSource\ApiClient($config, $merchantConfig);
        $api_instance = new CyberSource\Api\DecisionManagerApi($api_client);

        try {
            $apiResponse = $api_instance->addNegative($type, $requestObj);
            print_r(PHP_EOL);
            print_r($apiResponse);

            return $apiResponse;
        } catch (Cybersource\ApiException $e) {
            print_r($e->getResponseBody());
            print_r($e->getMessage());
        }
    }

    /* Decision Manager: MarkAsSuspect */
    public static function MarkAsSuspect($id) {
        $id = "5825489395116729903003";

        $riskInformationMarkingDetailsFieldsIncluded = array();
        $riskInformationMarkingDetailsFieldsIncluded[0] = "customer_email";
        $riskInformationMarkingDetailsFieldsIncluded[1] = "customer_phone";
        $riskInformationMarkingDetailsArr = [
                "notes" => "Adding this transaction as suspect",
                "reason" => "suspected",
                "fieldsIncluded" => $riskInformationMarkingDetailsFieldsIncluded,
                "action" => "add"
        ];
        $riskInformationMarkingDetails = new CyberSource\Model\Riskv1decisionsidmarkingRiskInformationMarkingDetails($riskInformationMarkingDetailsArr);

        $riskInformationArr = [
                "markingDetails" => $riskInformationMarkingDetails
        ];
        $riskInformation = new CyberSource\Model\Riskv1decisionsidmarkingRiskInformation($riskInformationArr);

        $requestObjArr = [
                "riskInformation" => $riskInformation
        ];
        $requestObj = new CyberSource\Model\FraudMarkingActionRequest($requestObjArr);


        $commonElement = new CyberSource\ExternalConfiguration();
        $config = $commonElement->ConnectionHost();
        $merchantConfig = $commonElement->merchantConfigObject();

        $api_client = new CyberSource\ApiClient($config, $merchantConfig);
        $api_instance = new CyberSource\Api\DecisionManagerApi($api_client);

        try {
            $apiResponse = $api_instance->fraudUpdate($id, $requestObj);
            print_r(PHP_EOL);
            print_r($apiResponse);

            return $apiResponse;
        } catch (Cybersource\ApiException $e) {
            print_r($e->getResponseBody());
            print_r($e->getMessage());
        }
    }

    /* Verification: Verify Customer Address */
    public static function VerboseRequestWithAllFields() {
        $clientReferenceInformationArr = [
                "code" => "addressEg",
                "comments" => "dav-All fields"
        ];
        $clientReferenceInformation = new CyberSource\Model\Riskv1addressverificationsClientReferenceInformation($clientReferenceInformationArr);

        $orderInformationBillToArr = [
                "address1" => "12301 research st",
                "address2" => "1",
                "address3" => "2",
                "address4" => "3",
                "administrativeArea" => "TX",
                "country" => "US",
                "locality" => "Austin",
                "postalCode" => "78759"
        ];
        $orderInformationBillTo = new CyberSource\Model\Riskv1addressverificationsOrderInformationBillTo($orderInformationBillToArr);

        $orderInformationShipToArr = [
                "address1" => "1715 oaks apt # 7",
                "address2" => " ",
                "address3" => "",
                "address4" => "",
                "administrativeArea" => "WI",
                "country" => "US",
                "locality" => "SUPERIOR",
                "postalCode" => "29681"
        ];
        $orderInformationShipTo = new CyberSource\Model\Riskv1addressverificationsOrderInformationShipTo($orderInformationShipToArr);

        $orderInformationLineItems = array();
        $orderInformationLineItems_0 = [
                "unitPrice" => "120.50",
                "quantity" => 3,
                "productSKU" => "9966223",
                "productName" => "headset",
                "productCode" => "electronic"
        ];
        $orderInformationLineItems[0] = new CyberSource\Model\Riskv1addressverificationsOrderInformationLineItems($orderInformationLineItems_0);

        $orderInformationArr = [
                "billTo" => $orderInformationBillTo,
                "shipTo" => $orderInformationShipTo,
                "lineItems" => $orderInformationLineItems
        ];
        $orderInformation = new CyberSource\Model\Riskv1addressverificationsOrderInformation($orderInformationArr);

        $buyerInformationArr = [
                "merchantCustomerId" => "ABCD"
        ];
        $buyerInformation = new CyberSource\Model\Riskv1addressverificationsBuyerInformation($buyerInformationArr);

        $requestObjArr = [
                "clientReferenceInformation" => $clientReferenceInformation,
                "orderInformation" => $orderInformation,
                "buyerInformation" => $buyerInformation
        ];
        $requestObj = new CyberSource\Model\VerifyCustomerAddressRequest($requestObjArr);


        $commonElement = new CyberSource\ExternalConfiguration();
        $config = $commonElement->ConnectionHost();
        $merchantConfig = $commonElement->merchantConfigObject();

        $api_client = new CyberSource\ApiClient($config, $merchantConfig);
        $api_instance = new CyberSource\Api\VerificationApi($api_client);

        try {
            $apiResponse = $api_instance->verifyCustomerAddress($requestObj);
            print_r(PHP_EOL);
            print_r($apiResponse);

            return $apiResponse;
        } catch (Cybersource\ApiException $e) {
            print_r($e->getResponseBody());
            print_r($e->getMessage());
        }
    }

    /* RetrieveTransaction */
    public static function RetrieveTransaction() {
        $id = SimpleAuthorizationInternet('false')[0]['id'];

        sleep(20);

        $commonElement = new CyberSource\ExternalConfiguration();
        $config = $commonElement->ConnectionHost();
        $merchantConfig = $commonElement->merchantConfigObject();

        $api_client = new CyberSource\ApiClient($config, $merchantConfig);
        $api_instance = new CyberSource\Api\TransactionDetailsApi($api_client);

        try {
            $apiResponse = $api_instance->getTransaction($id);
            print_r(PHP_EOL);
            print_r($apiResponse);

            return $apiResponse;
        } catch (Cybersource\ApiException $e) {
            print_r($e->getResponseBody());
            print_r($e->getMessage());
        }
    }

}