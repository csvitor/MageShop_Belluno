<?php

class MageShop_Belluno_Block_Info_Creditcard extends Mage_Payment_Block_Info {
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('mageshop/belluno/payment/info/creditcard.phtml');
    }
}
