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

  .voice-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 16px;
    color: #d1d5db;
    font-size: 0.9rem;
  }

  .credits {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
  }

  .credits .star {
    color: #f59e0b;
  }

  .generate-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    margin-top: 18px;
    border: none;
    border-radius: 12px;
    padding: 16px 18px;
    background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
    color: white;
    font-size: 1.05rem;
    font-weight: 800;
    cursor: pointer;
    box-shadow: 0 10px 20px rgba(249, 115, 22, 0.24);
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
    <p>Écrivez le texte à transformer en voix off.</p>

    <textarea id="voiceText">Exemple : Bienvenue chez Christ-ROI
VO ! Votre message, lu par une voix professionnelle, prêt en quelques secondes...</textarea>

    <div class="voice-footer">
      <div class="credits"><span class="star">★</span> 25 crédits par génération</div>
      <div>0 / 5000 caractères</div>
    </div>

    <button type="button" class="generate-btn" id="generateVoiceBtn">▶ Générer ma voix off</button>
  </div>
</div>

<script>
  const cards = document.querySelectorAll('.voice-card');
  const textArea = document.getElementById('voiceText');
  const generateBtn = document.getElementById('generateVoiceBtn');
  let selectedVoice = 'Zéphyr';

  cards.forEach((card) => {
    card.addEventListener('click', () => {
      cards.forEach(item => item.classList.remove('selected'));
      card.classList.add('selected');
      selectedVoice = card.dataset.voice;
    });
  });

  function generateVoice() {
    const text = textArea.value.trim();
    if (!text) {
      alert('Veuillez écrire un texte avant la génération.');
      return;
    }

    if (!('speechSynthesis' in window)) {
      alert('La synthèse vocale n\'est pas prise en charge par ce navigateur.');
      return;
    }

    speechSynthesis.cancel();
    const utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = 'fr-FR';
    utterance.rate = 1;
    utterance.pitch = 1;
    utterance.volume = 1;

    const voices = speechSynthesis.getVoices();
    const match = voices.find(v => v.name.toLowerCase().includes(selectedVoice.toLowerCase()));
    if (match) utterance.voice = match;

    speechSynthesis.speak(utterance);
  }

  generateBtn.addEventListener('click', generateVoice);
</script>

</body>
</html>
