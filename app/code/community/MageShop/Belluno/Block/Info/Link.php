<?php

class MageShop_Belluno_Block_Info_Link extends Mage_Payment_Block_Info {
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('mageshop/belluno/payment/info/link.phtml');
    }
}
