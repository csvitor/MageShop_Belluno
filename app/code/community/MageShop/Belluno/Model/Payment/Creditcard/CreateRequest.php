<?php

class MageShop_Belluno_Model_Payment_Creditcard_CreateRequest
{

    const VALUE = 'value';
    const CAPTURE = 'capture';
    const CARD_HASH = 'card_hash';
    const CARDHOLDER_NAME = 'cardholder_name';
    const CARDHOLDER_DOCUMENT = 'cardholder_document';
    const CARDHOLDER_CELLPHONE = 'cardholder_cellphone';
    const CARDHOLDER_BIRTH = 'cardholder_birth';
    const BRAND = 'brand';
    const INSTALLMENT_NUMBER = 'installment_number';
    const VISITOR_ID = 'visitor_id';
    const PAYER_IP = 'payer_ip';
    const CLIENT_NAME = 'client_name';
    const CLIENT_DOCUMENT = 'client_document';
    const CLIENT_EMAIL = 'client_email';
    const CLIENT_CELLPHONE = 'client_cellphone';
    const DETAILS = 'details';
    const SHIPPING = 'shipping';
    const POSTALCODE = 'postalCode';
    const STREET = 'street';
    const NUMBER = 'number';
    const CITY = 'city';
    const STATE = 'state';
    const BILLING = 'billing';
    const CART = 'cart';
    const PRODUCT_NAME = 'product_name';
    const QUANTITY = 'quantity';
    const UNIT_VALUE = 'unit_value';
    const POSTBACK = 'postback';
    const URL = 'url';

