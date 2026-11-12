// ---------- Variables ----------
// The word is NOT here. It stays on the server.
var shownWord = "";        // like "m _ n _ o ", sent by the server
var wrongCount = 0;
var roundPoints = 0;
var roundActive = false;

var MAX_PICTURE_LENGTH = 100000;   // picture limit: 100,000 characters of Base64 text

// ---------- Cookie helpers (only for the last level) ----------
function setCookie(name, value, days) {
  var date = new Date();
  date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
  document.cookie = name + "=" + encodeURIComponent(value) +
    "; expires=" + date.toUTCString() + "; path=/";
}

function getCookie(name) {
  var parts = document.cookie.split("; ");
  for (var i = 0; i < parts.length; i++) {
    var pair = parts[i].split("=");
    if (pair[0] === name) {
      return decodeURIComponent(pair[1]);
    }
  }
  return "";
}

// ---------- Small screen helpers ----------
function showMessage(text) {
  document.getElementById("message").textContent = text;
}

function applyTheme(theme) {
  document.body.className = "theme-" + theme;
}

// ---------- Talking to the server ----------
// GET: sends a request and returns the answer as an object. Returns null if there is a problem.
async function askServer(route, extra) {
  var address = route + ".php" + (extra || "");
  try {
    var response = await fetch(address);
    var data = await response.json();
    if (!response.ok) {
      showMessage(data.error || "The server had a problem.");
      return null;
    }
    return data;
  } catch (error) {
    showMessage("Could not reach the server. Is it running?");
    return null;
  }
}

// POST: sends form values (for the theme and the picture).
async function postToServer(route, formData) {
  try {
    var response = await fetch(route + ".php", { method: "POST", body: formData });
    var data = await response.json();
    if (!response.ok) {
      showMessage(data.error || "The server had a problem.");
      return null;
    }
    return data;
  } catch (error) {
    showMessage("Could not reach the server. Is it running?");
    return null;
  }
}

// ---------- Theme and picture ----------
async function themeChanged() {
  var theme = document.getElementById("theme").value;
  var formData = new FormData();
  formData.append("theme", theme);
  var data = await postToServer("set_theme", formData);
  if (data !== null) {
    applyTheme(theme);
    showMessage("Theme saved.");
  }
}

function pictureChosen() {
  var input = document.getElementById("pictureInput");
  var file = input.files[0];
  if (!file) {
    return;
  }
  if (file.type.indexOf("image/") !== 0) {
    showMessage("Please choose a picture file.");
    input.value = "";
    return;
  }

  var reader = new FileReader();
  reader.onload = async function () {
    var dataUrl = reader.result;   // text like data:image/jpeg;base64,...
    if (dataUrl.length > MAX_PICTURE_LENGTH) {
      showMessage("That picture is too big (" + dataUrl.length +
        " characters, limit is " + MAX_PICTURE_LENGTH + "). Please choose a smaller picture.");
      input.value = "";
      return;
    }
    var formData = new FormData();
    formData.append("picture", dataUrl);
    var data = await postToServer("set_picture", formData);
    if (data !== null) {
      var avatar = document.getElementById("avatar");
      avatar.src = dataUrl;
      avatar.hidden = false;
      showMessage("Profile picture saved.");
    }
  };
  reader.onerror = function () {
    showMessage("Could not read that file.");
  };
  reader.readAsDataURL(file);
}

// ---------- The game ----------
function resetRoundScreen() {
  shownWord = "";
  wrongCount = 0;
  roundPoints = 0;
  roundActive = false;
  document.getElementById("hintText").textContent = "";
  document.getElementById("letters").textContent = "";
  updateScreen();
  updateButtons();
}

function updateButtons() {
  document.getElementById("startButton").disabled = roundActive;
  document.getElementById("level").disabled = roundActive;
  document.getElementById("endButton").disabled = !roundActive;
  document.getElementById("hintButton").disabled = !roundActive;
}

// Shows what the server told us
function updateScreen() {
  document.getElementById("wordDisplay").textContent = shownWord;
  document.getElementById("wrongCount").textContent = wrongCount;
  document.getElementById("roundPoints").textContent = roundPoints;
  document.getElementById("hangmanImage").src =
    "images/hangman_stage_" + (wrongCount + 1) + ".png";
}

function takeState(data) {
  shownWord = data.shown;
  wrongCount = data.wrong;
  roundPoints = data.points;
  updateScreen();
}

async function startClicked() {
  var level = document.getElementById("level").value;
  setCookie("lastLevel", level, 30);
  showMessage("Starting...");

  var data = await askServer("start", "?level=" + level);
  if (data === null) {
    return;
  }

  roundActive = true;
  document.getElementById("hintText").textContent = "";
  makeLetterButtons();
  takeState(data);
  updateButtons();
  showMessage("Round started. Good luck!");
}

function makeLetterButtons() {
  var box = document.getElementById("letters");
  box.textContent = "";
  var alphabet = "abcdefghijklmnopqrstuvwxyz";
  for (var i = 0; i < alphabet.length; i++) {
    var button = document.createElement("button");
    button.textContent = alphabet[i];
    button.setAttribute("data-letter", alphabet[i]);
    button.addEventListener("click", letterClicked);
    box.appendChild(button);
  }
}

function disableLetterButtons() {
  var buttons = document.querySelectorAll("#letters button");
  for (var i = 0; i < buttons.length; i++) {
    buttons[i].disabled = true;
  }
}

async function letterClicked(event) {
  if (!roundActive) {
    return;
  }
  var button = event.target;
  var letter = button.getAttribute("data-letter");
  button.disabled = true;

  var data = await askServer("guess", "?letter=" + letter);
  if (data === null) {
    return;
  }
  takeState(data);
  if (data.over) {
    finishRound(data);
  }
}

async function hintClicked() {
  if (!roundActive) {
    return;
  }
  var data = await askServer("hint");
  if (data === null) {
    return;
  }
  document.getElementById("hintText").textContent = "Hint: " + data.hint;
  takeState(data);
}

async function endClicked() {
  if (!roundActive) {
    return;
  }
  var data = await askServer("end");
  if (data === null) {
    return;
  }
  takeState(data);
  finishRound(data);
}

// data.result is "won", "lost" or "ended". The server already saved the result.
function finishRound(data) {
  roundActive = false;
  disableLetterButtons();
  updateButtons();

  if (data.result === "won") {
    showMessage("You won! You got " + data.points + " points. See the Leaderboard.");
  } else if (data.result === "lost") {
    showMessage("You lost. The word was " + data.word + ". You still got " + data.points + " points.");
  } else {
    showMessage("Round ended. The word was " + data.word + ". You got " + data.points + " points, but it is not a win.");
  }
}

// ---------- Events ----------
document.getElementById("theme").addEventListener("change", themeChanged);
document.getElementById("pictureInput").addEventListener("change", pictureChosen);
document.getElementById("startButton").addEventListener("click", startClicked);
document.getElementById("endButton").addEventListener("click", endClicked);
document.getElementById("hintButton").addEventListener("click", hintClicked);

// ---------- When the page opens ----------
var lastLevel = getCookie("lastLevel");
if (lastLevel === "easy" || lastLevel === "normal") {
  document.getElementById("level").value = lastLevel;
}
resetRoundScreen();
