<?php
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="content">
  <h1>🗣️ Lecteur Vocal Intelligent</h1>

  <div class="form-box">
    <label for="sourceText">Texte source :</label>
    <textarea id="sourceText" placeholder="Écris ton texte ici..."></textarea>

    <label for="translateLang">Traduire vers :</label>
    <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
      <label style="display:flex; align-items:center; gap:6px;">
        <input type="checkbox" id="translateEnable" checked> Activer traduction
      </label>
      <select id="translateLang">
        <option value="fr">Français</option>
        <option value="en">English</option>
        <option value="es">Español</option>
        <option value="fon">Fon (Bénin)</option>
        <option value="goun">Goun (Bénin)</option>
      </select>
      <button type="button" id="translateBtn">🔁 Traduire</button>
      <button type="button" id="translateSpeakBtn">🔊 Traduire et lire</button>
    </div>

    <label for="translatedText">Texte traduit :</label>
    <textarea id="translatedText" placeholder="Traduction..."></textarea>

    <label for="voice">Voix disponible :</label>
    <select id="voice"></select>

    <div>
      <label for="rate">Vitesse : <span id="rateVal">1</span></label>
      <input type="range" id="rate" min="0.5" max="2" value="1" step="0.1">

      <label for="pitch">Tonalité : <span id="pitchVal">1</span></label>
      <input type="range" id="pitch" min="0.5" max="2" value="1" step="0.1">

      <label for="volume">Volume : <span id="volumeVal">1</span></label>
      <input type="range" id="volume" min="0" max="1" value="1" step="0.1">
    </div>

    <div class="controls">
      <button type="button" onclick="speak()">▶ Lire</button>
      <button type="button" onclick="pauseSpeech()">⏸ Pause</button>
      <button type="button" onclick="resumeSpeech()">⏵ Reprendre</button>
      <button type="button" onclick="stopSpeech()">⏹ Stop</button>
      <button type="button" id="saveSettings">💾 Sauvegarder</button>
    </div>
  </div>
</div>

<script>
let voices = [];
const voiceSelect = document.getElementById("voice");
const rateInput = document.getElementById("rate");
const pitchInput = document.getElementById("pitch");
const volumeInput = document.getElementById("volume");
const rateVal = document.getElementById('rateVal');
const pitchVal = document.getElementById('pitchVal');
const volumeVal = document.getElementById('volumeVal');
const sourceText = document.getElementById('sourceText');
const translatedText = document.getElementById('translatedText');
const translateEnable = document.getElementById('translateEnable');
const translateLang = document.getElementById('translateLang');
const translateBtn = document.getElementById('translateBtn');
const translateSpeakBtn = document.getElementById('translateSpeakBtn');

function loadVoices() {
  voices = speechSynthesis.getVoices();
  voiceSelect.innerHTML = "";
  
  voices.forEach((voice, i) => {
    const option = document.createElement("option");
    option.value = i;
    option.textContent = `${voice.name} (${voice.lang})`;
    voiceSelect.appendChild(option);
  });
}

if (speechSynthesis.onvoiceschanged !== undefined) {
  speechSynthesis.onvoiceschanged = loadVoices;
}
loadVoices();

function speak() {
  const toRead = translatedText.value.trim() || sourceText.value.trim();
  if (!toRead) return alert('Veuillez entrer du texte à lire.');

  stopSpeech();
  const utterance = new SpeechSynthesisUtterance(toRead);
  
  if (voices[voiceSelect.value]) {
    utterance.voice = voices[voiceSelect.value];
  }
  
  utterance.rate = parseFloat(rateInput.value);
  utterance.pitch = parseFloat(pitchInput.value);
  utterance.volume = parseFloat(volumeInput.value);
  
  speechSynthesis.speak(utterance);
}

async function translateWithServer(doSpeak = false) {
  const text = sourceText.value.trim();
  if (!text) return alert('Veuillez entrer un texte à traduire.');
  if (!translateEnable.checked) return;
  
  const target = translateLang.value;
  
  if (target === 'fon' || target === 'goun') {
    const manual = prompt(`Traduction manuelle pour "${text}" en ${target}:`);
    if (manual) {
      translatedText.value = manual;
      if (doSpeak) speak();
    }
    return;
  }
  
  try {
    const res = await fetch('translate.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ text, target })
    });
    const data = await res.json();
    
    if (data.ok) {
      translatedText.value = data.translated;
      if (doSpeak) speak();
    } else {
      alert('Erreur de traduction: ' + (data.message || data.error));
    }
  } catch (e) {
    alert('Erreur de connexion au serveur');
  }
}

translateBtn.addEventListener('click', () => translateWithServer(false));
translateSpeakBtn.addEventListener('click', () => translateWithServer(true));

function pauseSpeech() { if (speechSynthesis.speaking && !speechSynthesis.paused) speechSynthesis.pause(); }
function resumeSpeech() { if (speechSynthesis.paused) speechSynthesis.resume(); }
function stopSpeech() { if (speechSynthesis.speaking) speechSynthesis.cancel(); }

rateInput.addEventListener('input', () => rateVal.textContent = rateInput.value);
pitchInput.addEventListener('input', () => pitchVal.textContent = pitchInput.value);
volumeInput.addEventListener('input', () => volumeVal.textContent = volumeInput.value);

document.getElementById('saveSettings').addEventListener('click', () => {
  const settings = {
    voiceIndex: voiceSelect.value,
    rate: rateInput.value,
    pitch: pitchInput.value,
    volume: volumeInput.value,
    sourceText: sourceText.value,
    translatedText: translatedText.value,
    translateEnabled: translateEnable.checked,
    translateLang: translateLang.value
  };
  localStorage.setItem('tts_settings', JSON.stringify(settings));
  alert('Réglages sauvegardés');
});

function restoreSettings() {
  try {
    const raw = localStorage.getItem('tts_settings');
    if (!raw) return;
    const s = JSON.parse(raw);
    if (s.voiceIndex && voices[s.voiceIndex]) voiceSelect.value = s.voiceIndex;
    if (s.rate) { rateInput.value = s.rate; rateVal.textContent = s.rate; }
    if (s.pitch) { pitchInput.value = s.pitch; pitchVal.textContent = s.pitch; }
    if (s.volume) { volumeInput.value = s.volume; volumeVal.textContent = s.volume; }
    if (s.sourceText) sourceText.value = s.sourceText;
    if (s.translatedText) translatedText.value = s.translatedText;
    if (s.translateEnabled !== undefined) translateEnable.checked = s.translateEnabled;
    if (s.translateLang) translateLang.value = s.translateLang;
  } catch (e) {
    console.warn('Erreur restauration', e);
  }
}

setTimeout(restoreSettings, 500);
</script>

</body>
</html>