    /**
     * Function to create request
     * @param $data
     * @param $info
     */
    public function createRequest($data, $info)
    {
        $quote = $this->getQuote();
        $customerId = $quote->getCustomerId();
        $shippingAddress = $quote->getShippingAddress();
        $billingAddress = $quote->getBillingAddress();
        $documentCode = $quote->getReservedOrderId();

        $cardNumber = preg_replace('/[^0-9]/is', '', $data['card_number']);

        $taxDocument = $this->getUseTaxDocumentCapture();

        if ($taxDocument == true) {
            $clientDocument = $data['client_document'];
            $cardHolderDocument = $data['card_holder_document'];
        } else {
            $clientDocument = $this->getTaxVat($customerId);
            $clientDocument = $this->formatCpfCnpj($clientDocument);
            $cardHolderDocument = $clientDocument;
        }
        
        if (empty($clientDocument)) {
            $clientDocument = $quote->getCustomerTaxvat();
            $clientDocument = $this->formatCpfCnpj($clientDocument);
            $cardHolderDocument = $clientDocument;
        }

        $totalValue = $quote->getBaseGrandTotal();
        $dataValue = $this->getValueWithInterest($totalValue, $data['card_installment']);
        if ($dataValue['total'] > $totalValue) {
            $totalValue = $dataValue['total'];
        }


        if(!isset($data['card_hash']) || $data['card_hash'] == null) {
			Mage::throwException('card_hash não pode ser vazio. Por favor, atualize a página e tente novamente!');
		}
        
        //body request

        $value = $totalValue;
        $capture = $this->getRuleCapture();
        $cardHash = $data['card_hash'];
        $cardHolderName = $data['name_on_card'];
        $cardHolderCellphone = $data['card_holder_cellphone'];
        $cardHolderBirth = $data['card_holder_birth'];
        $brand = $this->getCreditCardValidator()->getCardTypeBelluno($cardNumber);
        $installmentNumber = ($data['card_installment']);
        $visitorId = $data['visitor_id'];
        $payerIp = $quote->getRemoteIp();
        $clientName = $quote->getCustomerFirstname() . ' ' . $quote->getCustomerLastname();
        if(strlen($clientName) > 100){
             $clientName = $quote->getCustomerFirstname();
        }

        $clientEmail = $quote->getCustomerEmail();
        $clientPhone = $cardHolderCellphone;
        //shipping
        $postalCode = $shippingAddress->getPostcode();
        $postalCode = preg_replace('/[^0-9]/is', '', $postalCode);
        $postalCode = substr_replace($postalCode, '-', 5, 0);
        $street = $shippingAddress->getStreet(1);
        $number = $shippingAddress->getStreet(2);
        $city = $shippingAddress->getCity();
        $state = $this->getRegionCodeAPI()->getRegionCode($shippingAddress->getRegion());
        //billing
        $bpostalCode = $billingAddress->getPostcode();
        $bpostalCode = preg_replace('/[^0-9]/is', '', $bpostalCode);
        $bpostalCode = substr_replace($bpostalCode, '-', 5, 0);
        $bstreet = $billingAddress->getStreet(1);
        $bnumber = $billingAddress->getStreet(2);
        $bcity = $billingAddress->getCity();
        $bstate = $this->getRegionCodeAPI()->getRegionCode($billingAddress->getRegion());
        //cart
        $items = $quote->getAllItems();
        $subTotal = $quote->getSubtotal();
        $shippingValue = $value - $subTotal - $dataValue['valueInterest'];
        if(abs($shippingValue) < 0.00000001){
            $shippingValue = 0;
        }

        $card_flag = $data['card_flag'];
        //create array for show in admin
        $this->createViewInformations(
            $value,
            $cardNumber,
            $card_flag,
            $installmentNumber,
            $cardHolderName,
            $cardHolderCellphone,
            $cardHolderDocument,
            $clientName,
            $clientDocument,
            $clientPhone,
            $info
        );

        foreach ($items as $item) {
            if ($item->getProductType() == 'simple' || $item->getProductType() == 'grouped') {
                if ($item->getPrice() == 0) {
                    $parentItem = $item->getParentItem();
                    $price = $parentItem->getPrice();
                } else {
                    $price = $item->getPrice();
                }
                $array[] = [
                    self::PRODUCT_NAME => $item->getName(),
                    self::QUANTITY => $item->getQty(),
                    self::UNIT_VALUE => $price
                ];
            }
        }
        if ($shippingValue > 0) {
            $array[] = [
                self::PRODUCT_NAME => 'Envio',
                self::QUANTITY => '1',
                self::UNIT_VALUE => $shippingValue
            ];
        }

        if ($dataValue['valueInterest'] != 0) {
            $array[] = [
                self::PRODUCT_NAME => 'Interest',
                self::QUANTITY => '1',
                self::UNIT_VALUE => $dataValue['valueInterest']
            ];
        }

        $request['transaction'] = [
            self::VALUE => $value,
            self::CAPTURE => $capture,
            self::CARD_HASH => $cardHash,
            self::CARDHOLDER_NAME => $cardHolderName,
            self::CARDHOLDER_DOCUMENT => $cardHolderDocument,
            self::CARDHOLDER_CELLPHONE => $cardHolderCellphone,
            self::CARDHOLDER_BIRTH => $cardHolderBirth,
            self::BRAND => $brand,
            self::INSTALLMENT_NUMBER => $installmentNumber,
            self::VISITOR_ID => $visitorId,
            self::PAYER_IP => $payerIp,
            self::CLIENT_NAME => $clientName,
            self::CLIENT_DOCUMENT => $clientDocument,
            self::CLIENT_EMAIL => $clientEmail,
            self::CLIENT_CELLPHONE => $clientPhone,
            self::DETAILS => $documentCode,
            self::SHIPPING => [
                self::POSTALCODE => $postalCode,
                self::STREET => $street,
                self::NUMBER => $number,
                self::CITY => $city,
                self::STATE => $state
            ],
            self::BILLING => [
                self::POSTALCODE => $bpostalCode,
                self::STREET => $bstreet,
                self::NUMBER => $bnumber,
                self::CITY => $bcity,
                self::STATE => $bstate
            ],
            self::CART => $array,
            self::POSTBACK => [
                self::URL => Mage::getBaseUrl() . 'belluno/webhook/postback'
            ]
        ];
        $request = json_encode($request);
        $info->setAdditionalInformation("data", $request);
        return $request;
    }

