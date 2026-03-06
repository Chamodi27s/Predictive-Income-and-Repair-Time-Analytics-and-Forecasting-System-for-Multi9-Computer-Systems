<!-- CHATBOT STYLE -->
<style>

.chatbot-btn{
position:fixed;
bottom:25px;
right:25px;
width:65px;
height:65px;
border-radius:50%;
background:#0f766e;
color:white;
font-size:30px;
border:none;
cursor:pointer;
box-shadow:0 10px 25px rgba(0,0,0,0.3);
z-index:9999;
}

.chat-container{
position:fixed;
bottom:100px;
right:25px;
width:360px;
height:480px;
background:white;
border-radius:18px;
box-shadow:0 10px 30px rgba(0,0,0,.3);
display:none;
flex-direction:column;
overflow:hidden;
z-index:9999;
}

.chat-header{
background:#0f766e;
color:white;
padding:14px;
text-align:center;
font-weight:bold;
}

.chat-body{
flex:1;
padding:12px;
overflow-y:auto;
}

.msg{
padding:10px 14px;
border-radius:14px;
margin-bottom:8px;
font-size:14px;
max-width:80%;
}

.user{
background:#dcfce7;
margin-left:auto;
}

.bot{
background:#e5e7eb;
}

.chat-input{
display:flex;
border-top:1px solid #ddd;
}

.chat-input input{
flex:1;
padding:12px;
border:none;
outline:none;
}

.chat-input button{
background:#0f766e;
color:white;
border:none;
padding:12px 18px;
cursor:pointer;
}

</style>


<button class="chatbot-btn" onclick="toggleChat()">🤖</button>

<div class="chat-container" id="chatBox">

<div class="chat-header">
System Help Assistant
</div>

<div class="chat-body" id="chatBody">
<div class="msg bot">
Hello 👋 I can help you understand this system.
</div>
</div>

<div class="chat-input">
<input type="text" id="chatMsg" placeholder="Ask about system...">
<button onclick="sendChat()">Send</button>
</div>

</div>


<script>

function toggleChat(){
let box=document.getElementById("chatBox");

if(box.style.display==="flex"){
box.style.display="none";
}else{
box.style.display="flex";
}
}

function sendChat(){

let msg=document.getElementById("chatMsg").value;
if(msg==="") return;

let chat=document.getElementById("chatBody");

chat.innerHTML+=`<div class="msg user">${msg}</div>`;
document.getElementById("chatMsg").value="";

fetch("chatbot_api.php",{
method:"POST",
headers:{"Content-Type":"application/x-www-form-urlencoded"},
body:"message="+encodeURIComponent(msg)
})
.then(res=>res.text())
.then(reply=>{
chat.innerHTML+=`<div class="msg bot">${reply}</div>`;
chat.scrollTop=chat.scrollHeight;
});

}

</script>