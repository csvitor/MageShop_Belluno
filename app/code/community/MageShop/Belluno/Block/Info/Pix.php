<?php

class MageShop_Belluno_Block_Info_Pix extends Mage_Payment_Block_Info {
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('mageshop/belluno/payment/info/pix.phtml');
    }
}
