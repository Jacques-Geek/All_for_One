<?php
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<style>
  body {
    background: #0f172a;
    color: #f3f4f6;
  }

  .voiceoff-shell {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px 18px 40px;
  }

  .voice-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(250px, 1fr));
    gap: 14px 16px;
    margin-bottom: 24px;
  }

  .voice-card {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(148, 163, 184, 0.22);
    border-radius: 16px;
    padding: 14px 14px 12px;
    min-height: 120px;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: inset 0 0 0 1px rgba(255,255,255,0.02);
  }

  .voice-card.selected {
    border-color: rgba(251, 146, 60, 0.9);
    background: rgba(251, 146, 60, 0.08);
    box-shadow: 0 0 0 1px rgba(251, 146, 60, 0.6);
  }

  .voice-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 8px;
  }

  .voice-name {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 800;
    color: #f9fafb;
  }

  .voice-gender {
    display: block;
    margin-top: 4px;
    font-size: 0.68rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #cbd5e1;
    opacity: 0.82;
  }

  .voice-check {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: rgba(251, 146, 60, 0.15);
    border: 1px solid rgba(251, 146, 60, 0.9);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #f59e0b;
    font-weight: 700;
    font-size: 0.8rem;
  }

  .voice-card:not(.selected) .voice-check {
    display: none;
  }

  .voice-desc {
    margin: 0;
    color: #dbe2ec;
    font-size: 0.88rem;
    line-height: 1.48;
    min-height: 40px;
  }

  .voice-action {
    margin-top: 12px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: transparent;
    border: none;
    color: #f8fafc;
    font-size: 0.98rem;
    font-weight: 700;
    padding: 0;
    cursor: pointer;
  }

  .play-icon {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: rgba(59, 130, 246, 0.12);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #60a5fa;
    font-size: 0.75rem;
  }

  .voice-generator {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 16px;
    padding: 18px 18px 16px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.12);
  }

  .voice-generator h2 {
    margin: 0 0 8px;
    font-size: 1.12rem;
    color: #f4f4f5;
  }

  .voice-generator p {
    margin: 0 0 14px;
    color: #cbd5e1;
    font-size: 0.97rem;
  }

  .voice-generator textarea {
    width: 100%;
    min-height: 150px;
    resize: vertical;
    border: 1px solid rgba(148, 163, 184, 0.22);
    border-radius: 12px;
    background: rgba(15, 23, 42, 0.45);
    color: #f8fafc;
    padding: 16px 18px;
    font-size: 1rem;
    line-height: 1.6;
  }

  .voice-generator textarea:focus {
    outline: none;
    border-color: rgba(96, 165, 250, 0.8);
    box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1);
  }

  .record-row,
  .action-row {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 14px;
  }

  .record-button,
  .action-button,
  .secondary-button {
    border: none;
    border-radius: 10px;
    padding: 11px 14px;
    font-weight: 700;
    cursor: pointer;
  }

  .record-button,
  .action-button {
    background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
    color: white;
  }

  .secondary-button {
    background: rgba(148, 163, 184, 0.12);
    color: #e2e8f0;
    border: 1px solid rgba(148, 163, 184, 0.18);
  }

  .voice-status {
    margin-top: 12px;
    min-height: 22px;
    color: #e2e8f0;
    font-size: 0.9rem;
  }

  audio {
    width: 100%;
    margin-top: 12px;
    display: none;
  }

  @media (max-width: 768px) {
    .voice-grid {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="content voiceoff-shell">
  <div class="voice-grid" id="voiceGrid">
    <div class="voice-card selected" data-voice="Zéphyr">
      <div class="voice-top">
        <div>
          <h3 class="voice-name">Zéphyr</h3>
          <span class="voice-gender">FEMME</span>
        </div>
        <span class="voice-check">✓</span>
      </div>
      <p class="voice-desc">Voix féminine lumineuse</p>
      <button type="button" class="voice-action"><span class="play-icon">▶</span> Écouter</button>
    </div>

    <div class="voice-card" data-voice="Puck">
      <div class="voice-top">
        <div>
          <h3 class="voice-name">Puck</h3>
          <span class="voice-gender">HOMME</span>
        </div>
        <span class="voice-check">✓</span>
      </div>
      <p class="voice-desc">Voix masculine dynamique</p>
      <button type="button" class="voice-action"><span class="play-icon">▶</span> Écouter</button>
    </div>

    <div class="voice-card" data-voice="Charon">
      <div class="voice-top">
        <div>
          <h3 class="voice-name">Charon</h3>
          <span class="voice-gender">HOMME</span>
        </div>
        <span class="voice-check">✓</span>
      </div>
      <p class="voice-desc">Voix masculine informative, posée</p>
      <button type="button" class="voice-action"><span class="play-icon">▶</span> Écouter</button>
    </div>

    <div class="voice-card" data-voice="Kore">
      <div class="voice-top">
        <div>
          <h3 class="voice-name">Kore</h3>
          <span class="voice-gender">FEMME</span>
        </div>
        <span class="voice-check">✓</span>
      </div>
      <p class="voice-desc">Voix féminine ferme et assurée</p>
      <button type="button" class="voice-action"><span class="play-icon">▶</span> Écouter</button>
    </div>

    <div class="voice-card" data-voice="Fenrir">
      <div class="voice-top">
        <div>
          <h3 class="voice-name">Fenrir</h3>
          <span class="voice-gender">HOMME</span>
        </div>
        <span class="voice-check">✓</span>
      </div>
      <p class="voice-desc">Voix masculine expressive, énergique</p>
      <button type="button" class="voice-action"><span class="play-icon">▶</span> Écouter</button>
    </div>

    <div class="voice-card" data-voice="Leda">
      <div class="voice-top">
        <div>
          <h3 class="voice-name">Leda</h3>
          <span class="voice-gender">FEMME</span>
        </div>
        <span class="voice-check">✓</span>
      </div>
      <p class="voice-desc">Voix féminine douce et naturelle</p>
      <button type="button" class="voice-action"><span class="play-icon">▶</span> Écouter</button>
    </div>

    <div class="voice-card" data-voice="Orus">
      <div class="voice-top">
        <div>
          <h3 class="voice-name">Orus</h3>
          <span class="voice-gender">HOMME</span>
        </div>
        <span class="voice-check">✓</span>
      </div>
      <p class="voice-desc">Voix masculine ferme et rassurante</p>
      <button type="button" class="voice-action"><span class="play-icon">▶</span> Écouter</button>
    </div>

    <div class="voice-card" data-voice="Aoede">
      <div class="voice-top">
        <div>
          <h3 class="voice-name">Aoede</h3>
          <span class="voice-gender">FEMME</span>
        </div>
        <span class="voice-check">✓</span>
      </div>
      <p class="voice-desc">Voix féminine légère et détendue</p>
      <button type="button" class="voice-action"><span class="play-icon">▶</span> Écouter</button>
    </div>
  </div>

  <div class="voice-generator">
    <h2>Votre texte</h2>
    <p>Rédigez votre message, enregistrez votre voix puis générez la lecture.</p>

    <textarea id="voiceText">Exemple : Bienvenue chez Christ-ROI
VO ! Votre message, lu par une voix professionnelle, prêt en quelques secondes...</textarea>

    <div class="record-row">
      <button type="button" class="record-button" id="startRecordBtn">🎙️ Enregistrer ma voix (30s)</button>
      <button type="button" class="secondary-button" id="stopRecordBtn">⏹ Arrêter</button>
      <button type="button" class="secondary-button" id="playRecordedBtn">▶ Écouter l'enregistrement</button>
    </div>

    <audio id="recordedAudio" controls></audio>
    <div id="voiceStatus" class="voice-status">Aucune voix enregistrée pour le moment.</div>

    <div class="action-row">
      <button type="button" class="action-button" id="generateVoiceBtn">🔊 Générer la lecture</button>
      <button type="button" class="secondary-button" id="pauseBtn">⏸ Pause</button>
      <button type="button" class="secondary-button" id="resumeBtn">⏵ Reprendre</button>
      <button type="button" class="secondary-button" id="stopSpeechBtn">⏹ Stop</button>
    </div>
  </div>
</div>

<script>
  const cards = document.querySelectorAll('.voice-card');
  const textArea = document.getElementById('voiceText');
  const generateBtn = document.getElementById('generateVoiceBtn');
  const startRecordBtn = document.getElementById('startRecordBtn');
  const stopRecordBtn = document.getElementById('stopRecordBtn');
  const playRecordedBtn = document.getElementById('playRecordedBtn');
  const recordedAudio = document.getElementById('recordedAudio');
  const voiceStatus = document.getElementById('voiceStatus');
  const pauseBtn = document.getElementById('pauseBtn');
  const resumeBtn = document.getElementById('resumeBtn');
  const stopSpeechBtn = document.getElementById('stopSpeechBtn');

  let selectedVoice = 'Zéphyr';
  let mediaRecorder = null;
  let recordedStream = null;
  let recordedBlob = null;
  let recordedUrl = '';

  cards.forEach((card) => {
    card.addEventListener('click', () => {
      cards.forEach(item => item.classList.remove('selected'));
      card.classList.add('selected');
      selectedVoice = card.dataset.voice;
      voiceStatus.textContent = 'Voix sélectionnée : ' + selectedVoice;
    });
  });

  function getAvailableVoices() {
    return speechSynthesis.getVoices();
  }

  function loadVoices() {
    const voices = getAvailableVoices();
    if (voices.length === 0) {
      voiceStatus.textContent = 'Le navigateur charge encore les voix disponibles...';
    }
  }

  speechSynthesis.onvoiceschanged = loadVoices;
  loadVoices();

  function chooseVoice() {
    const voices = getAvailableVoices();
    const target = selectedVoice.toLowerCase();
    const match = voices.find(v => v.name.toLowerCase().includes(target)) || voices.find(v => v.lang.toLowerCase().startsWith('fr')) || voices[0];
    return match || null;
  }

  function generateVoice() {
    const text = textArea.value.trim();
    if (!text) {
      alert('Veuillez écrire le texte à lire.');
      return;
    }

    if (!('speechSynthesis' in window)) {
      alert('La synthèse vocale n\'est pas prise en charge par ce navigateur.');
      return;
    }

    const voices = getAvailableVoices();
    if (voices.length === 0) {
      alert('Les voix du navigateur ne sont pas encore prêtes. Réessayez dans quelques secondes.');
      return;
    }

    speechSynthesis.cancel();
    const utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = 'fr-FR';
    utterance.rate = 1;
    utterance.pitch = 1;
    utterance.volume = 1;

    const selected = chooseVoice();
    if (selected) utterance.voice = selected;

    speechSynthesis.speak(utterance);
    voiceStatus.textContent = 'Lecture en cours avec la voix sélectionnée.';
  }

  async function startRecording() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      alert('L’enregistrement audio n\'est pas supporté par ce navigateur.');
      return;
    }

    try {
      const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
      recordedStream = stream;
      mediaRecorder = new MediaRecorder(stream);
      const chunks = [];

      mediaRecorder.ondataavailable = (event) => {
        if (event.data.size > 0) chunks.push(event.data);
      };

      mediaRecorder.onstop = () => {
        recordedBlob = new Blob(chunks, { type: 'audio/webm' });
        if (recordedUrl) URL.revokeObjectURL(recordedUrl);
        recordedUrl = URL.createObjectURL(recordedBlob);
        recordedAudio.src = recordedUrl;
        recordedAudio.style.display = 'block';
        voiceStatus.textContent = 'Enregistrement terminé. Vous pouvez l’écouter ou générer une lecture.';
        recordedStream.getTracks().forEach(track => track.stop());
      };

      mediaRecorder.start();
      voiceStatus.textContent = 'Enregistrement en cours... Parlez pendant 30 secondes.';

      setTimeout(() => {
        if (mediaRecorder && mediaRecorder.state === 'recording') {
          mediaRecorder.stop();
        }
      }, 30000);
    } catch (error) {
      alert('L’accès au microphone a été refusé. Autorisez l’accès et réessayez.');
      console.error(error);
    }
  }

  function stopRecording() {
    if (mediaRecorder && mediaRecorder.state === 'recording') {
      mediaRecorder.stop();
    }
  }

  function playRecordedAudio() {
    if (!recordedUrl) {
      alert('Aucun enregistrement vocal disponible.');
      return;
    }
    recordedAudio.play();
    voiceStatus.textContent = 'Lecture de votre enregistrement vocal.';
  }

  generateBtn.addEventListener('click', generateVoice);
  startRecordBtn.addEventListener('click', startRecording);
  stopRecordBtn.addEventListener('click', stopRecording);
  playRecordedBtn.addEventListener('click', playRecordedAudio);

  pauseBtn.addEventListener('click', () => {
    if (speechSynthesis.speaking && !speechSynthesis.paused) speechSynthesis.pause();
  });

  resumeBtn.addEventListener('click', () => {
    if (speechSynthesis.paused) speechSynthesis.resume();
  });

  stopSpeechBtn.addEventListener('click', () => {
    if (speechSynthesis.speaking) speechSynthesis.cancel();
  });
</script>

</body>
</html>
