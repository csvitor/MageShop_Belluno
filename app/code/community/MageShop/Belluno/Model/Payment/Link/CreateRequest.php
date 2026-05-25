<?php

class MageShop_Belluno_Model_Payment_Link_CreateRequest {

  const VALUE = 'value';
  const CAPTURE = 'capture';
  const PAYMENT_METHODS = 'payment_methods';
  const INSTALLMENT_NUMBER = 'installment_number';
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
   * @param $payment
   * @return string
   */
  public function createRequest($payment) {
    $order = $payment->getOrder();
    $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
    $billingAddress = $order->getBillingAddress();
    //body request
    $value = $order->getBaseGrandTotal();
    // verifica se foi definido o id_increment
    if(!$order->getReservedOrderId()){
      $order->setReservedOrderId($order->getIncrementId());
    }

    $documentCode = $order->getReservedOrderId();
    //client
    $clientName = $customer->getName();
    if(strlen($clientName) > 100){
       $clientName = $customer->getCustomerFirstname();
    }
    $clientDocument = $customer->getData('taxvat');
    $clientDocument = $this->formatCpfCnpj($clientDocument);
    
    if (empty($clientDocument)) {
      $clientDocument = $customer->getCustomerTaxvat();
      $clientDocument = $this->formatCpfCnpj($clientDocument);
    }
    $clientEmail = $customer->getEmail();
    $clientPhone = $billingAddress->getTelephone();
    $capture = $this->getRuleCapture();
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
    $items = $order->getAllItems();
    $subTotal = $order->getSubtotal();
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
        $cart[] = [
          self::PRODUCT_NAME => $item->getName(),
          self::QUANTITY => $item->getQtyOrdered(),
          self::UNIT_VALUE => $price
        ];
      }
    }
    if ($shippingValue > 0) {
      $cart[] = [
        self::PRODUCT_NAME => "Envio",
        self::QUANTITY => '1',
        self::UNIT_VALUE => $shippingValue
      ];
    }
    $request['transaction'] = [
      self::VALUE => $value,
      self::CAPTURE => $capture,
      self::PAYMENT_METHODS => $this->getPaymentMethodsSelect(),
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
      self::CART => $cart,
      self::POSTBACK => [
        self::URL => Mage::getBaseUrl() . 'belluno/webhook/postback'
      ]
    ];
    $request = json_encode($request);
    $order->setAdditionalInformation("data", $request);
    return $request;
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

  public function getPaymentMethodsSelect()
  {
    $res = [1,2];
    $payment_method = Mage::getStoreConfig("payment/belluno_link/payment_methods");
    if(strlen($payment_method) > 0 && !empty($payment_method)){
      $res = array_filter(explode(",", $payment_method));
    }
    return $res;
  }
  /**Function to return class region code API */
  public function getRegionCodeAPI() {
    return new MageShop_Belluno_Validations_RegionCodeAPI();
  }

    /**
     * Function to get tax document
     */
    public function getInstallmentsNumber()
    {
        return Mage::getStoreConfig('payment/belluno_link/installments');
    }
}
