<?php
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="content">
  <h1>📱 Générateur de QR Code</h1>

  <div class="form-box" style="max-width:500px;">
    <label for="qrText">Texte ou URL à encoder :</label>
    <?php
      $default = 'http://localhost/All_for_one/extract.php';
      $value = !empty($_REQUEST['text']) ? htmlspecialchars($_REQUEST['text']) : $default;
    ?>
    <input type="text" id="qrText" value="<?php echo $value; ?>">

    <div style="margin:20px 0; text-align:center;">
      <div id="qrcode" style="display:inline-block;"></div>
    </div>

    <div style="display:flex; gap:10px; justify-content:center;">
      <button id="generate">Générer</button>
      <button id="download" class="secondary">Télécharger</button>
      <button id="setExtract" class="secondary">Reset</button>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
let qr;
const qrContainer = document.getElementById('qrcode');

function makeQRCode(text) {
  qrContainer.innerHTML = '';
  qr = new QRCode(qrContainer, {
    text: text,
    width: 256,
    height: 256,
    colorDark: '#000000',
    colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.H
  });
}

makeQRCode(document.getElementById('qrText').value);

document.getElementById('generate').addEventListener('click', () => {
  const text = document.getElementById('qrText').value.trim();
  if (text) makeQRCode(text);
});

document.getElementById('setExtract').addEventListener('click', () => {
  const defaultUrl = '<?php echo $default; ?>';
  document.getElementById('qrText').value = defaultUrl;
  makeQRCode(defaultUrl);
});

document.getElementById('download').addEventListener('click', () => {
  const img = qrContainer.querySelector('img');
  const canvas = qrContainer.querySelector('canvas');
  
  if (img) {
    const a = document.createElement('a');
    a.href = img.src;
    a.download = 'qrcode.png';
    a.click();
  } else if (canvas) {
    const a = document.createElement('a');
    a.href = canvas.toDataURL('image/png');
    a.download = 'qrcode.png';
    a.click();
  }
});
</script>

</body>
</html>