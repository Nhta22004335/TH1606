<?php /* Chat AI footer */ ?>
<style>
.ai-hide{display:none}

#ai-fab{
  position:fixed;right:20px;bottom:20px;width:56px;height:56px;border-radius:50%;
  background:#2563eb;color:#fff;border:0;cursor:pointer;box-shadow:0 10px 25px rgba(0,0,0,.15);z-index:9998;
  display:flex;align-items:center;justify-content:center;transition:transform .2s,box-shadow .2s
}
#ai-fab:hover{transform:scale(1.06);box-shadow:0 14px 32px rgba(0,0,0,.2)}

#ai-box{
  position:fixed;right:20px;bottom:90px;width:360px;max-width:92vw;height:480px;z-index:9999;
  background:#fff;border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 18px 40px rgba(0,0,0,.18);
  display:flex;flex-direction:column;overflow:hidden
}
/* Quan trọng: ưu tiên ẩn khi có .ai-hide */
#ai-box.ai-hide{display:none}

#ai-mask{position:fixed;inset:0;background:transparent;z-index:9996}
#ai-mask.ai-hide{display:none}

.ai-head{background:#2563eb;color:#fff;padding:10px 12px;display:flex;align-items:center;justify-content:space-between}
.ai-head b{font-size:14px}
.ai-close{background:transparent;border:0;color:#fff;font-size:20px;cursor:pointer}

#ai-msgs{flex:1;padding:12px;overflow:auto;background:#f9fafb}
.ai-row{margin:8px 0;display:flex}
.ai-left{justify-content:flex-start}
.ai-right{justify-content:flex-end}
.ai-bubble{max-width:78%;padding:10px 12px;border-radius:12px;font-size:14px;line-height:1.4;white-space:pre-wrap;word-wrap:break-word}
.ai-bubble.user{background:#e5e7eb;color:#111827}
.ai-bubble.ai{background:#dbeafe;color:#1e3a8a}
.ai-form{display:flex;border-top:1px solid #e5e7eb}
.ai-input{flex:1;border:0;padding:10px 12px;font-size:14px;outline:none}
.ai-send{background:#2563eb;color:#fff;border:0;padding:0 14px;cursor:pointer}

</style>

<button id="ai-fab" type="button" aria-label="Chat AI">
  <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
    <path d="M21 15a4 4 0 0 1-4 4H7l-4 4V7a4 4 0 0 1 4-4h6"/><path d="M15 3h6v6"/>
  </svg>
</button>

<div id="ai-box" class="ai-hide" role="dialog" aria-modal="true" aria-label="Chat AI">
  <div class="ai-head">
    <b>Trợ lý AI</b>
    <button class="ai-close" type="button" aria-label="Đóng">×</button>
  </div>
  <div id="ai-msgs"></div>
  <form id="ai-form" class="ai-form">
    <input id="ai-input" class="ai-input" name="message" placeholder="Nhập tin nhắn..." autocomplete="off" required>
    <button class="ai-send" type="submit">Gửi</button>
  </form>
</div>

<div id="ai-mask" class="ai-hide"></div>


<script>
(function(){
  const API = window.location.origin + '/app/views/api/chat_ai.php';
  const fab = document.getElementById('ai-fab');
  const box = document.getElementById('ai-box');
  const mask = document.getElementById('ai-mask');
  const closeBtn = box.querySelector('.ai-close');
  const msgs = document.getElementById('ai-msgs');
  const form = document.getElementById('ai-form');
  const input = document.getElementById('ai-input');

  function openChat(){
    box.classList.remove('ai-hide');
    mask.classList.remove('ai-hide');
    fab.classList.add('ai-hide');   // ẩn icon khi mở
    input.focus();
  }
  function closeChat(){
    box.classList.add('ai-hide');
    mask.classList.add('ai-hide');
    fab.classList.remove('ai-hide'); // hiện lại icon khi đóng
  }

  fab.addEventListener('click', openChat);
  closeBtn.addEventListener('click', closeChat);
  mask.addEventListener('click', closeChat);
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeChat(); });

  function append(role,text){
    const row=document.createElement('div');
    row.className='ai-row '+(role==='user'?'ai-right':'ai-left');
    const b=document.createElement('div');
    b.className='ai-bubble '+(role==='user'?'user':'ai');
    b.innerHTML=String(text).replace(/\n/g,'<br>');
    row.appendChild(b); msgs.appendChild(row); msgs.scrollTop=msgs.scrollHeight;
  }

  form.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const text = input.value.trim(); if(!text) return;
    append('user', text); input.value=''; input.disabled=true;
    try{
      const r = await fetch(API,{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'message='+encodeURIComponent(text)});
      const d = await r.json().catch(()=>null);
      append('ai', d&&d.ok ? d.reply : ('⚠ '+(d&&d.error||'Lỗi phản hồi')));
    }catch{ append('ai','⚠ Lỗi kết nối'); }
    finally{ input.disabled=false; input.focus(); }
  });
})();
</script>

