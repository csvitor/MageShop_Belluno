<?php

class MageShop_Belluno_Model_Payment_Pix_CreateRequest {

  const VALUE = 'value';
  const CLIENT = 'client';
  const NAME = 'client_name';
  const DOCUMENT = 'client_document';
  const DETAILS = 'details';
  const EMAIL = 'client_email';
  const PHONE = 'client_cellphone';
  const BILLING = 'billing';
  const POSTALCODE = 'postalCode';
  const DISTRICT = 'district';
  const ADDRESS = 'address';
  const NUMBER = 'number';
  const CITY = 'city';
  const STATE = 'state';
  const COUNTRY = 'country';
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
   * @return string
   */
  public function createRequest($data, $info) {
    $quote = $this->getQuote();
    $customerId = $quote->getCustomerId();
    $billingAddress = $quote->getBillingAddress();

    //body request
    $value = $quote->getBaseGrandTotal();
    $documentCode = $quote->getReservedOrderId();
    //client
    $clientName = $quote->getCustomerFirstname() . ' ' . $quote->getCustomerLastname();

    if(strlen($clientName) > 100){
       $clientName = $quote->getCustomerFirstname();
    }

    $taxDocument = $this->getUseTaxDocumentCapture();
    if ($taxDocument == true) {
      $clientDocument = $data['client_document'];
    } else {
      $clientDocument = $this->getTaxVat($customerId);
      $clientDocument = $this->formatCpfCnpj($clientDocument);
    }
    if (empty($clientDocument)) {
      $clientDocument = $quote->getCustomerTaxvat();
      $clientDocument = $this->formatCpfCnpj($clientDocument);
    }

    $clientEmail = $quote->getCustomerEmail();
    $clientPhone = $billingAddress->getTelephone();
    //billing
    $postalCode = $billingAddress->getPostcode();
    $postalCode = preg_replace('/[^0-9]/is', '', $postalCode);
    $postalCode = substr_replace($postalCode, '-', 5, 0);
    $district = $billingAddress->getRegion();
    $address = $billingAddress->getStreet(1);
    $number = $billingAddress->getStreet(2);
    $city = $billingAddress->getCity();
    $state = $this->getRegionCodeAPI()->getRegionCode($billingAddress->getRegion());
    $country = $billingAddress->getCountryId();
    //cart
    $items = $quote->getAllItems();
    $subTotal = $quote->getSubtotal();
    $shippingValue = $value - $subTotal;
    if(abs($shippingValue) < 0.00000001){
      $shippingValue = 0;
    }

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
   
    $request['transaction'] = [
      self::VALUE => $value,
      self::NAME => $clientName,
      self::DOCUMENT => $clientDocument,
      self::EMAIL => $clientEmail,
      self::PHONE => $clientPhone,
      self::DETAILS => $documentCode,
      self::BILLING => [
        self::POSTALCODE => $postalCode,
        self::DISTRICT => $district,
        self::ADDRESS => $address,
        self::NUMBER => $number,
        self::CITY => $city,
        self::STATE => $state,
        self::COUNTRY => $country
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
   * Get checkout session 
   * @return Mage_Checkout_Model_Session
   */
  public function getCheckout() {
    return Mage::getSingleton('checkout/session');
  }

  /**
   * Get current quote
   * @return Mage_Sales_Model_Quote
   */
  public function getQuote() {
    return $this->getCheckout()->getQuote();
  }


  /**
   * Function to get tax document
   */
  public function getUseTaxDocumentCapture() {
    return Mage::getStoreConfig('payment/belluno_pix/capture_tax');
  }

  /**
   * Function to format cpf and cnpj
   */
  public function formatCpfCnpj($doc) {
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
  public function getTaxVat($customerId) {
    $customer = Mage::getModel('customer/customer')->load($customerId);
    $vatNumber = $customer->getData('taxvat');
    return $vatNumber;
  }

  /**Function to return class region code API */
  public function getRegionCodeAPI() {
    return new MageShop_Belluno_Validations_RegionCodeAPI();
  }
}
