<?php
// PHP only serves the page, all timer functionality in JS for smooth UX.
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Pomodoro Timer | Aesthetic UI</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap');

  body, html {
    height: 100%;
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background:
      linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .container {
    background: rgba(255,255,255,0.1);
    padding: 3rem 2rem;
    border-radius: 20px;
    box-shadow:
      0 20px 60px rgba(118,75,162,0.4),
      0 12px 20px rgba(102,126,234,0.3);
    width: 360px;
    max-width: 95vw;
  }

  h1 {
    font-weight: 700;
    font-size: 2.4rem;
    margin-bottom: 1.5rem;
    text-align: center;
    text-shadow: 0 2px 8px rgba(0,0,0,0.3);
  }

  .inputs {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-bottom: 2rem;
  }

  .inputs label {
    display: block;
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 0.3rem;
    color: #e0d7f5;
    text-align: center;
  }

  .inputs input[type=number] {
    width: 80px;
    padding: 0.4rem 0.6rem;
    border-radius: 10px;
    border: none;
    font-size: 1.1rem;
    text-align: center;
    font-weight: 600;
    box-shadow: inset 0 0 6px rgba(0,0,0,0.2);
    outline: none;
  }

  .timer {
    font-weight: 700;
    font-size: 5.5rem;
    letter-spacing: 2px;
    text-align: center;
    user-select: none;
    margin-bottom: 1.5rem;
    text-shadow: 0 3px 10px rgba(0,0,0,0.4);
  }

  .mode {
    font-weight: 600;
    font-size: 1.3rem;
    text-align: center;
    margin-bottom: 2rem;
    letter-spacing: 1px;
    color: #d4c1f9;
    text-shadow: 0 2px 6px rgba(0,0,0,0.25);
  }

  .buttons {
    display: flex;
    gap: 12px;
    justify-content: center;
  }

  .btn-custom {
    border-radius: 50px;
    width: 80px;
    height: 80px;
    font-weight: 700;
    font-size: 1.1rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
    transition: background-color 0.3s ease, transform 0.2s ease;
    user-select: none;
  }
  .btn-custom:hover:not(:disabled) {
    transform: scale(1.15);
  }
  .btn-start {
    background: #7b5cff;
    color: white;
  }
  .btn-pause {
    background: #ff6a88;
    color: white;
  }
  .btn-reset {
    background: #6ee7b7;
    color: #064e3b;
  }
  .btn-custom:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
  }

  /* Responsive */
  @media (max-width: 400px) {
    .timer {
      font-size: 4.2rem;
    }
    .btn-custom {
      width: 70px;
      height: 70px;
      font-size: 1rem;
    }
  }
</style>
</head>
<body>

<div class="container shadow-lg">
  <h1>Pomodoro Timer</h1>

  <div class="inputs">
    <div>
      <label for="work-input">Work (min)</label>
      <input type="number" id="work-input" min="1" max="60" value="25" aria-label="Work duration in minutes" />
    </div>
    <div>
      <label for="break-input">Break (min)</label>
      <input type="number" id="break-input" min="1" max="30" value="5" aria-label="Break duration in minutes" />
    </div>
  </div>

  <div class="timer" id="timer">25:00</div>
  <div class="mode" id="mode-indicator">Work</div>

  <div class="buttons">
    <button class="btn btn-start btn-custom" id="start-btn" aria-label="Start timer">Start</button>
    <button class="btn btn-pause btn-custom" id="pause-btn" disabled aria-label="Pause timer">Pause</button>
    <button class="btn btn-reset btn-custom" id="reset-btn" disabled aria-label="Reset timer">Reset</button>
  </div>

  <audio id="alarm" preload="auto" src="https://actions.google.com/sounds/v1/alarms/alarm_clock.ogg"></audio>
</div>

<script>
(() => {
  const workInput = document.getElementById('work-input');
  const breakInput = document.getElementById('break-input');
  const timerEl = document.getElementById('timer');
  const modeEl = document.getElementById('mode-indicator');
  const startBtn = document.getElementById('start-btn');
  const pauseBtn = document.getElementById('pause-btn');
  const resetBtn = document.getElementById('reset-btn');
  const alarm = document.getElementById('alarm');

  let intervalId = null;
  let isRunning = false;
  let isWork = true;
  let totalSeconds = parseInt(workInput.value, 10) * 60;
  let remainingSeconds = totalSeconds;

  function formatTime(seconds) {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${String(mins).padStart(2,'0')}:${String(secs).padStart(2,'0')}`;
  }

  function updateTimerDisplay() {
    timerEl.textContent = formatTime(remainingSeconds);
  }

  function updateModeDisplay() {
    modeEl.textContent = isWork ? 'Work' : 'Break';
    modeEl.style.color = isWork ? '#b57aff' : '#77dd77';
  }

  function resetTimer() {
    clearInterval(intervalId);
    intervalId = null;
    isRunning = false;
    isWork = true;
    totalSeconds = parseInt(workInput.value, 10) * 60;
    remainingSeconds = totalSeconds;
    updateTimerDisplay();
    updateModeDisplay();
    startBtn.disabled = false;
    pauseBtn.disabled = true;
    resetBtn.disabled = true;
  }

  function tick() {
    if (remainingSeconds > 0) {
      remainingSeconds--;
      updateTimerDisplay();
    } else {
      alarm.play();
      isWork = !isWork;
      totalSeconds = isWork ? parseInt(workInput.value, 10) * 60 : parseInt(breakInput.value, 10) * 60;
      remainingSeconds = totalSeconds;
      updateModeDisplay();
      updateTimerDisplay();
    }
  }

  startBtn.addEventListener('click', () => {
    if (isRunning) return;
    isRunning = true;
    intervalId = setInterval(tick, 1000);
    startBtn.disabled = true;
    pauseBtn.disabled = false;
    resetBtn.disabled = false;
  });

  pauseBtn.addEventListener('click', () => {
    if (!isRunning) return;
    isRunning = false;
    clearInterval(intervalId);
    intervalId = null;
    startBtn.disabled = false;
    pauseBtn.disabled = true;
  });

  resetBtn.addEventListener('click', () => {
    resetTimer();
  });

  // Update timer when inputs change but only if timer not running
  workInput.addEventListener('change', () => {
    if (!isRunning) {
      totalSeconds = parseInt(workInput.value, 10) * 60;
      if (isWork) remainingSeconds = totalSeconds;
      updateTimerDisplay();
    }
  });
  breakInput.addEventListener('change', () => {
    if (!isRunning && !isWork) {
      totalSeconds = parseInt(breakInput.value, 10) * 60;
      if (!isWork) remainingSeconds = totalSeconds;
      updateTimerDisplay();
    }
  });

  // Setup initial states
  resetTimer();
})();
</script>

</body>
</html>