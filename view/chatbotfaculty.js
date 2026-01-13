function toggleChat() {
  const chat = document.getElementById("chatContainer");
  chat.style.display = chat.style.display === "flex" ? "none" : "flex";
  if (chat.style.display === "flex") {
    document.getElementById("userInput").focus();
  }
}

// New function to handle clicking the buttons
function handleOption(text) {
  document.getElementById("userInput").value = text;
  sendMessage();
}

function handleEnter(e) {
  if (e.key === "Enter") sendMessage();
}

async function sendMessage() {
  const input = document.getElementById("userInput");
  const message = input.value.trim();
  const body = document.getElementById("chatBody");

  if (!message) return;

  // 1. Show User Message
  body.insertAdjacentHTML(
    "beforeend",
    `<div class="message user"><p>${message}</p></div>`
  );
  input.value = "";
  body.scrollTop = body.scrollHeight;

  // 2. Typing Indicator
  const typingId = "typing-" + Date.now();
  body.insertAdjacentHTML(
    "beforeend",
    `<div class="typing-indicator" id="${typingId}">Thinking...</div>`
  );
  body.scrollTop = body.scrollHeight;

  try {
    // 3. Send to PHP Backend
    const res = await fetch("chatbotfaculty.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ message: message }),
    });
    const data = await res.json();

    // 4. Show Response
    document.getElementById(typingId).remove();
    body.insertAdjacentHTML(
      "beforeend",
      `<div class="message bot"><p>${data.reply}</p></div>`
    );
  } catch (err) {
    document.getElementById(typingId).remove();
    body.insertAdjacentHTML(
      "beforeend",
      `<div class="message bot"><p style="color:red;">Connection error.</p></div>`
    );
  }
  body.scrollTop = body.scrollHeight;
}
