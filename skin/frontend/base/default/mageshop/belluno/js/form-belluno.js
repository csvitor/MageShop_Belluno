function maskDocument(document) {
  const i = document.value.length;
  if (i == 11) {
    document.value = maskCpf(document.value);
  }
  if (i == 15) {
    document.value = document.value.replace(/[\.-]/g, "");
  }
  if (i == 14) {
    document.value = maskCnpj(document.value);
  }
  if (i < 14 && i > 11) {
    document.value = document.value.replace(/[\.-]/g, "");
  }
}

function maskPhone(o) {
  if(o.value.length == 0){
		return false;
	}
    setTimeout(function() {
      var v = mphone(o.value.toString());
      if (v != o.value) {
        o.value = v;
      }
    }, 1);
}

function mphone(v) {
  var r = v.replace(/\D/g, "");
  r = r.replace(/^0/, "");
  if (r.length > 10) {
      r = r.replace(/^(\d\d)(\d{5})(\d{4}).*/, "($1) $2-$3");
  } else if (r.length > 5) {
      r = r.replace(/^(\d\d)(\d{4})(\d{0,4}).*/, "($1) $2-$3");
  } else if (r.length > 2) {
      r = r.replace(/^(\d\d)(\d{0,5})/, "($1) $2");
  } else {
      r = r.replace(/^(\d*)/, "($1");
  }
  return r;
}


function maskBirth(document) {
  const i = document.value.length;
  if (i == 2) {
    document.value = document.value + "/";
  } else if (i == 5) {
    document.value = document.value + "/";
  }
}

function maskExpiresAt(input) {
  const i = input.value.length;
  if (i == 2) {
    input.value = input.value + "/";
  }
}

function maskCardNumber(input) {
  input.value = cc_format(input.value);
}

function cc_format(value) {
  var v = value.replace(/\s+/g, "").replace(/[^0-9]/gi, "");
  var matches = v.match(/\d{4,16}/g);
  var match = (matches && matches[0]) || "";
  var parts = [];

  for (i = 0, len = match.length; i < len; i += 4) {
    parts.push(match.substring(i, i + 4));
  }

  if (parts.length) {
    return parts.join(" ");
  } else {
    return value;
  }
}

function maskCpf(cpf) {
  cpf = cpf.replace(/\D/g, "");
  cpf = cpf.replace(/(\d{3})(\d)/, "$1.$2");
  cpf = cpf.replace(/(\d{3})(\d)/, "$1.$2");
  cpf = cpf.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
  return cpf;
}

function maskCnpj(cnpj) {
  cnpj = cnpj.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, "$1.$2.$3/$4-$5");
  return cnpj;
}

function copyTextSucess() {
  navigator.clipboard
    .writeText(document.getElementById("text-digitableline").innerText)
    .then(
      function () {},
      function () {}
    );
}

 var imported = document.createElement("script");
imported.src =
  "https://cdn.jsdelivr.net/npm/node-forge@0.7.0/dist/forge.min.js";
document.head.appendChild(imported);

function encryptRSA(pk, cardHash) {
  var publicKey = pk;
  publicKey = forge.pki.publicKeyFromPem(publicKey);

  // convert string to UTF-8 encoded bytes
  const to_encrypt_value = cardHash,
    buffer = forge.util.createBuffer(to_encrypt_value),
    bytes = buffer.getBytes(),
    // encrypt data with a public key using RSAES PKCS#1 v1.5
    encrypted = publicKey.encrypt(bytes, "RSAES-PKCS1-V1_5"),
    // base64-encode encrypted data to send to server
    b64Encoded = forge.util.encode64(encrypted);
  return b64Encoded;
}

async function updateKey() {
  let data = await fetch(`${baseUrlBelluno}belluno/hash/index`);
  data = await data.json();
  var rsapk = JSON.parse(data);
  var cardNumber = document.getElementById("belluno_card_number").value;
  var expiration = document.getElementById("belluno_expires_at").value.split("/");
  var cardCvv = document.getElementById("belluno_card_cvv").value;

  var pk_saved = rsapk["rsa_public_key"].replace("\r\n", "");
  var querieString = `card_number=${cardNumber.replace(
    /\s/g,
    ""
  )}&card_expiration_date=${expiration[0]}${expiration[1]}&card_cvv=${cardCvv}`;
  // alert(querieString);

  var hashRSA = encryptRSA(pk_saved, querieString);

  document.getElementById("belluno_card_hash").value = `${rsapk["id"]}_${hashRSA}`;

  if(document.getElementById("belluno_card_hash").value == null) {
	  alert('card_hash não pode ser vazio. Por favor, atualize a página e tente novamente!');
	  throw new Error('card_hash não pode ser vazio. Por favor, atualize a página e tente novamente!');
  }
  
  return;
}

async function cc_submit() {
  var is_belluno = document.getElementById("p_method_belluno_creditcardpayment");
  if(is_belluno){
    if (is_belluno.checked) await updateKey();
  }

  review.save();
}

