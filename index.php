<?php
// /web-tools/editor/index.php  (PHP 8.5 compatible)
// UI only (API moved to api.php)
?><!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Web Tools Editor</title>

  <!-- CodeMirror 5 (HTML/CSS highlighting) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/material-darker.min.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/css/css.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/htmlmixed/htmlmixed.min.js"></script>

  <style>
    :root{
      --hl: #fac800;
      --bg: #0b1020;
      --panel: #121a33;
      --border: rgba(255,255,255,.10);
      --text: rgba(255,255,255,.92);
      --muted: rgba(255,255,255,.65);
      --shadow: 0 14px 40px rgba(0,0,0,.35);
      --radius: 14px;
    }

    html, body { height:100%; margin:0; background: var(--bg); color: var(--text); font-family: ui-sans-serif, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, "Apple Color Emoji","Segoe UI Emoji"; }
    * { box-sizing: border-box; }

    .topbar{
      height: 52px;
      display:flex;
      align-items:center;
      justify-content: space-between;
      padding: 0 12px;
      border-bottom: 1px solid var(--border);
      background: linear-gradient(180deg, rgba(255,255,255,.05), rgba(255,255,255,0));
      position: sticky;
      top: 0;
      z-index: 30;
      backdrop-filter: blur(10px);
    }
    .brand{
      display:flex;
      gap:10px;
      align-items:center;
      font-weight: 700;
      letter-spacing: .2px;
    }
    .brand .dot{
      width: 10px;
      height: 10px;
      border-radius: 99px;
      background: var(--hl);
      box-shadow: 0 0 0 4px rgba(250,200,0,.18);
    }
    .toolbar{
      display:flex;
      gap:8px;
      align-items:center;
    }
    .btn{
      height: 34px;
      min-width: 34px;
      padding: 0 10px;
      border-radius: 10px;
      border: 1px solid var(--border);
      background: rgba(255,255,255,.04);
      color: var(--text);
      cursor: pointer;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      user-select: none;
    }
    .btn:hover{ background: rgba(255,255,255,.07); }
    .btn:active{ transform: translateY(1px); }
    .status{
      font-size: 12px;
      color: var(--muted);
      padding: 0 10px;
      border: 1px solid var(--border);
      border-radius: 999px;
      height: 34px;
      display:flex;
      align-items:center;
      gap:8px;
      background: rgba(255,255,255,.03);
    }
    .status .pill{
      width: 8px; height: 8px; border-radius: 99px; background: rgba(250,200,0,.9);
      box-shadow: 0 0 0 4px rgba(250,200,0,.14);
    }

    .wrap{ height: calc(100% - 52px); display:flex; flex-direction: column; }

    .split{ flex: 1; display:flex; overflow:hidden; }
    .split.row{ flex-direction: row; }
    .split.col{ flex-direction: column; }

    .pane{
      flex: 1;
      min-width: 0;
      min-height: 0;
      border-right: 1px solid var(--border);
      border-bottom: 1px solid var(--border);
      background: rgba(255,255,255,.02);
      position: relative;
      display:flex;
      flex-direction: column;
    }
    .split.row .pane:last-child{ border-right: none; }
    .split.col .pane{ border-right: none; }
    .split.col .pane:last-child{ border-bottom: none; }

    .paneHeader{
      height: 44px;
      display:flex;
      align-items:center;
      justify-content: space-between;
      padding: 0 10px;
      border-bottom: 1px solid var(--border);
      background: rgba(255,255,255,.02);
      gap: 10px;
    }
    .paneHeader .title{
      font-size: 13px;
      color: var(--muted);
      font-weight: 650;
      display:flex;
      align-items:center;
      gap: 8px;
    }

    .searchbar{
      position: sticky;
      top: 44px;
      z-index: 20;
      padding: 10px;
      border-bottom: 1px solid var(--border);
      background: rgba(11,16,32,.75);
      backdrop-filter: blur(10px);
    }
    .searchbar .searchBox{
      width: 100%;
      height: 36px;
      border-radius: 999px;
      border: 1px solid var(--border);
      background: rgba(255,255,255,.04);
      color: var(--text);
      padding: 0 14px 0 36px;
      outline: none;
      box-shadow: 0 6px 18px rgba(0,0,0,.18);
    }
    .searchbar .icon{
      position:absolute;
      left: 22px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 14px;
      color: var(--muted);
      pointer-events: none;
    }

    #editorHost{ flex:1; min-height:0; }
    .CodeMirror{ height: 100% !important; font-size: 13px; }

    /* ===== EDITOR highlight ===== */
    .cm-hl{ background: #fac800 !important; color: #111 !important; border-radius: 3px; }
    .CodeMirror-selected,
    .CodeMirror-focused .CodeMirror-selected{ background: #fac800 !important; color:#111 !important; }

    .previewWrap{ flex:1; min-height:0; overflow:hidden; }
    iframe{ width:100%; height:100%; border:none; background:#fff; }

    .historyPanel{
      position: absolute;
      right: 12px;
      top: 52px;
      width: 320px;
      max-width: calc(100vw - 24px);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      background: rgba(18,26,51,.96);
      box-shadow: var(--shadow);
      overflow:hidden;
      z-index: 60;
      display:none;
      backdrop-filter: blur(10px);
    }
    .historyPanel.show{ display:block; }
    .historyPanel .hd{
      padding: 10px 12px;
      border-bottom: 1px solid var(--border);
      display:flex;
      align-items:center;
      justify-content: space-between;
      gap:10px;
    }
    .historyPanel .hd .t{ font-weight: 700; font-size: 13px; color: var(--text); }
    .historyPanel .bd{ max-height: 420px; overflow:auto; }
    .histItem{ padding:10px 12px; border-bottom:1px solid rgba(255,255,255,.06); cursor:pointer; }
    .histItem:hover{ background: rgba(255,255,255,.05); }
    .histItem .line1{ font-size:12px; color: var(--text); font-weight:650; }
    .histItem .line2{ font-size:12px; color: var(--muted); margin-top:2px; }
    .histEmpty{ padding:14px 12px; color: var(--muted); font-size:12px; }

    .hlDot{ display:inline-block;width:10px;height:10px;border-radius:3px;background:#fac800;vertical-align:middle; }
  </style>
</head>
<body>
  <div class="topbar">
    <div class="brand">
      <div class="dot"></div>
      <div>Web Tools Editor</div>
    </div>
    <div class="toolbar">
      <div class="status" id="status"><span class="pill"></span><span id="statusText">Ready</span></div>
      <button class="btn" id="btnSave" title="Save">💾</button>
      <button class="btn" id="btnHistory" title="历史版本">📋</button>
      <button class="btn" id="btnSplitCol" title="上下分屏">↕️</button>
      <button class="btn" id="btnSplitRow" title="左右分屏">↔️</button>
    </div>
  </div>

  <div class="historyPanel" id="historyPanel">
    <div class="hd">
      <div class="t">历史版本（最多20条）</div>
      <button class="btn" id="btnHistoryClose" title="关闭">✕</button>
    </div>
    <div class="bd" id="historyList"></div>
  </div>

  <div class="wrap">
    <div class="split row" id="splitRoot">
      <div class="pane" id="paneEditor">
        <div class="paneHeader">
          <div class="title">代码编辑区（HTML + CSS）</div>
        </div>

        <div class="searchbar">
          <div class="icon">🔎</div>
          <input class="searchBox" id="searchBox" placeholder="搜索（实时高亮并滚动到命中位置）" />
        </div>

        <div id="editorHost"></div>
      </div>

      <div class="pane" id="panePreview">
        <div class="paneHeader">
          <div class="title">实时预览区</div>
          <div class="title" style="font-weight:600;">选中文本高亮：<span class="hlDot"></span></div>
        </div>
        <div class="previewWrap">
          <iframe id="previewFrame" sandbox="allow-scripts allow-forms allow-modals allow-popups allow-popups-to-escape-sandbox"></iframe>
        </div>
      </div>
    </div>
  </div>

<script>
(function(){
  const HL = '#fac800';
  const statusText = document.getElementById('statusText');
  const splitRoot = document.getElementById('splitRoot');
  const previewFrame = document.getElementById('previewFrame');
  const searchBox = document.getElementById('searchBox');

  const historyPanel = document.getElementById('historyPanel');
  const historyList = document.getElementById('historyList');
  const btnHistory = document.getElementById('btnHistory');
  const btnHistoryClose = document.getElementById('btnHistoryClose');
  const btnSplitCol = document.getElementById('btnSplitCol');
  const btnSplitRow = document.getElementById('btnSplitRow');
  const btnSave = document.getElementById('btnSave');

  function setStatus(t){ statusText.textContent = t; }
  function toast(msg){
    setStatus(msg);
    clearTimeout(toast._t);
    toast._t = setTimeout(() => setStatus('Ready'), 2500);
  }

  const defaultCode =
`<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Preview</title>
  <style>
    body{font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial; padding:24px;}
    .card{border:1px solid #e5e7eb;border-radius:14px;padding:18px;max-width:760px}
    h1{margin:0 0 10px 0;font-size:22px}
    p{margin:0;color:#334155;line-height:1.6}
    .tag{display:inline-block;background:#0ea5e9;color:#fff;border-radius:999px;padding:6px 10px;font-size:12px;margin-top:12px}
  </style>
</head>
<body>
  <div class="card">
    <h1>实时编辑 + 实时预览</h1>
    <p>选中这里的文本（不是代码），预览区会同步高亮。</p>
    <div class="tag">autosave 每分钟</div>
  </div>
</body>
</html>`;

  const LS_KEY = 'dwith_webtools_editor_code_v3';

  const editor = CodeMirror(document.getElementById('editorHost'), {
    value: localStorage.getItem(LS_KEY) || defaultCode,
    mode: 'htmlmixed',
    theme: 'material-darker',
    lineNumbers: true,
    lineWrapping: true,
    indentUnit: 2,
    tabSize: 2,
    viewportMargin: Infinity
  });

  // ---------- Marks ----------
  let searchMarks = [];
  let selectionMark = null;

  function clearMarks(arr){
    while(arr.length) { try { arr.pop().clear(); } catch(e){} }
  }
  function clearSelectionMark(){
    if (selectionMark) { try { selectionMark.clear(); } catch(e){} }
    selectionMark = null;
  }

  function escapeRegExp(s){ return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }

  function isLikelyTextSelection(s){
    if (!s) return false;
    const t = String(s).trim();
    if (t.length < 1) return false;
    if (t.length > 200) return false;
    if (/[<>]/.test(t)) return false;
    if (/[{};]/.test(t)) return false;
    return true;
  }

  // ---------- Preview highlight engine (NO editing) ----------
  function injectPreviewHighlighter(){
    const win = previewFrame.contentWindow;
    if (!win || !win.document) return;

    const doc = win.document;

    // remove old artifacts
    try{
      const oldStyle = doc.getElementById('__dwith_hl_style__');
      if (oldStyle) oldStyle.remove();
      const oldScript = doc.getElementById('__dwith_hl_script__');
      if (oldScript) oldScript.remove();
    } catch(e){}

    const style = doc.createElement('style');
    style.id = '__dwith_hl_style__';
    style.textContent = `
.__dwith_hl__{ background:${HL}; color:#111; padding:0 2px; border-radius:3px; }
`;
    doc.head.appendChild(style);

    const script = doc.createElement('script');
    script.id = '__dwith_hl_script__';
    script.type = 'text/javascript';
    script.text = `
(function(){
  function unwrap(){
    const spans = document.querySelectorAll('span.__dwith_hl__');
    spans.forEach(sp => {
      const p = sp.parentNode;
      while (sp.firstChild) p.insertBefore(sp.firstChild, sp);
      p.removeChild(sp);
      p.normalize();
    });
  }

  function isValidText(s){
    if (!s) return false;
    const t = String(s).trim();
    if (t.length < 1) return false;
    if (t.length > 200) return false;
    if (/[<>]/.test(t)) return false;
    if (/[{};]/.test(t)) return false;
    return true;
  }

  function highlight(text){
    unwrap();
    if (!isValidText(text)) return;
    const needle = String(text).trim();
    if (!needle) return;

    const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, {
      acceptNode(n){
        if (!n || !n.nodeValue) return NodeFilter.FILTER_REJECT;
        if (!n.parentElement) return NodeFilter.FILTER_REJECT;
        if (n.parentElement.closest('script,style,head,textarea,input')) return NodeFilter.FILTER_REJECT;
        return NodeFilter.FILTER_ACCEPT;
      }
    });

    let node;
    while((node = walker.nextNode())){
      const hay = node.nodeValue;
      const idx = hay.indexOf(needle);
      if (idx === -1) continue;

      const r = document.createRange();
      r.setStart(node, idx);
      r.setEnd(node, idx + needle.length);
      const sp = document.createElement('span');
      sp.className='__dwith_hl__';
      try{
        r.surroundContents(sp);
        sp.scrollIntoView({block:'center', inline:'nearest', behavior:'smooth'});
      }catch(e){}
      break;
    }
  }

  window.addEventListener('message', (ev) => {
    const d = ev.data || {};
    if (d.type === 'dwith_hl') highlight(d.text || '');
    if (d.type === 'dwith_clear_hl') unwrap();
  });

  // expose no-edit mode: do NOT add any listeners for input/editing
})();
`;
    doc.body.appendChild(script);
  }

  // ---------- Render preview ----------
  let renderTimer = null;

  function renderPreview(){
    const code = editor.getValue();
    previewFrame.srcdoc = code;
    try { localStorage.setItem(LS_KEY, code); } catch(e){}
  }

  editor.on('change', () => {
    clearTimeout(renderTimer);
    renderTimer = setTimeout(renderPreview, 180);
    scheduleDirty();
  });

  previewFrame.addEventListener('load', () => {
    injectPreviewHighlighter();
    // re-apply current selection highlight after reload
    const sel = editor.getDoc().getSelection();
    if (isLikelyTextSelection(sel)) {
      try { previewFrame.contentWindow.postMessage({type:'dwith_hl', text: sel}, '*'); } catch(e){}
    } else {
      try { previewFrame.contentWindow.postMessage({type:'dwith_clear_hl'}, '*'); } catch(e){}
    }
  });

  renderPreview();

  // ---------- Search ----------
  function applySearch(q){
    clearMarks(searchMarks);
    if (!q) return;

    const doc = editor.getDoc();
    const full = doc.getValue();
    const re = new RegExp(escapeRegExp(q), 'gi');
    let m, firstFrom = null, firstTo = null;

    while ((m = re.exec(full)) !== null) {
      const from = doc.posFromIndex(m.index);
      const to = doc.posFromIndex(m.index + m[0].length);
      if (!firstFrom) { firstFrom = from; firstTo = to; }
      searchMarks.push(doc.markText(from, to, { className: 'cm-hl' }));
      if (m.index === re.lastIndex) re.lastIndex++;
      if (searchMarks.length > 400) break;
    }

    if (firstFrom) {
      doc.setSelection(firstFrom, firstTo);
      editor.scrollIntoView({ from: firstFrom, to: firstTo }, 80);
    }
  }

  let searchTimer = null;
  searchBox.addEventListener('input', () => {
    clearTimeout(searchTimer);
    const q = searchBox.value;
    searchTimer = setTimeout(() => applySearch(q), 80);
  });

  // ---------- Selection highlight sync (editor -> preview) ----------
  function markSelectionInEditor(){
    clearSelectionMark();
    const doc = editor.getDoc();
    const sel = doc.getSelection();
    if (!isLikelyTextSelection(sel)) {
      try { previewFrame.contentWindow && previewFrame.contentWindow.postMessage({type:'dwith_clear_hl'}, '*'); } catch(e){}
      return;
    }

    const from = doc.getCursor('from');
    const to = doc.getCursor('to');
    selectionMark = doc.markText(from, to, { className: 'cm-hl' });

    // IMPORTANT: send highlight message ALWAYS
    try {
      previewFrame.contentWindow && previewFrame.contentWindow.postMessage({ type:'dwith_hl', text: sel }, '*');
    } catch(e){}
  }

  // More reliable than cursorActivity in some cases (mouse selection)
  editor.on('cursorActivity', markSelectionInEditor);
  editor.on('mouseup', markSelectionInEditor);
  editor.on('keyup', markSelectionInEditor);

  // ---------- Autosave + Manual Save ----------
  let dirty = false;
  let lastSavedTs = '';
  let lastManualSave = 0;

  function nowTs(){
    const d = new Date();
    const pad = n => String(n).padStart(2, '0');
    return `${d.getFullYear()}${pad(d.getMonth()+1)}${pad(d.getDate())}_${pad(d.getHours())}${pad(d.getMinutes())}${pad(d.getSeconds())}`;
  }

  function scheduleDirty(){ dirty = true; }

  async function saveToServer(){
    const ts = nowTs();
    const code = editor.getValue();
    const size = new Blob([code]).size;
    if (size > 2 * 1024 * 1024) {
      toast('File size is too large');
      return false;
    }

    try{
      setStatus('Saving...');
      const res = await fetch('api.php?action=save', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ ts, code })
      });
      const j = await res.json().catch(() => null);
      if (j && j.ok) {
        dirty = false;
        lastSavedTs = ts;
        return true;
      } else {
        toast((j && j.error) ? String(j.error) : 'Save failed');
        return false;
      }
    } catch(e){
      toast('Save failed');
      return false;
    }
  }

  async function autosave(){
    if (!dirty) return;
    const ok = await saveToServer();
    if (ok) setStatus('Saved ' + lastSavedTs);
  }

  async function manualSave(){
    const now = Date.now();
    if (now - lastManualSave < 5000) {
      toast('Do not save repeatedly...');
      return;
    }
    lastManualSave = now;

    const ok = await saveToServer();
    if (ok) toast('Saved successfully');
  }

  btnSave.addEventListener('click', manualSave);

  setInterval(autosave, 60 * 1000);
  window.addEventListener('beforeunload', () => {
    try { localStorage.setItem(LS_KEY, editor.getValue()); } catch(e){}
  });

  // ---------- History panel ----------
  function toggleHistory(show){
    if (typeof show === 'boolean') {
      historyPanel.classList.toggle('show', show);
      return;
    }
    historyPanel.classList.toggle('show');
  }

  async function loadHistoryList(){
    historyList.innerHTML = '';
    try{
      const res = await fetch('api.php?action=list', { cache: 'no-store' });
      const j = await res.json();
      if (!j || !j.ok || !Array.isArray(j.items) || j.items.length === 0) {
        historyList.innerHTML = '<div class="histEmpty">暂无历史版本（需等待自动保存或手动保存）</div>';
        return;
      }

      const frag = document.createDocumentFragment();
      j.items.forEach(it => {
        const div = document.createElement('div');
        div.className = 'histItem';
        const dt = new Date(it.mtime * 1000);
        const label = dt.toLocaleString();
        div.innerHTML = `<div class="line1">${label}</div><div class="line2">${it.file} · ${it.size} bytes</div>`;
        div.addEventListener('click', async () => {
          try{
            const rr = await fetch('api.php?action=load&file=' + encodeURIComponent(it.file), { cache: 'no-store' });
            const jj = await rr.json();
            if (jj && jj.ok && jj.data && typeof jj.data.code === 'string') {
              editor.setValue(jj.data.code);
              renderPreview();
              setStatus('Restored ' + (jj.data.ts || it.file));
              dirty = false;
              toggleHistory(false);
            }
          } catch(e){}
        });
        frag.appendChild(div);
      });
      historyList.appendChild(frag);
    } catch(e){
      historyList.innerHTML = '<div class="histEmpty">加载失败</div>';
    }
  }

  btnHistory.addEventListener('click', async () => {
    const willShow = !historyPanel.classList.contains('show');
    toggleHistory(willShow);
    if (willShow) await loadHistoryList();
  });
  btnHistoryClose.addEventListener('click', () => toggleHistory(false));

  // ---------- Split controls ----------
  btnSplitCol.addEventListener('click', () => {
    splitRoot.classList.remove('row');
    splitRoot.classList.add('col');
  });
  btnSplitRow.addEventListener('click', () => {
    splitRoot.classList.remove('col');
    splitRoot.classList.add('row');
  });

})();
</script>
</body>
</html>