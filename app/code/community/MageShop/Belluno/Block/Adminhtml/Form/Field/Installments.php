<?php

class MageShop_Belluno_Block_Adminhtml_Form_Field_Installments extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract {

  /**
   * Prepare rendering the new field by adding all the needed columns
   */
  protected function _prepareToRender() {
    $this->addColumn('from_qty', ['label' => __('Installment Interest'), 'class' => 'required-entry validate-length maximum-length-5 minimum-length-0 validate-number']);
    $this->_addAfter = false;
    $this->_addButtonLabel = __('Add');
  }
  
}
