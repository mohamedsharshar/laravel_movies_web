@extends('layouts.app')
@section('content')
<div class="main-content" style="max-width:600px;margin:0 auto;padding:2rem 1rem;">
    <h2 style="font-size:2rem;font-weight:900;margin-bottom:1.5rem;text-align:center;">🎬 Movie AI Assistant</h2>
    <div id="ai-chat-box" style="background:#fff;border-radius:14px;box-shadow:0 2px 16px #00bfae22;padding:1.5rem;min-height:320px;">
        <div id="ai-messages" style="min-height:180px;"></div>
        <form id="ai-form" style="display:flex;gap:0.7rem;margin-top:1.2rem;">
            <input type="text" id="ai-input" name="message" placeholder="Ask me about movies..." autocomplete="off" style="flex:1;padding:0.7rem 1rem;border-radius:8px;border:1.5px solid #00bfae;font-size:1.1rem;">
            <button type="submit" style="background:linear-gradient(90deg,#00bfae 60%,#1c2541 100%);color:#fff;font-weight:700;padding:0.7rem 1.5rem;border:none;border-radius:8px;cursor:pointer;transition:background 0.18s;">Send</button>
        </form>
    </div>
</div>
<script>
const form = document.getElementById('ai-form');
const input = document.getElementById('ai-input');
const messages = document.getElementById('ai-messages');
form.onsubmit = async function(e) {
    e.preventDefault();
    const userMsg = input.value.trim();
    if (!userMsg) return;
    messages.innerHTML += `<div style='margin-bottom:10px;text-align:right;'><span style='background:#00bfae;color:#fff;padding:8px 16px;border-radius:8px 8px 2px 16px;display:inline-block;'>${userMsg}</span></div>`;
    input.value = '';
    messages.innerHTML += `<div id='ai-typing' style='margin-bottom:10px;'><span style='background:#e0e7ef;color:#222;padding:8px 16px;border-radius:8px 8px 16px 2px;display:inline-block;'>AI is typing...</span></div>`;
    messages.scrollTop = messages.scrollHeight;
    const res = await fetch("/ai/ask", {
        method: "POST",
        headers: {"Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector('meta[name=csrf-token]').content},
        body: JSON.stringify({message: userMsg})
    });
    const data = await res.json();
    document.getElementById('ai-typing').remove();
    messages.innerHTML += `<div style='margin-bottom:10px;text-align:left;'><span style='background:#e0e7ef;color:#222;padding:8px 16px;border-radius:8px 8px 16px 2px;display:inline-block;'>${data.reply}</span></div>`;
    messages.scrollTop = messages.scrollHeight;
};
</script>
@endsection
