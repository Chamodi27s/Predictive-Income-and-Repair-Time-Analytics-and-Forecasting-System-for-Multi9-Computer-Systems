<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    /* 1. Floating Robot Button - සුදු පැහැති අයිකනය සහ Animation සහිතව */
    .chat-bubble-btn {
        position: fixed;
        bottom: 25px;
        right: 25px;
        width: 65px;
        height: 65px;
        background: #107c62; /* Multi9 Dark Green */
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 10px 25px rgba(16, 124, 98, 0.4);
        z-index: 10000;
        animation: floatAnimation 3s ease-in-out infinite;
        transition: all 0.3s ease;
        border: 2px solid rgba(255, 255, 255, 0.1);
    }

    @keyframes floatAnimation {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
        100% { transform: translateY(0px); }
    }

    .chat-bubble-btn:hover { 
        transform: scale(1.1);
        background: #0d634f;
    }

    /* සුදු පැහැති අයිකනය සඳහා Style */
    .chat-bubble-btn i {
        color: #ffffff;
        font-size: 32px;
    }

    /* 2. AI Chat Window */
    .ai-chat-window {
        position: fixed;
        bottom: 100px;
        right: 25px;
        width: 375px;
        height: 600px;
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #e0e0e0;
        display: none; 
        flex-direction: column;
        z-index: 10000;
        overflow: hidden;
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        font-family: 'Inter', 'Segoe UI', sans-serif;
    }

    .chat-header {
        background: #107c62;
        padding: 15px 20px;
        color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 600;
    }

    .chat-video-area {
        width: 100%;
        height: 180px;
        background: #000;
        overflow: hidden;
    }

    .chat-messages {
        flex: 1;
        padding: 15px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 12px;
        background: #f9fafb;
    }

    .msg {
        padding: 12px 16px;
        border-radius: 18px;
        max-width: 82%;
        font-size: 0.9rem;
        line-height: 1.5;
    }

    .bot { 
        background: #ffffff; 
        color: #374151; 
        align-self: flex-start; 
        border-bottom-left-radius: 4px; 
        border: 1px solid #e5e7eb;
    }
    
    .user { 
        background: #107c62; 
        color: #fff; 
        align-self: flex-end; 
        border-bottom-right-radius: 4px; 
    }

    /* 3. Improved Input & Send Button */
    .chat-input {
        padding: 15px;
        border-top: 1px solid #f3f4f6;
        display: flex;
        gap: 10px;
        background: #fff;
        align-items: center;
    }

    .chat-input input {
        flex: 1;
        background: #f3f4f6;
        border: 1px solid transparent;
        padding: 12px 15px;
        border-radius: 12px;
        outline: none;
        font-size: 0.95rem;
        transition: all 0.2s;
    }

    .chat-input input:focus {
        background: #fff;
        border-color: #107c62;
    }

    .send-btn {
        background: #107c62;
        border: none;
        width: 48px;
        height: 48px;
        border-radius: 12px;
        cursor: pointer;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .send-btn:hover {
        background: #0d634f;
    }
</style>

<div class="chat-bubble-btn" id="botIcon" onclick="toggleChat()">
    <i class="bi bi-robot"></i>
</div>

<div class="ai-chat-window" id="aiChatWindow">
    <div class="chat-header">
        <span><i class="bi bi-cpu-fill"></i> Multi9 AI Assistant</span>
        <i class="bi bi-x-lg" style="cursor:pointer;" onclick="toggleChat()"></i>
    </div>

    <div class="chat-video-area">
        <video width="100%" height="100%" autoplay muted loop style="object-fit: cover;">
            <source src="assets/uploads/products/melody_vedio.mp4" type="video/mp4">
        </video>
    </div>

    <div class="chat-messages" id="chatBox">
        <div class="msg bot">Hi! 👋 I'm the Multi9 Assistant. How can I help you today?</div>
    </div>

    <div class="chat-input">
        <input type="text" id="userInp" placeholder="Ask something..." autocomplete="off">
        <button class="send-btn" onclick="sendMessage()" title="Send Message">
            <i class="bi bi-send-fill"></i>
        </button>
    </div>
</div>

<script>
    const GROQ_API_KEY = "";

    function toggleChat() {
        const chatWin = document.getElementById('aiChatWindow');
        const botIcon = document.getElementById('botIcon');
        
        if (chatWin.style.display === 'flex') {
            chatWin.style.display = 'none';
            botIcon.style.display = 'flex';
        } else {
            chatWin.style.display = 'flex';
            botIcon.style.display = 'none';
            document.getElementById('userInp').focus();
            const box = document.getElementById('chatBox');
            box.scrollTop = box.scrollHeight;
        }
    }

    async function sendMessage() {
        const input = document.getElementById('userInp');
        const box = document.getElementById('chatBox');
        const userText = input.value.trim();

        if (userText === "") return;

        // User message display
        box.innerHTML += `<div class="msg user">${userText}</div>`;
        input.value = ""; 
        box.scrollTo({ top: box.scrollHeight, behavior: 'smooth' });

        // Typing indicator
        const loadingId = "loading-" + Date.now();
        box.innerHTML += `<div class="msg bot" id="${loadingId}"><span style="opacity:0.6">Thinking...</span></div>`;
        box.scrollTo({ top: box.scrollHeight, behavior: 'smooth' });

        try {
            const response = await fetch("https://api.groq.com/openai/v1/chat/completions", {
                method: "POST",
                headers: {
                    "Authorization": `Bearer ${GROQ_API_KEY}`,
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    model: "llama-3.3-70b-versatile",
                    messages: [
                        { role: "system", content: "You are a professional analytics assistant for Multi9 Computer Systems. Keep answers helpful and concise." },
                        { role: "user", content: userText }
                    ]
                })
            });

            const data = await response.json();
            document.getElementById(loadingId).innerText = data.choices[0].message.content;
        } catch (error) {
            document.getElementById(loadingId).innerText = "Sorry, I encountered an error. Please try again.";
        }
        box.scrollTo({ top: box.scrollHeight, behavior: 'smooth' });
    }

    document.getElementById('userInp').addEventListener("keypress", (e) => {
        if (e.key === "Enter") sendMessage();
    });
</script>