<?php

class MageShop_Belluno_Model_Source_Environment {

  /*
  * Function to get environment options
  * @return array
  */
  public function toOptionArray() {
    $array = [
      [
        'value' => 'sandbox',
        'label' => __('Sandbox - Environment for tests')
      ],
      [
        'value' => 'production',
        'label' => __('Production')
      ]  
    ];

    return $array;
  }
}
