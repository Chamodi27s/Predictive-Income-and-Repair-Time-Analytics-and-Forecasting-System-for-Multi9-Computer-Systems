<style>
    /* --- Main Button --- */
    .chatbot-btn {
        position: fixed;
        bottom: 25px;
        right: 25px;
        width: 65px;
        height: 65px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%);
        color: white;
        font-size: 30px;
        border: none;
        cursor: pointer;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        z-index: 9999;
        transition: transform 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .chatbot-btn:hover {
        transform: scale(1.1) rotate(5deg);
    }

    /* --- Chat Container --- */
    .chat-container {
        position: fixed;
        bottom: 100px;
        right: 25px;
        width: 360px;
        height: 500px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        display: none;
        flex-direction: column;
        overflow: hidden;
        z-index: 9999;
        border: 1px solid #e2e8f0;
        animation: slideUp 0.4s ease-out;
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .chat-header {
        background: #0f766e;
        color: white;
        padding: 16px;
        text-align: center;
        font-weight: 700;
        font-size: 16px;
        letter-spacing: 0.5px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .chat-body {
        flex: 1;
        padding: 15px;
        overflow-y: auto;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    /* --- Message Bubbles --- */
    .msg {
        padding: 12px 16px;
        border-radius: 18px;
        font-size: 14px;
        line-height: 1.5;
        max-width: 85%;
        word-wrap: break-word;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    .user {
        background: #0f766e;
        color: white;
        align-self: flex-end;
        border-bottom-right-radius: 4px;
    }

    .bot {
        background: white;
        color: #1e293b;
        align-self: flex-start;
        border-bottom-left-radius: 4px;
        border: 1px solid #e2e8f0;
    }

    /* --- Input Area --- */
    .chat-input {
        display: flex;
        padding: 12px;
        background: white;
        border-top: 1px solid #eee;
    }

    .chat-input input {
        flex: 1;
        padding: 10px 15px;
        border: 1px solid #e2e8f0;
        border-radius: 25px;
        outline: none;
        font-size: 14px;
        transition: border 0.3s;
    }

    .chat-input input:focus {
        border-color: #0f766e;
    }

    .chat-input button {
        background: #0f766e;
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin-left: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.3s;
    }

    .chat-input button:hover {
        background: #0d9488;
    }

    /* --- DARK MODE ADAPTATION --- */
    body.dark-mode .chat-container {
        background: #1e293b;
        border-color: #334155;
    }

    body.dark-mode .chat-body {
        background: #0f172a;
    }

    body.dark-mode .bot {
        background: #1e293b;
        color: #f1f5f9;
        border-color: #334155;
    }

    body.dark-mode .chat-input {
        background: #1e293b;
        border-top-color: #334155;
    }

    body.dark-mode .chat-input input {
        background: #0f172a;
        color: white;
        border-color: #334155;
    }

</style>

<button class="chatbot-btn" onclick="toggleChat()" id="botBtn">🤖</button>

<div class="chat-container" id="chatBox">
    <div class="chat-header">
        <span>🤖 System Assistant</span>
        <span onclick="toggleChat()" style="cursor:pointer; font-size: 20px;">&times;</span>
    </div>

    <div class="chat-body" id="chatBody">
        <div class="msg bot">
            Hello! 👋 I'm your Smart Finance assistant. How can I help you today?
        </div>
    </div>

    <div class="chat-input">
        <input type="text" id="chatMsg" placeholder="Type a message..." onkeypress="handleKeyPress(event)">
        <button onclick="sendChat()">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
        </button>
    </div>
</div>

<script>
    function toggleChat() {
        const box = document.getElementById("chatBox");
        const btn = document.getElementById("botBtn");
        
        if (box.style.display === "flex") {
            box.style.display = "none";
            btn.innerHTML = "🤖";
        } else {
            box.style.display = "flex";
            btn.innerHTML = "✕";
            // Scroll to bottom when opening
            const chatBody = document.getElementById("chatBody");
            chatBody.scrollTop = chatBody.scrollHeight;
        }
    }

    function handleKeyPress(event) {
        if (event.key === "Enter") {
            sendChat();
        }
    }

    function sendChat() {
        const inputField = document.getElementById("chatMsg");
        const msg = inputField.value.trim();
        
        if (msg === "") return;

        const chatBody = document.getElementById("chatBody");

        // User Message
        chatBody.innerHTML += `<div class="msg user">${msg}</div>`;
        inputField.value = "";
        
        // Auto scroll to bottom
        chatBody.scrollTop = chatBody.scrollHeight;

        // Typing indicator (Optional look)
        const typingId = "typing-" + Date.now();
        chatBody.innerHTML += `<div class="msg bot" id="${typingId}">...</div>`;
        chatBody.scrollTop = chatBody.scrollHeight;

        // Fetch API
        fetch("chatbot_api.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "message=" + encodeURIComponent(msg)
        })
        .then(res => res.text())
        .then(reply => {
            const typingElem = document.getElementById(typingId);
            if(typingElem) typingElem.remove();
            
            chatBody.innerHTML += `<div class="msg bot">${reply}</div>`;
            chatBody.scrollTop = chatBody.scrollHeight;
        })
        .catch(err => {
            const typingElem = document.getElementById(typingId);
            if(typingElem) typingElem.innerHTML = "Sorry, I'm having trouble connecting.";
        });
    }
</script>