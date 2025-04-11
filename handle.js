// tao khoa
let e, d, n;
const smallPrimes = [11n, 13n, 17n, 19n, 23n, 29n, 31n, 37n, 41n, 43n, 47n];

function gcd(a, b) {
  return b === 0n ? a : gcd(b, a % b);
}

function modInverse(a, m) {
  let m0 = m, x0 = 0n, x1 = 1n;
  while (a > 1n) {
    let q = a / m;
    [a, m] = [m, a % m];
    [x0, x1] = [x1 - q * x0, x0];
  }
  return x1 < 0n ? x1 + m0 : x1;
}


function getRandomPrime() {
  return smallPrimes[Math.floor(Math.random() * smallPrimes.length)];
}


function generateKeys() {
    let p = getRandomPrime(); 
    let q;
    do {
        q = getRandomPrime();
    } while (q === p);

    n = p * q;
    const phi = (p - 1n) * (q - 1n);

    e = 101n;
    while (gcd(e, phi) !== 1n && e < 1000n) {
    e += 2n;
    }

    d = modInverse(e, phi);

    document.getElementById('private_key').value = d.toString();
    document.getElementById('public_key_e').value = e.toString();
    listKeyn = document.querySelectorAll('.public_key_n');
    for(let key of listKeyn){
      console.log(key)
      key.value = n.toString();
    }
}

function simpleHash(content, nLimit) {
  let hash = 0n;
  for (let i = 0; i < content.length; i++) {
    hash = (hash + BigInt(content.charCodeAt(i))) % nLimit;
  }
  return hash;
}

function signFile() {
  const fileInput = document.getElementById('file');
  const file = fileInput.files[0];

  if (!file || !d || !n) {
    alert("Hãy tạo khóa và chọn file trước!");
    return;
  }

  const reader = new FileReader();
  reader.onload = function (eFile) {
    const content = new TextDecoder().decode(new Uint8Array(eFile.target.result));
    const hash = simpleHash(content, n);
    const signature = hash ** d % n;

    document.getElementById('signature_result').innerHTML = `<p style="color: green;">✅ Tạo chữ ký thành công.</p>`;

    // Tạo file chữ ký
    const blob = new Blob([signature.toString()], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);

    const a = document.createElement('a');
    a.href = url;
    a.download = file.name + ".sig";
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  };

  reader.readAsArrayBuffer(file);
}



function verifySignature() {
  const fileInput = document.getElementById('originalFile').files[0];
  const sigInput = document.getElementById('signatureFile').files[0];
  const eText = document.getElementById('eInput').value;
  const nText = document.getElementById('nInput').value;

  if (!fileInput || !sigInput || !eText || !nText) {
    alert("Vui lòng chọn file, chữ ký và nhập khóa công khai!");
    return;
  }

  const e = BigInt(eText);
  const n = BigInt(nText);

  const fileReader = new FileReader();
  const sigReader = new FileReader();

  fileReader.onload = function (fileEvent) {
    const content = new TextDecoder().decode(new Uint8Array(fileEvent.target.result));
    const hash = simpleHash(content, n);

    sigReader.onload = function (sigEvent) {
      const signature = BigInt(sigEvent.target.result.trim());
      const decryptedHash = signature ** e % n;

      let result = '';
      if (decryptedHash === hash) {
        result = `<p style="color: green;">✅ Chữ ký hợp lệ.</p>`;
      } else {
        result = `<p style="color: red;">❌ Chữ ký KHÔNG hợp lệ.</p>`;
      }
      document.getElementById('verify_result').innerHTML = result;
    };

    sigReader.readAsText(sigInput);
  };

  fileReader.readAsArrayBuffer(fileInput);
}
