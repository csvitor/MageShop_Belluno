<?php

class MageShop_Belluno_Model_Source_Logs{

  const MS_BELLUNO_ACTION_SYSTEM = 1;
  const MS_BELLUNO_ACTION_TRANSACTION = 2;
  const MS_BELLUNO_ACTION_ALL = 3;
  /*
  * Function to get environment options
  * @return array
  */
  public function toOptionArray() {
    $array = [
      [
        'value' => MageShop_Belluno_Model_Source_Logs::MS_BELLUNO_ACTION_SYSTEM,
        'label' => 'Não gerar log'
      ],
      [
        'value' => MageShop_Belluno_Model_Source_Logs::MS_BELLUNO_ACTION_TRANSACTION,
        'label' => 'Gerar log transação'
      ],
      [
        'value' => MageShop_Belluno_Model_Source_Logs::MS_BELLUNO_ACTION_ALL,
        'label' => 'Gerar log de todos os eventos'
      ]
    ];
    return $array;
  }
}