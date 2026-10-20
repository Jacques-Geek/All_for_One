<?php
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="content">
  <h1>🎙️ VoiceOff</h1>

  <div class="form-box" style="max-width:700px;">
    <label for="voiceText">Texte à lire :</label>
    <textarea id="voiceText" placeholder="Saisis un texte ici...">Bonjour, bienvenue sur All_For_One !</textarea>

    <div style="display:flex; gap:10px; flex-wrap:wrap; margin: 20px 0;">
      <button type="button" id="readBtn">🔊 Lire</button>
      <button type="button" id="pauseBtn" class="secondary">⏸ Pause</button>
      <button type="button" id="resumeBtn" class="secondary">▶ Reprendre</button>
      <button type="button" id="stopBtn" class="secondary">⏹ Stop</button>
    </div>

    <label for="transcript">Transcription vocale :</label>
    <textarea id="transcript" placeholder="La transcription apparaîtra ici..." readonly></textarea>

    <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:20px;">
      <button type="button" id="startRecordBtn">🎙️ Démarrer l'enregistrement</button>
      <button type="button" id="stopRecordBtn" class="secondary">🛑 Arrêter</button>
    </div>
  </div>
</div>

<script>
const voiceText = document.getElementById('voiceText');
const transcript = document.getElementById('transcript');
const readBtn = document.getElementById('readBtn');
const pauseBtn = document.getElementById('pauseBtn');
const resumeBtn = document.getElementById('resumeBtn');
const stopBtn = document.getElementById('stopBtn');
const startRecordBtn = document.getElementById('startRecordBtn');
const stopRecordBtn = document.getElementById('stopRecordBtn');

function speakText() {
  const text = voiceText.value.trim();
  if (!text) {
    alert('Veuillez entrer un texte à lire.');
    return;
  }

  if (speechSynthesis.speaking) {
    speechSynthesis.cancel();
  }

  const utterance = new SpeechSynthesisUtterance(text);
  utterance.lang = 'fr-FR';
  utterance.rate = 1;
  utterance.pitch = 1;
  utterance.volume = 1;
  speechSynthesis.speak(utterance);
}

readBtn.addEventListener('click', speakText);
pauseBtn.addEventListener('click', () => {
  if (speechSynthesis.speaking && !speechSynthesis.paused) {
    speechSynthesis.pause();
  }
});
resumeBtn.addEventListener('click', () => {
  if (speechSynthesis.paused) {
    speechSynthesis.resume();
  }
});
stopBtn.addEventListener('click', () => {
  if (speechSynthesis.speaking) {
    speechSynthesis.cancel();
  }
});

const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
let recognition = null;

if (SpeechRecognition) {
  recognition = new SpeechRecognition();
  recognition.lang = 'fr-FR';
  recognition.interimResults = false;
  recognition.continuous = false;

  recognition.onresult = (event) => {
    const result = event.results[0][0].transcript;
    transcript.value = result;
    voiceText.value = result;
  };

  recognition.onerror = () => {
    alert('Erreur lors de l\'enregistrement vocal.');
  };
}

startRecordBtn.addEventListener('click', () => {
  if (!recognition) {
    alert('La reconnaissance vocale n\'est pas prise en charge par ce navigateur.');
    return;
  }

  recognition.start();
  transcript.value = 'Enregistrement en cours...';
});

stopRecordBtn.addEventListener('click', () => {
  if (recognition && recognition.running) {
    recognition.stop();
  }
});
</script>

</body>
</html>
