var CreditCardValidation = {
  // takes the form field value and returns true on valid number
  checkCredit: function valid_credit_card(value) {
      if (!(value.length > 0)) return false;
      if (/[^0-9-\s]+/.test(value)) return false; // accept only digits, dashes or spaces
      var nCheck = 0,
          nDigit = 0,
          bEven = false;
      value = value.replace(/\D/g, "");
      for (var n = value.length - 1; n >= 0; n--) {
          var cDigit = value.charAt(n),
              nDigit = parseInt(cDigit, 10);
          if (bEven) {
              if ((nDigit *= 2) > 9) nDigit -= 9;
          }
          nCheck += nDigit;
          bEven = !bEven;
      }
      return nCheck % 10 == 0;
  },
  getCardFlag: function(cardnumber) {
      var cardnumber = cardnumber.replace(/[^0-9]+/g, "");
      var cards = {
          visa: /^4[0-9]{12}(?:[0-9]{3})?$/,
          mastercard: /^(603136|603689|608619|606200|603326|605919|608783|607998|603690|604891|603600|603134|608718|603680|608710|604998)|(5[1-5][0-9]{14}|2221[0-9]{12}|222[2-9][0-9]{12}|22[3-9][0-9]{13}|2[3-6][0-9]{14}|27[01][0-9]{13}|2720[0-9]{12})$/,
          diners: /^3(?:0[0-5]|[68][0-9])[0-9]{11}$/,
          amex: /^3[47][0-9]{13}$/,
          discover: /^6(?:011|5[0-9]{2}|4[4-9][0-9]{1}|(22(12[6-9]|1[3-9][0-9]|[2-8][0-9]{2}|9[01][0-9]|92[0-5]$)[0-9]{10}$))[0-9]{12}$/,
          hipercard: /^(38[0-9]{17}|60[0-9]{14})$/,
          elo: /^(401178|401179|431274|438935|451416|457393|457631|457632|504175|627780|636297|636368|(506699|5067[0-6]\d|50677[0-8])|(50900\d|5090[1-9]\d|509[1-9]\d{2})|65003[1-3]|(65003[5-9]|65004\d|65005[0-1])|(65040[5-9]|6504[1-3]\d)|(65048[5-9]|65049\d|6505[0-2]\d|65053[0-8])|(65054[1-9]|6505[5-8]\d|65059[0-8])|(65070\d|65071[0-8])|65072[0-7]|(65090[1-9]|65091\d|650920)|(65165[2-9]|6516[6-7]\d)|(65500\d|65501\d)|(65502[1-9]|6550[3-4]\d|65505[0-8]))[0-9]{10,12}/,
          jcb: /^(?:2131|1800|35\d{3})\d{11}$/,
          aura: /^50[0-9]{14,17}$/,
          cabal: /^(60420[1-9]|6042[1-9][0-9]|6043[0-9]{2}|604400)/,
          hiper: /^(6370950000000005|637095|637609|637599|637612|637568|63737423|63743358)/,
      };
      for (var flag in cards) {
          if (cards[flag].test(cardnumber)) {
              return flag;
          }
      }
      return false;
  },
  checkCVV: function(cvv) {
      if (cvv.length == 0) return false;
      if (cvv.length != 3 && cvv.length != 4) return false;
      return true;
  },
  checkName: function(name) {
      if (name.length == 0) return false;
      if (name.length > 50) return false;
      return true;
  },
  checkPhone: function(phone) {
      phone = phone.replace("(", "");
      phone = phone.replace(")", "");
      phone = phone.replace("-", "");
      phone = phone.replace(" ", "");
      var regex = new RegExp(
          "^((1[1-9])|([2-9][0-9]))((3[0-9]{3}[0-9]{4})|(9[0-9]{3}[0-9]{5}))$"
      );
      return regex.test(phone);
  },
  checkCPF: function(cpf) {
      cpf = cpf.replace(/\D/g, "");
      if (cpf.toString().length != 11 || /^(\d)\1{10}$/.test(cpf)) return false;
      var result = true;
      [9, 10].forEach(function(j) {
          var soma = 0,
              r;
          cpf
              .split(/(?=)/)
              .splice(0, j)
              .forEach(function(e, i) {
                  soma += parseInt(e) * (j + 2 - (i + 1));
              });
          r = soma % 11;
          r = r < 2 ? 0 : 11 - r;
          if (r != cpf.substring(j, j + 1)) result = false;
      });
      return result;
  },
  checkCNPJ: function(cnpj) {
      if (!cnpj) return false;

      // Aceita receber o valor como string, número ou array com todos os dígitos
      const isString = typeof cnpj === "string";
      const validTypes =
          isString || Number.isInteger(cnpj) || Array.isArray(cnpj);

      // Elimina valor em formato inválido
      if (!validTypes) return false;

      // Filtro inicial para entradas do tipo string
      if (isString) {
          // Limita ao máximo de 18 caracteres, para CNPJ formatado
          if (cnpj.length > 18) return false;

          // Teste Regex para veificar se é uma string apenas dígitos válida
          const digitsOnly = /^\d{14}$/.test(cnpj);
          // Teste Regex para verificar se é uma string formatada válida
          const validFormat = /^\d{2}.\d{3}.\d{3}\/\d{4}-\d{2}$/.test(cnpj);

          // Se o formato é válido, usa um truque para seguir o fluxo da validação
          if (digitsOnly || validFormat) true;
          // Se não, retorna inválido
          else return false;
      }

      // Guarda um array com todos os dígitos do valor
      const match = cnpj.toString().match(/\d/g);
      const numbers = Array.isArray(match) ? match.map(Number) : [];

      // Valida a quantidade de dígitos
      if (numbers.length !== 14) return false;

      // Elimina inválidos com todos os dígitos iguais
      const items = [...new Set(numbers)];
      if (items.length === 1) return false;

      // Cálculo validador
      const calc = (x) => {
          const slice = numbers.slice(0, x);
          let factor = x - 7;
          let sum = 0;

          for (let i = x; i >= 1; i--) {
              const n = slice[x - i];
              sum += n * factor--;
              if (factor < 2) factor = 9;
          }

          const result = 11 - (sum % 11);

          return result > 9 ? 0 : result;
      };

      // Separa os 2 últimos dígitos de verificadores
      const digits = numbers.slice(12);

      // Valida 1o. dígito verificador
      const digit0 = calc(12);
      if (digit0 !== digits[0]) return false;

      // Valida 2o. dígito verificador
      const digit1 = calc(13);
      return digit1 === digits[1];
  },
  checkExpirationDate: function(data) {
      let dtArray = data.split("/");

      if (dtArray == null) return false;

      var dtMonth = dtArray[0];
      var dtYear = dtArray[1];

      if (dtMonth < 1 || dtMonth > 12) return false;

      if (dtYear < new Date().getFullYear() || dtYear > 2050) return false;

      return true;
  },
  checkBirthdayDate: function(data) {
      let dtArray = data.split("/");

      if (dtArray == null) return false;

      var dtDay = dtArray[0];
      var dtMonth = dtArray[1];
      var dtYear = dtArray[2];

      if (dtYear < 1945 || dtYear >= new Date().getFullYear()) return false;

      if (dtMonth < 1 || dtMonth > 12) return false;
      else if (dtDay < 1 || dtDay > 31) return false;
      else if (
          (dtMonth == 4 || dtMonth == 6 || dtMonth == 9 || dtMonth == 11) &&
          dtDay == 31
      )
          return false;
      else if (dtMonth == 2) {
          var isleap = dtYear % 4 == 0 && (dtYear % 100 != 0 || dtYear % 400 == 0);
          if (dtDay > 29 || (dtDay == 29 && !isleap)) return false;
      }

      return true;
  },
};
