function toggleChat() {
  const chatContainer = document.getElementById("chatContainer");
  const isVisible = chatContainer.style.display === "flex";
  chatContainer.style.display = isVisible ? "none" : "flex";

  // Optional: Focus input when opening
  if (!isVisible) {
    setTimeout(() => document.getElementById("userInput").focus(), 100);
  }
}

function handleOption(optionText) {
  document.getElementById("userInput").value = optionText;
  sendMessage();
}

function handleEnter(event) {
  if (event.key === "Enter") {
    sendMessage();
  }
}

async function sendMessage() {
  const inputField = document.getElementById("userInput");
  const message = inputField.value.trim();
  const chatBody = document.getElementById("chatBody");

  if (message === "") return;

  // 1. Display User Message
  const time = new Date().toLocaleTimeString([], {
    hour: "2-digit",
    minute: "2-digit",
  });
  const userMsgHTML = `
                <div class="message user">
                    <p>${message}</p>
                    <span class="time">${time}</span>
                </div>
            `;
  chatBody.insertAdjacentHTML("beforeend", userMsgHTML);

  // Clear input and scroll
  inputField.value = "";
  chatBody.scrollTop = chatBody.scrollHeight;

  // 2. Show Typing Indicator
  const typingId = "typing-" + Date.now();
  const typingHTML = `<div class="typing-indicator" id="${typingId}">SalikTech is typing...</div>`;
  chatBody.insertAdjacentHTML("beforeend", typingHTML);
  chatBody.scrollTop = chatBody.scrollHeight;

  try {
    // 3. Send to PHP Backend (OpenAI)
    const response = await fetch("chatbot.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ message: message }),
    });

    const data = await response.json();

    // Remove typing indicator
    const typingElement = document.getElementById(typingId);
    if (typingElement) typingElement.remove();

    // 4. Display Bot Response
    const botMsgHTML = `
                    <div class="message bot">
                        <p>${formatBotResponse(data.reply)}</p>
                        <span class="time">${time}</span>
                    </div>
                `;
    chatBody.insertAdjacentHTML("beforeend", botMsgHTML);
  } catch (error) {
    // Error handling
    const typingElement = document.getElementById(typingId);
    if (typingElement) typingElement.remove();

    const errorHTML = `
                    <div class="message bot">
                        <p style="color:red;">Error: Unable to reach SalikTech server.</p>
                    </div>`;
    chatBody.insertAdjacentHTML("beforeend", errorHTML);
  }

  chatBody.scrollTop = chatBody.scrollHeight;
}

// Helper to format line breaks from AI
function formatBotResponse(text) {
  return text.replace(/\n/g, "<br>");
}
