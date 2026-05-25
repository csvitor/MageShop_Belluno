<?php

class MageShop_Belluno_Validations_RegionCodeAPI {

  /**
   * Function to get region code
   * @param string $state
   * @return string
   */
  public function getRegionCode($state) {
    $response = $this->getStates();
    
    foreach ($response as $states) {
      if ($this->removeAccents(strtolower($states['nome'])) == $this->removeAccents(strtolower($state))) {
        return $states['sigla'];
      } else if ($this->removeAccents(strtolower($states['sigla'])) == $this->removeAccents(strtolower($state))) {
        return $states['sigla'];
      }
    }

    Mage::throwException('Região Inaválida. Verifique por favor.');
  }

  /**
   * Function to execute request
   * @return array
   */
  public function getStates() {
    $array = array (
      0 => 
      array (
        'id' => 11,
        'sigla' => 'RO',
        'nome' => 'Rondônia',
        'regiao' => 
        array (
          'id' => 1,
          'sigla' => 'N',
          'nome' => 'Norte',
        ),
      ),
      1 => 
      array (
        'id' => 12,
        'sigla' => 'AC',
        'nome' => 'Acre',
        'regiao' => 
        array (
          'id' => 1,
          'sigla' => 'N',
          'nome' => 'Norte',
        ),
      ),
      2 => 
      array (
        'id' => 13,
        'sigla' => 'AM',
        'nome' => 'Amazonas',
        'regiao' => 
        array (
          'id' => 1,
          'sigla' => 'N',
          'nome' => 'Norte',
        ),
      ),
      3 => 
      array (
        'id' => 14,
        'sigla' => 'RR',
        'nome' => 'Roraima',
        'regiao' => 
        array (
          'id' => 1,
          'sigla' => 'N',
          'nome' => 'Norte',
        ),
      ),
      4 => 
      array (
        'id' => 15,
        'sigla' => 'PA',
        'nome' => 'Pará',
        'regiao' => 
        array (
          'id' => 1,
          'sigla' => 'N',
          'nome' => 'Norte',
        ),
      ),
      5 => 
      array (
        'id' => 16,
        'sigla' => 'AP',
        'nome' => 'Amapá',
        'regiao' => 
        array (
          'id' => 1,
          'sigla' => 'N',
          'nome' => 'Norte',
        ),
      ),
      6 => 
      array (
        'id' => 17,
        'sigla' => 'TO',
        'nome' => 'Tocantins',
        'regiao' => 
        array (
          'id' => 1,
          'sigla' => 'N',
          'nome' => 'Norte',
        ),
      ),
      7 => 
      array (
        'id' => 21,
        'sigla' => 'MA',
        'nome' => 'Maranhão',
        'regiao' => 
        array (
          'id' => 2,
          'sigla' => 'NE',
          'nome' => 'Nordeste',
        ),
      ),
      8 => 
      array (
        'id' => 22,
        'sigla' => 'PI',
        'nome' => 'Piauí',
        'regiao' => 
        array (
          'id' => 2,
          'sigla' => 'NE',
          'nome' => 'Nordeste',
        ),
      ),
      9 => 
      array (
        'id' => 23,
        'sigla' => 'CE',
        'nome' => 'Ceará',
        'regiao' => 
        array (
          'id' => 2,
          'sigla' => 'NE',
          'nome' => 'Nordeste',
        ),
      ),
      10 => 
      array (
        'id' => 24,
        'sigla' => 'RN',
        'nome' => 'Rio Grande do Norte',
        'regiao' => 
        array (
          'id' => 2,
          'sigla' => 'NE',
          'nome' => 'Nordeste',
        ),
      ),
      11 => 
      array (
        'id' => 25,
        'sigla' => 'PB',
        'nome' => 'Paraíba',
        'regiao' => 
        array (
          'id' => 2,
          'sigla' => 'NE',
          'nome' => 'Nordeste',
        ),
      ),
      12 => 
      array (
        'id' => 26,
        'sigla' => 'PE',
        'nome' => 'Pernambuco',
        'regiao' => 
        array (
          'id' => 2,
          'sigla' => 'NE',
          'nome' => 'Nordeste',
        ),
      ),
      13 => 
      array (
        'id' => 27,
        'sigla' => 'AL',
        'nome' => 'Alagoas',
        'regiao' => 
        array (
          'id' => 2,
          'sigla' => 'NE',
          'nome' => 'Nordeste',
        ),
      ),
      14 => 
      array (
        'id' => 28,
        'sigla' => 'SE',
        'nome' => 'Sergipe',
        'regiao' => 
        array (
          'id' => 2,
          'sigla' => 'NE',
          'nome' => 'Nordeste',
        ),
      ),
      15 => 
      array (
        'id' => 29,
        'sigla' => 'BA',
        'nome' => 'Bahia',
        'regiao' => 
        array (
          'id' => 2,
          'sigla' => 'NE',
          'nome' => 'Nordeste',
        ),
      ),
      16 => 
      array (
        'id' => 31,
        'sigla' => 'MG',
        'nome' => 'Minas Gerais',
        'regiao' => 
        array (
          'id' => 3,
          'sigla' => 'SE',
          'nome' => 'Sudeste',
        ),
      ),
      17 => 
      array (
        'id' => 32,
        'sigla' => 'ES',
        'nome' => 'Espírito Santo',
        'regiao' => 
        array (
          'id' => 3,
          'sigla' => 'SE',
          'nome' => 'Sudeste',
        ),
      ),
      18 => 
      array (
        'id' => 33,
        'sigla' => 'RJ',
        'nome' => 'Rio de Janeiro',
        'regiao' => 
        array (
          'id' => 3,
          'sigla' => 'SE',
          'nome' => 'Sudeste',
        ),
      ),
      19 => 
      array (
        'id' => 35,
        'sigla' => 'SP',
        'nome' => 'São Paulo',
        'regiao' => 
        array (
          'id' => 3,
          'sigla' => 'SE',
          'nome' => 'Sudeste',
        ),
      ),
      20 => 
      array (
        'id' => 41,
        'sigla' => 'PR',
        'nome' => 'Paraná',
        'regiao' => 
        array (
          'id' => 4,
          'sigla' => 'S',
          'nome' => 'Sul',
        ),
      ),
      21 => 
      array (
        'id' => 42,
        'sigla' => 'SC',
        'nome' => 'Santa Catarina',
        'regiao' => 
        array (
          'id' => 4,
          'sigla' => 'S',
          'nome' => 'Sul',
        ),
      ),
      22 => 
      array (
        'id' => 43,
        'sigla' => 'RS',
        'nome' => 'Rio Grande do Sul',
        'regiao' => 
        array (
          'id' => 4,
          'sigla' => 'S',
          'nome' => 'Sul',
        ),
      ),
      23 => 
      array (
        'id' => 50,
        'sigla' => 'MS',
        'nome' => 'Mato Grosso do Sul',
        'regiao' => 
        array (
          'id' => 5,
          'sigla' => 'CO',
          'nome' => 'Centro-Oeste',
        ),
      ),
      24 => 
      array (
        'id' => 51,
        'sigla' => 'MT',
        'nome' => 'Mato Grosso',
        'regiao' => 
        array (
          'id' => 5,
          'sigla' => 'CO',
          'nome' => 'Centro-Oeste',
        ),
      ),
      25 => 
      array (
        'id' => 52,
        'sigla' => 'GO',
        'nome' => 'Goiás',
        'regiao' => 
        array (
          'id' => 5,
          'sigla' => 'CO',
          'nome' => 'Centro-Oeste',
        ),
      ),
      26 => 
      array (
        'id' => 53,
        'sigla' => 'DF',
        'nome' => 'Distrito Federal',
        'regiao' => 
        array (
          'id' => 5,
          'sigla' => 'CO',
          'nome' => 'Centro-Oeste',
        ),
      ),
    );
    return $array;
  }

  /**
   * Function to remove accents from strings
   * @param string $name
   * @return string
   */
  public function removeAccents($name) {
    return preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/"),explode(" ","a A e E i I o O u U n N"),$name);
  }

}