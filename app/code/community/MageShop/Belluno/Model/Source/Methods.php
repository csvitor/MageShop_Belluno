<?php

class MageShop_Belluno_Model_Source_Methods {

  /*
  * Function to get environment options
  * @return array
  */
  public function toOptionArray() {
    return array(
      array('value'=>'1','label'=>'Cartão'),
      array('value'=>'2','label'=>'Pix')
		);	
  }
}
