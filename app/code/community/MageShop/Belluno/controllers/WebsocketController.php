<?php

class MageShop_Belluno_WebsocketController extends MageShop_Belluno_Controller_AbstractController {
  private $resulstApi = [];
  private $error = null;
  private $img_res_frontend = null;
  private $comment_res_frontend = null;
  const IMAGE_PAYMENT_SUCCESS = "media/mageshop/belluno/images/payment_sucess.png";

   /**
   * Function to get websocket from belluno @author Vitor <web@tryideas.com.br>
   */
  public function indexAction() {
      $api = $this->getConnector();
      $methodsPayments = $this->getRequest()->getParams();
      if(empty($methodsPayments)){
        $this->error [] = array(
          "res" => false,
          "code" => "401",
          "message" => "Parametro não encontrado"
        );
      }

      Mage::helper("belluno")->log( json_encode( $methodsPayments ), 'bulluno-payment-websocket.log');
      foreach ($methodsPayments as $key => $transaction_id) {
        if(!empty($transaction_id) || strlen($transaction_id)){
          $uri = $this->_methodPayment( $key, $transaction_id);
          $this->resulstApi = json_decode($api->doRequest('', "GET", $uri), true);
          Mage::helper("belluno")->log( json_encode( $this->resulstApi ), 'bulluno-payment-websocket.log');
          if($this->resulstApi){
            $this->order();
          }else{
            $this->error [$transaction_id] = array(
              "response" => false,
              "code" => "401",
              "message" => "Pedido não encontrado"
            );
            break;
          }
        }else{
          $this->error [] = array(
            "response" => false,
            "code" => "401",
            "message" => "transaction_id null"
          );
          break;
        }

      }
      
      if($this->error !== null){
        echo json_encode($this->error);
        return false;
      }
  
      echo json_encode(array(
        "response" => true,
        "code" => "200",
        "message" => $this->comment_res_frontend,
        "img" =>  $this->img_res_frontend,
      ));
      return true;

  }

  private function order(){

    if(isset($this->resulstApi['transaction']) && count($this->resulstApi['transaction']) > 0){
        $orderId = $this->resulstApi['transaction']['details'];
        $statusBelluno = $this->resulstApi['transaction']['status'];
        $this->comment = $this->resulstApi['transaction']['reason'];
    
    }else if(isset($this->resulstApi['bankslip']) && count($this->resulstApi['bankslip']) > 0){
        $orderId = $this->resulstApi['bankslip']['document_code'];
        $statusBelluno = $this->resulstApi['bankslip']['status'];
        $this->comment = $this->resulstApi['bankslip']['reason'];
    }


    if (empty($orderId) || empty($statusBelluno)) {
      $this->error [] = array(
        "response" => false,
        "code" => "402",
        "message" =>'402 ' + $orderId
      );
      return false;
    }

    $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
    $orderStatusMagento = $order->getStatus();
  
    if($orderStatusMagento == 'canceled' || $orderStatusMagento == 'closed' ){
      return true;
    }

    switch ($statusBelluno) {
        case self::BL_STATUS_PAID:
            parent::payments($this->resulstApi);
            $this->_paid($order, $statusBelluno);
            $this->img_res_frontend = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . self::IMAGE_PAYMENT_SUCCESS;
            $this->comment_res_frontend =  "O pagamento deste pedido foi confirmado com sucesso! 
            Seu produto será enviado em breve e você receberá um e-mail de confirmação assim que ele for despachado.
            Agradecemos a sua compra e ficamos à disposição para eventuais dúvidas ou problemas.";
        break;
        case self::BL_STATUS_CC_ANALYSIS:
        case self::BL_STATUS_CC_CLIENT_ANALYSIS:
            $this->_holded($order, $statusBelluno);
        break;
        case self::BL_STATUS_REFUSED:
        case self::BL_STATUS_EXPIRED:
        case self::BL_STATUS_INACTIVATED:
        case self::BL_STATUS_CANCELLED:
        case self::BL_STATUS_BL_CL_BY_DEADLINE:
        case self::BL_STATUS_BL_CL_BY_REQUEST:
        case self::BL_STATUS_BL_CL_REQUEST:
        case self::BL_STATUS_CC_EXPIRED_USER_ANALYSIS:
            $this->_cancelled($order, $statusBelluno);
            $this->img_res_frontend = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . self::IMAGE_PAYMENT_SUCCESS;
        break;
        default:
        $history = $order->addStatusHistoryComment('Gateway Belluno: Atualização via websocket | Status: '.$statusBelluno.'', false);
        $history->setIsCustomerNotified(false); 
        $order->save();
    }
  }

  private function _methodPayment($key, $transaction_id)
  {
    switch ($key) {
      case 'belluno_creditcard':
        return "/v2/transaction/{$transaction_id}";
      case 'belluno_bankslip':
        return "/v2/bankslip/{$transaction_id}";
      case 'belluno_pix':
        return "/v2/transaction/{$transaction_id}/pix";
    }
  }
  
  /**Function to return class connector for requests */
  private function getConnector()
  {
      return new MageShop_Belluno_Service_ApiBelluno();
  }
}