    /**
     * Function to get total value with interest
     * @param string $totalValue
     * @param string $installmentNumber
     * @return array
     */
    public function getValueWithInterest($totalValue, $installmentNumber)
    {
        $interest = Mage::getStoreConfig('payment/belluno_creditcard/installment_interest');
        $maxInstallment = Mage::getStoreConfig('payment/belluno_creditcard/installments');

        $interest = unserialize($interest);
        $valueInterest = 0;

        $i = 1;
        foreach ($interest as $value) {
            if ($i == $installmentNumber) {
                if ($value['from_qty'] > $valueInterest) {
                    $interestPercent = $value['from_qty'];
                }
            }
            $i++;
        }

        $valueInterest = (($interestPercent / 100) * $totalValue);
        $totalValue = $totalValue + (($interestPercent / 100) * $totalValue);

        return [
            'total' => $totalValue,
            'valueInterest' => $valueInterest
        ];
    }

    /**
     * Function to ajust sensitive informations
     * @param string $value
     * @param string $cardNumber
     * @param string $installmentsNumber
     * @param string $cardHolderName
     * @param string $cardHolderCellphone
     * @param string $cardHolderDocument
     * @param string $clientName ,
     * @param string $clientDocument
     * @param string $clientPhone
     * @param $info
     */
    public function createViewInformations(
        $value,
        $cardNumber,
        $card_flag,
        $installmentNumber,
        $cardHolderName,
        $cardHolderCellphone,
        $cardHolderDocument,
        $clientName,
        $clientDocument,
        $clientPhone,
        $info
    )
    {
        $cardNumber = substr($cardNumber, -5, -1);

        $view = [
            __("Valor total:"),
            $value,
            __("Digitos finais do cartão:"),
            $cardNumber,
            __("Bandeira:"),
            $card_flag,
            __("Número de parcelas:"),
            $installmentNumber,
            __("Nome do titular do cartão:"),
            $cardHolderName,
            __("Documento do titular do cartão:"),
            $cardHolderDocument,
            __("Celular do titular do cartão:"),
            $cardHolderCellphone,
            __("Nome do cliente:"),
            $clientName,
            __("Documento do cliente:"),
            $clientDocument,
            __("Celular do cliente:"),
            $clientPhone
        ];

        $view = json_encode($view);
        $info->setAdditionalInformation("view", $view);
    }

    /**
     * Get checkout session
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get current quote
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

    /**
     * Function to get rule capture
     */
    public function getRuleCapture()
    {
        return 1;
    }

    /**
     * Function to format cpf and cnpj
     */
    public function formatCpfCnpj($doc)
    {
        $doc = preg_replace("/[^0-9]/", "", $doc);
        $qtd = strlen($doc);

        if ($qtd >= 11) {
            if ($qtd === 11) {

                $docFormatado = substr($doc, 0, 3) . '.' .
                    substr($doc, 3, 3) . '.' .
                    substr($doc, 6, 3) . '-' .
                    substr($doc, 9, 2);
            } else {
                $docFormatado = substr($doc, 0, 2) . '.' .
                    substr($doc, 2, 3) . '.' .
                    substr($doc, 5, 3) . '/' .
                    substr($doc, 8, 4) . '-' .
                    substr($doc, -2);
            }

            return $docFormatado;
        } else {
            return false;
        }
    }

    /**
     * Function to get TaxVat
     * @param $customerId
     */
    public function getTaxVat($customerId)
    {
        $customer = Mage::getModel('customer/customer')->load($customerId);
        $vatNumber = $customer->getData('taxvat');
        return $vatNumber;
    }

    /**
     * Function to get tax document
     */
    public function getUseTaxDocumentCapture()
    {
        return Mage::getStoreConfig('payment/belluno_creditcard/capture_tax');
    }

    /**Function to return class region code API */
    public function getRegionCodeAPI()
    {
        return new MageShop_Belluno_Validations_RegionCodeAPI();
    }

    /**Function to return class credit card validator */
    public function getCreditCardValidator()
    {
        return new MageShop_Belluno_Validations_CreditCardValidator();
    }
}