Validation.addAllThese([
  [
    "validate-document",
    "Documento inválido. Verifique por favor",
    function (v) {
      const tamDocument = v.length;
      if (tamDocument == 14) {
        v = v.replace(/\D/g, "");
        if (v.toString().length != 11 || /^(\d)\1{10}$/.test(v)) return false;
        var result = true;
        [9, 10].forEach(function (j) {
          var soma = 0,
            r;
          v.split(/(?=)/)
            .splice(0, j)
            .forEach(function (e, i) {
              soma += parseInt(e) * (j + 2 - (i + 1));
            });
          r = soma % 11;
          r = r < 2 ? 0 : 11 - r;
          if (r != v.substring(j, j + 1)) result = false;
        });
        return result;
      } else if (tamDocument == 18) {
        var cnpj = v.trim();

        cnpj = cnpj.replace(/\./g, "");
        cnpj = cnpj.replace("-", "");
        cnpj = cnpj.replace("/", "");
        cnpj = cnpj.split("");

        var v1 = 0;
        var v2 = 0;
        var aux = false;

        for (var i = 1; cnpj.length > i; i++) {
          if (cnpj[i - 1] != cnpj[i]) {
            aux = true;
          }
        }

        if (aux == false) {
          return false;
        }

        for (var i = 0, p1 = 5, p2 = 13; cnpj.length - 2 > i; i++, p1--, p2--) {
          if (p1 >= 2) {
            v1 += cnpj[i] * p1;
          } else {
            v1 += cnpj[i] * p2;
          }
        }

        v1 = v1 % 11;

        if (v1 < 2) {
          v1 = 0;
        } else {
          v1 = 11 - v1;
        }

        if (v1 != cnpj[12]) {
          return false;
        }

        for (var i = 0, p1 = 6, p2 = 14; cnpj.length - 1 > i; i++, p1--, p2--) {
          if (p1 >= 2) {
            v2 += cnpj[i] * p1;
          } else {
            v2 += cnpj[i] * p2;
          }
        }

        v2 = v2 % 11;

        if (v2 < 2) {
          v2 = 0;
        } else {
          v2 = 11 - v2;
        }

        if (v2 != cnpj[13]) {
          return false;
        } else {
          return true;
        }
      } else {
        return false;
      }
    },
  ],

  [
    "validate-cellphone",
    "Celular inválido. Verifique por favor",
    function (v) {
      const regexNumber =
        /\(?\b([0-9]{2,3}|0((x|[0-9]){2,3}[0-9]{2}))\)?\s*[0-9]{4,5}[- ]*[0-9]{4}\b/gm;
      var isValid = regexNumber.test(v);
      if (!isValid) {
        return false;
      } else {
        return true;
      }
    },
  ],

  [
    "validate-date",
    "Data de nascimento inválida. Verifique por favor",
    function (v) {
      date = v;
      var bits = date.split("/");
      var y = bits[2],
        m = bits[1],
        d = bits[0];

      var daysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

      if ((!(y % 4) && y % 100) || !(y % 400)) {
        daysInMonth[1] = 29;
      }
      return !/\D/.test(String(d)) && d > 0 && d <= daysInMonth[--m];
    },
  ],

  [
    "validate-cardnumber",
    "Número do cartão inválido. Verifique por favor",
    function (v) {
      cardNumber = v.replace(/[\ ]/g, "");

      if (cardNumber.length === 0) return false;

      let digit, digits, flag, sum, _i, _len;
      flag = true;
      sum = 0;
      digits = (cardNumber + "").split("").reverse();
      for (_i = 0, _len = digits.length; _i < _len; _i++) {
        digit = digits[_i];
        digit = parseInt(digit, 10);
        if ((flag = !flag)) {
          digit *= 2;
        }
        if (digit > 9) {
          digit -= 9;
        }
        sum += digit;
      }

      return sum % 10 === 0;
    },
  ],

  [
    "validate-name",
    "Nome inválido. Verifique por favor",
    function (v) {
      if (v == "" || v == " ") {
        return false;
      } else {
        return true;
      }
    },
  ],

  [
    "validate-expires_at",
    "Data de vencimento inválida. Verifique por favor",
    function (v) {
      let dtArray = v.split("/");

      if (dtArray == null) return false;

      var dtMonth = dtArray[0];
      var dtYear = dtArray[1];

      if (!Number(dtMonth)) return false;
      if (!Number(dtYear)) return false;

      if (dtMonth < 1 || dtMonth > 12) return false;

      if (dtYear.length === 3) return false;

      if (dtYear.length === 2) dtYear = "20" + dtYear;

      if (dtYear < new Date().getFullYear() || dtYear > 2050) return false;

      return true;
    },
  ],

  [
    "validate-cvv",
    "CVV inválido. Verifique por favor",
    function (v) {
      const i = v.length;
      if (isNaN(v) || v.includes(" ") || i < 3) {
        return false;
      } else {
        return true;
      }
    },
  ],

  [
    "validate-installment",
    "Número de parcelas inválida. Verifique por favor",
    function (v) {
      if (v == 0) {
        return false;
      } else {
        return true;
      }
    },
  ],
  [
    "validate-expirymonth",
    "Mês inválido. Verifique por favor",
    function (v) {
      if (v > 0) {
        return true;
      } else {
        return false;
      }
    },
  ],
  [
    "validate-expiryyear",
    "Ano inválido. Verifique por favor",
    function (v) {
      if (v > 0) {
        return true;
      } else {
        return false;
      }
    },
  ],
]);