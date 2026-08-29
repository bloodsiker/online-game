<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Добыча ресурсов</title>
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    @vite('resources/js/app.js')
    <style>
        * { box-sizing: border-box; }
        html, body { min-height: 100%; margin: 0; }
        body { color: #211b14; font: 12px Tahoma, Arial, sans-serif; }
        .gathering-page { margin: 0 auto; }
        .gathering-layout { display: grid; grid-template-columns: minmax(0, 1fr) 275px; gap: 10px; align-items: start; }
        .gathering-frame { border: 2px ridge #9c815b; background: #c8b792; box-shadow: inset 0 0 0 2px #e8ddc4, 0 2px 8px rgba(0, 0, 0, .35); }
        .gathering-frame-head, .gathering-frame-foot { padding: 6px 9px; border: 1px solid #826c4f; background: linear-gradient(#efe5cf, #cdbb98); }
        .gathering-frame-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
        .gathering-frame-title { color: #6e160f; font-weight: bold; }
        .gathering-frame-meta { color: #4c4031; font-size: 11px; }
        .gathering-frame-foot { min-height: 31px; border-top: 0; background: #eee3ca; font-size: 11px; }
        .gathering-field-wrap { position: relative; border: 1px solid #7e6b4e; border-top: 0; background: #292a20; }
        .gathering-field { position: relative; height: 520px; overflow: scroll; cursor: grab; scrollbar-color: #826c4f #d6c6a3; scrollbar-width: auto; }
        .gathering-field.is-dragging { cursor: grabbing; user-select: none; }
        .gathering-canvas { position: relative; width: 1254px; height: 1254px; overflow: hidden; background-position: center; background-size: cover; }
        .gathering-canvas::before { content: ""; position: absolute; inset: 0; z-index: 1; pointer-events: none; background: linear-gradient(rgba(21,16,10,.03), rgba(21,16,10,.3)); }
        .gathering-help { position: absolute; top: 8px; left: 50%; z-index: 4; transform: translateX(-50%); width: max-content; max-width: calc(100% - 20px); padding: 4px 9px; color: #f7eacb; background: rgba(31,27,22,.82); border: 1px solid rgba(236,214,167,.5); font-size: 10px; text-align: center; }
        .gathering-empty { position: absolute; top: 50%; left: 50%; z-index: 4; transform: translate(-50%, -50%); width: 80%; padding: 12px; color: #f7eacb; background: rgba(31,27,22,.82); border: 1px solid #c5a86d; text-align: center; }
        .gathering-node { position: absolute; z-index: 3; width: 34px; height: 34px; padding: 0; border: 0; border-radius: 50%; transform: translate(-50%, -50%); background: transparent; cursor: pointer; font: inherit; user-select: none; }
        .gathering-node-ring { position: absolute; inset: 0; display: grid; place-items: center; overflow: hidden; border: 2px solid var(--rarity, #d9ba69); border-radius: 50%; background: rgba(13,18,13,.72); box-shadow: 0 0 0 1px rgba(63,43,18,.9), 0 0 7px rgba(255,220,111,.38), inset 0 0 4px rgba(0,0,0,.85); animation: gathering-pulse 2.1s ease-in-out infinite; transition: transform .12s ease; }
        .gathering-node:hover .gathering-node-ring { transform: scale(1.07); filter: brightness(1.15); }
        .gathering-node-icon { width: 21px; height: 21px; object-fit: contain; filter: drop-shadow(0 1px 1px rgba(0,0,0,.8)); }
        .gathering-node-gatherers { position: absolute; top: -7px; right: -7px; z-index: 2; display: none; min-width: 15px; height: 15px; padding: 0 3px; color: #fff7e3; background: #b52e24; border: 1px solid #f0b18a; border-radius: 50%; box-shadow: 0 1px 3px rgba(58,19,12,.55); font-size: 9px; font-weight: 700; line-height: 13px; text-align: center; pointer-events: none; }
        .gathering-node-gatherers:not(:empty) { display: block; }
        .gathering-node.is-active .gathering-node-ring { border-color: #fff0a4; animation: gathering-active-pulse 1.35s ease-in-out infinite; }
        .gathering-node.is-selected .gathering-node-ring { box-shadow: 0 0 0 3px #fff0a4, 0 0 16px rgba(255,220,111,.7), inset 0 0 8px rgba(0,0,0,.85); }
        .gathering-node.is-busy:not(.is-active) .gathering-node-ring { border-color: #be3729; animation: gathering-busy-pulse 1.35s ease-in-out infinite; }
        .gathering-selection { display: flex; align-items: center; gap: 8px; min-height: 54px; }
        .gathering-selection img { width: 48px; height: 48px; object-fit: contain; filter: drop-shadow(0 2px 1px rgba(0,0,0,.45)); }
        .gathering-selection-name { color: #6e160f; font-weight: bold; }
        .gathering-selection-meta { margin-top: 3px; color: #5d5040; font-size: 10px; }
        .gathering-selection.empty { color: #7c6e59; font-style: italic; }
        .gathering-work { display: none; margin-top: 8px; padding-top: 8px; color: #312519; border-top: 1px solid #b29e79; }
        .gathering-work.is-visible { display: block; }
        .gathering-work-row, .gathering-skill-row, .gathering-tooltip-row { display: flex; justify-content: space-between; gap: 8px; }
        .gathering-work-row { margin-bottom: 5px; font-size: 11px; }
        .gathering-progress-track, .gathering-xp-track { overflow: hidden; border: 1px solid #9d8b68; background: #332c22; box-shadow: inset 0 1px 3px rgba(0,0,0,.75); }
        .gathering-progress-track { height: 15px; border-color: #c9b98d; background: #1d1a16; }
        .gathering-progress-fill, .gathering-xp-fill { height: 100%; }
        .gathering-progress-fill { width: 0; background: repeating-linear-gradient(135deg, #789331 0 8px, #96b641 8px 16px); }
        .gathering-cancel { width: 100%; margin-top: 7px; padding: 4px 8px; color: #5f190d; background: linear-gradient(#ffe2ba, #f0b47b); border: 1px solid #a54c2b; box-shadow: inset 0 1px rgba(255,255,255,.45); font: 11px Tahoma, Arial, sans-serif; cursor: pointer; }
        .gathering-cancel:hover { background: linear-gradient(#ffedc9, #f6c18e); }
        .gathering-cancel:disabled { opacity: .55; cursor: wait; }
        .gathering-xp-track { height: 11px; margin: 5px 0; }
        .gathering-xp-fill { background: linear-gradient(#a5c94d, #708f2b); }
        .gathering-sidebar { display: grid; gap: 10px; }
        .gathering-sidebar .gathering-frame-foot { border-top: 1px solid #826c4f; }
        .gathering-skill { margin-bottom: 9px; }
        .gathering-skill:last-child { margin-bottom: 0; }
        .gathering-skill-level { color: #6e160f; font-weight: bold; }
        .gathering-note { color: #5d5040; font-size: 10px; line-height: 1.35; }
        .gathering-bag { min-height: 42px; margin: 0; padding: 0; list-style: none; }
        .gathering-bag li { display: flex; align-items: center; gap: 5px; padding: 3px 0; border-bottom: 1px dotted #b29e79; }
        .gathering-bag img { width: 20px; height: 20px; object-fit: contain; }
        .gathering-bag-count { margin-left: auto; font-weight: bold; }
        .gathering-bag .empty { color: #7c6e59; font-style: italic; }
        .gathering-tooltip { position: fixed; z-index: 1000; display: none; width: 260px; padding: 2px; color: #461c0b; background: #d8905c; border: 2px ridge #9b562e; box-shadow: 0 4px 12px rgba(50,22,8,.46), inset 0 0 0 1px #ffe2bd; font-size: 10px; pointer-events: none; }
        .gathering-tooltip-inner { padding: 8px; border: 1px solid #c5774b; background: linear-gradient(135deg, #fbd4a4, #f4bb8a); }
        .gathering-tooltip-head { display: flex; align-items: center; gap: 7px; padding-bottom: 6px; border-bottom: 1px solid #d18a5d; }
        .gathering-tooltip-icon { width: 34px; height: 34px; object-fit: contain; filter: drop-shadow(0 2px 1px rgba(83,37,14,.42)); }
        .gathering-tooltip-name { color: #641c08; font-weight: bold; text-shadow: 0 1px #ffe7c7; }
        .gathering-tooltip-rarity { margin-top: 2px; font-size: 9px; font-style: italic; }
        .gathering-tooltip-row { display: flex; justify-content: space-between; gap: 8px; padding: 4px 1px 0; color: #79401f; border-bottom: 1px dotted rgba(136,72,35,.36); }
        .gathering-tooltip-row span:last-child { color: #461c0b; font-weight: bold; text-align: right; }
        .gathering-tooltip-warning { margin-top: 6px; padding: 4px 5px; color: #7a160e; background: rgba(255,232,205,.62); border: 1px solid #b55d45; }
        .gathering-notice { position: absolute; top: 39px; left: 50%; z-index: 4; display: none; width: max-content; max-width: calc(100% - 20px); padding: 5px 9px; color: #5f190d; background: linear-gradient(#ffe2ba, #f4b67f); border: 1px solid #a54c2b; box-shadow: 0 3px 10px rgba(54,25,9,.45); font-size: 11px; line-height: 1.35; text-align: center; transform: translateX(-50%); }
        .gathering-notice.is-error { color: #7c170e; background: linear-gradient(#ffe0cf, #f0a98e); border-color: #a33d2f; }
        .gathering-notice.is-warn { color: #654212; background: linear-gradient(#fff0be, #efc26f); border-color: #a97824; }
        @keyframes gathering-pulse { 50% { transform: scale(1.04); } }
        @keyframes gathering-active-pulse { 50% { transform: scale(1.09); box-shadow: 0 0 0 2px rgba(255,240,164,.7), 0 0 13px rgba(255,220,111,.75), inset 0 0 5px rgba(0,0,0,.8); } }
        @keyframes gathering-busy-pulse { 50% { transform: scale(1.08); box-shadow: 0 0 0 2px rgba(220,69,51,.66), 0 0 13px rgba(210,50,36,.76), inset 0 0 5px rgba(0,0,0,.8); } }
        @media (max-width: 760px) { .gathering-layout { grid-template-columns: 1fr; } .gathering-field { height: 420px; } }
    </style>
</head>
<body>
<main class="gathering-page">
    <div class="gathering-layout">
        <section class="gathering-frame">
            <div class="gathering-field-wrap">
                <div class="gathering-help">Тяните карту или используйте прокрутку · двойной клик — начать добычу</div>
                <div class="gathering-notice" id="gathering-notice" role="status" aria-live="polite"></div>
                <div class="gathering-empty" id="gathering-empty" hidden></div>
                <div class="gathering-field" id="gathering-field" tabindex="0" aria-label="Карта сбора ресурсов. Перемещайте её полосами прокрутки или перетаскиванием мышью.">
                    <div class="gathering-canvas" id="gathering-canvas" style="background-image:linear-gradient(to bottom,rgba(25,31,20,.08),rgba(25,31,20,.45)),url('{{ asset('prototypes/map.png') }}')">
                        <div id="gathering-nodes"></div>
                    </div>
                </div>
            </div>
        </section>
        <aside class="gathering-sidebar">
            <section class="gathering-frame"><header class="gathering-frame-head"><span class="gathering-frame-title">Выбранный ресурс</span></header><div class="gathering-frame-foot"><div class="gathering-selection empty" id="gathering-selection">Нажмите на ресурс на карте.</div><div class="gathering-work" id="gathering-work"><div class="gathering-work-row"><strong id="gathering-work-name">Добыча</strong><span id="gathering-work-time">0%</span></div><div class="gathering-progress-track" id="gathering-progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="gathering-progress-fill" id="gathering-progress-fill"></div></div><button class="gathering-cancel" id="gathering-cancel" type="button">Отменить добычу</button></div></div></section>
            <section class="gathering-frame"><header class="gathering-frame-head"><span class="gathering-frame-title">Мирные профессии</span></header><div class="gathering-frame-foot" id="gathering-professions"></div></section>
            <section class="gathering-frame"><header class="gathering-frame-head"><span class="gathering-frame-title">Добыто за этот заход</span></header><div class="gathering-frame-foot"><ul class="gathering-bag" id="gathering-bag"><li class="empty">пока ничего</li></ul></div></section>
            <section class="gathering-frame"><div class="gathering-frame-foot gathering-note">Предмет выдаётся даже при переполненном рюкзаке. Пока рюкзак перегружен, персонаж не сможет перейти на другую локацию.</div></section>
        </aside>
    </div>
</main>
<div class="gathering-tooltip" id="gathering-tooltip"></div>
@php
    $initialGatheringState = [
        'enabled' => $page->enabled,
        'message' => $page->message,
        'nodes' => $page->nodes,
        'professions' => $page->professions,
        'activeAttempt' => $page->activeAttempt,
        'serverTime' => now()->toIso8601String(),
    ];
@endphp
<script>
(function () {
    'use strict';
    try { window.parent.setMapHiddenForGathering(true); } catch (e) {}
    const urls = { state: @js($page->stateUrl), start: @js($page->startUrl), complete: @js($page->completeUrl), cancel: @js($page->cancelUrl) };
    const mapId = @js($page->mapId);
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const initial = @json($initialGatheringState);
    const state = { nodes: new Map(), professions: [], attempt: null, selectedNodeId: null, lastResult: null, noticeTimer: null, serverOffset: 0, inventory: {}, completing: false, refreshing: null, viewportPositioned: false, pan: null };
    const el = { field: document.getElementById('gathering-field'), nodes: document.getElementById('gathering-nodes'), empty: document.getElementById('gathering-empty'), selection: document.getElementById('gathering-selection'), work: document.getElementById('gathering-work'), workName: document.getElementById('gathering-work-name'), workTime: document.getElementById('gathering-work-time'), progress: document.getElementById('gathering-progress-fill'), progressTrack: document.getElementById('gathering-progress-track'), cancel: document.getElementById('gathering-cancel'), professions: document.getElementById('gathering-professions'), bag: document.getElementById('gathering-bag'), tooltip: document.getElementById('gathering-tooltip'), notice: document.getElementById('gathering-notice') };

    function request(url, method) {
        return fetch(url, { method: method || 'GET', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' }).then(async function (response) { const data = await response.json(); if (!response.ok) throw data; return data; });
    }
    function showNotice(message, type) { clearTimeout(state.noticeTimer); el.notice.textContent = message; el.notice.className = 'gathering-notice ' + (type || ''); el.notice.style.display = 'block'; state.noticeTimer = window.setTimeout(function () { el.notice.style.display = 'none'; }, 4200); }
    function setLog(message, type) { if (type) showNotice(message, type); }
    function escapeHtml(value) { const div = document.createElement('div'); div.textContent = value == null ? '' : String(value); return div.innerHTML; }
    function positionViewportOnce() { if (state.viewportPositioned) return; state.viewportPositioned = true; window.requestAnimationFrame(function () { el.field.scrollLeft = Math.max(0, (el.field.scrollWidth - el.field.clientWidth) / 2); el.field.scrollTop = Math.max(0, (el.field.scrollHeight - el.field.clientHeight) / 2); }); }
    function startPan(event) { if (event.button !== 0 || event.target.closest('.gathering-node')) return; state.pan = { pointerId: event.pointerId, x: event.clientX, y: event.clientY, left: el.field.scrollLeft, top: el.field.scrollTop }; el.field.classList.add('is-dragging'); el.field.setPointerCapture(event.pointerId); }
    function movePan(event) { if (!state.pan || state.pan.pointerId !== event.pointerId) return; el.field.scrollLeft = state.pan.left - (event.clientX - state.pan.x); el.field.scrollTop = state.pan.top - (event.clientY - state.pan.y); }
    function stopPan(event) { if (!state.pan || state.pan.pointerId !== event.pointerId) return; state.pan = null; el.field.classList.remove('is-dragging'); if (el.field.hasPointerCapture(event.pointerId)) el.field.releasePointerCapture(event.pointerId); }
    function applyState(data) {
        state.serverOffset = Date.parse(data.serverTime) - Date.now(); state.professions = data.professions || []; state.attempt = data.activeAttempt || null;
        state.nodes = new Map((data.nodes || []).map(function (node) { return [Number(node.id), node]; }));
        if (state.attempt) state.selectedNodeId = Number(state.attempt.nodeId); else if (state.selectedNodeId !== null && !state.nodes.has(state.selectedNodeId)) state.selectedNodeId = null;
        el.empty.hidden = data.enabled && state.nodes.size > 0; if (!el.empty.hidden) el.empty.textContent = data.message || 'На карте нет доступных ресурсов.';
        renderProfessions(); renderNodes(); renderSelection(); renderProgress();
    }
    function renderProfessions() {
        if (!state.professions.length) { el.professions.innerHTML = '<div class="gathering-note">Профессии ещё не созданы. Выполните миграции.</div>'; return; }
        el.professions.innerHTML = state.professions.map(function (p) { const progress = Math.min(100, p.levelExperience / p.levelExperienceRequired * 100); return '<div class="gathering-skill"><div class="gathering-skill-row"><span>' + escapeHtml(p.name) + ' <span class="gathering-skill-level">' + p.level + ' ур.</span></span><span>' + Math.round(progress) + '%</span></div><div class="gathering-xp-track"><div class="gathering-xp-fill" style="width:' + progress + '%"></div></div></div>'; }).join('') + '<div class="gathering-note">Опыт начисляется только за успешно добытый ресурс.</div>';
    }
    function renderNodes() {
        const existing = new Map(Array.from(el.nodes.querySelectorAll('.gathering-node')).map(function (node) { return [Number(node.dataset.nodeId), node]; }));
        existing.forEach(function (node, id) { if (!state.nodes.has(id)) node.remove(); });
        state.nodes.forEach(function (node) {
            let button = existing.get(node.id);
            if (!button) {
                button = document.createElement('button'); button.type = 'button'; button.className = 'gathering-node'; button.dataset.nodeId = node.id;
                button.innerHTML = '<span class="gathering-node-ring"><img class="gathering-node-icon" alt=""></span><span class="gathering-node-gatherers" aria-hidden="true"></span>';
                button.addEventListener('click', function () { selectNode(Number(button.dataset.nodeId)); });
                button.addEventListener('dblclick', function (event) { event.preventDefault(); startGathering(Number(button.dataset.nodeId)); });
                button.addEventListener('keydown', function (event) { if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); startGathering(Number(button.dataset.nodeId)); } });
                button.addEventListener('pointerenter', showTooltip); button.addEventListener('pointermove', positionTooltip); button.addEventListener('pointerleave', function () { el.tooltip.style.display = 'none'; }); el.nodes.appendChild(button);
            }
            button.style.left = node.x + '%'; button.style.top = node.y + '%'; button.style.setProperty('--rarity', node.rarityColor);
            button.querySelector('.gathering-node-icon').src = node.image; button.querySelector('.gathering-node-icon').alt = node.name;
            const active = !!state.attempt && state.attempt.nodeId === node.id; button.classList.toggle('is-active', active); button.classList.toggle('is-selected', state.selectedNodeId === node.id); button.classList.toggle('is-busy', node.busy);
            button.querySelector('.gathering-node-gatherers').textContent = node.gatheringPlayersCount > 0 ? node.gatheringPlayersCount : '';
            button.setAttribute('aria-label', node.name + (node.gatheringPlayersCount > 0 ? ': сейчас добывают: ' + node.gatheringPlayersCount + '.' : (node.blockedReason ? ': ' + node.blockedReason : '')));
        });
    }
    function selectNode(nodeId) { if (!state.nodes.has(nodeId)) return; state.lastResult = null; state.selectedNodeId = nodeId; renderNodes(); renderSelection(); }
    function renderSelection() {
        const node = state.nodes.get(state.selectedNodeId);
        if (!node) { el.selection.className = 'gathering-selection empty'; el.selection.textContent = state.lastResult || 'Нажмите на ресурс на карте.'; return; }
        el.selection.className = 'gathering-selection';
        el.selection.innerHTML = '<img src="' + escapeHtml(node.image) + '" alt=""><div><div class="gathering-selection-name">' + escapeHtml(node.name) + '</div><div class="gathering-selection-meta">Требуется умение: ' + escapeHtml(node.professionName || 'Ресурс') + ' ' + node.requiredLevel + ' ур.</div></div>';
    }
    function showTooltip(event) {
        const node = state.nodes.get(Number(event.currentTarget.dataset.nodeId)); if (!node) return;
        el.tooltip.innerHTML = '<div class="gathering-tooltip-inner"><div class="gathering-tooltip-head"><img class="gathering-tooltip-icon" src="' + escapeHtml(node.image) + '" alt=""><div><div class="gathering-tooltip-name">' + escapeHtml(node.name) + '</div><div class="gathering-tooltip-rarity" style="color:' + escapeHtml(node.rarityColor) + '">' + escapeHtml(node.rarityLabel) + '</div></div></div><div class="gathering-tooltip-row"><span>Требуется умение</span><span>' + escapeHtml(node.professionName) + ' ' + node.requiredLevel + ' ур.</span></div><div class="gathering-tooltip-row"><span>Инструмент</span><span>' + escapeHtml(node.toolName) + '</span></div>' + (node.blockedReason ? '<div class="gathering-tooltip-warning">' + escapeHtml(node.blockedReason) + '</div>' : '') + '</div>';
        el.tooltip.style.display = 'block'; positionTooltip(event);
    }
    function positionTooltip(event) { const bounds = el.tooltip.getBoundingClientRect(); let left = event.clientX + 12, top = event.clientY + 12; if (left + bounds.width > innerWidth - 8) left = event.clientX - bounds.width - 12; if (top + bounds.height > innerHeight - 8) top = event.clientY - bounds.height - 12; el.tooltip.style.left = left + 'px'; el.tooltip.style.top = top + 'px'; }
    function startGathering(nodeId) {
        const node = state.nodes.get(nodeId); if (!node || state.attempt) { setLog('Сначала завершите текущую добычу.', 'is-warn'); return; }
        state.lastResult = null; state.selectedNodeId = nodeId; request(urls.start.replace('__NODE__', String(nodeId)), 'POST').then(function (data) { state.attempt = data.attempt; setLog(data.message); renderNodes(); renderSelection(); renderProgress(); }).catch(function (error) { setLog(error.message || 'Не удалось начать добычу.', 'is-error'); refreshState(); });
    }
    function renderProgress() { if (!state.attempt) { el.work.classList.remove('is-visible'); el.progress.style.width = '0%'; return; } const node = state.nodes.get(Number(state.attempt.nodeId)); el.work.classList.add('is-visible'); el.workName.textContent = 'Добыча: ' + (node ? node.name : 'ресурс'); window.requestAnimationFrame(updateProgress); }
    function updateProgress() {
        if (!state.attempt) return; const now = Date.now() + state.serverOffset, start = Date.parse(state.attempt.startedAt), end = Date.parse(state.attempt.completesAt); const progress = Math.max(0, Math.min(1, (now - start) / Math.max(1, end - start))), percent = Math.round(progress * 100);
        el.progress.style.width = (progress * 100).toFixed(2) + '%'; el.progressTrack.setAttribute('aria-valuenow', percent); el.workTime.textContent = percent + '%'; if (progress < 1) { window.requestAnimationFrame(updateProgress); return; } if (!state.completing) completeGathering();
    }
    function completeGathering() {
        state.completing = true; request(urls.complete, 'POST').then(function (data) { state.attempt = null; state.completing = false; state.lastResult = data.ok ? null : data.message; if (data.reward) state.inventory[data.reward.shareItemId] = { ...data.reward, count: (state.inventory[data.reward.shareItemId]?.count || 0) + data.reward.count }; setLog(data.message); renderSelection(); renderBag(); return refreshState(); }).catch(function (error) { state.completing = false; state.lastResult = error.message || 'Не удалось завершить добычу.'; renderSelection(); setLog(state.lastResult, 'is-error'); refreshState(); });
    }
    function cancelGathering() {
        if (!state.attempt || state.completing) return;
        state.completing = true; el.cancel.disabled = true; request(urls.cancel, 'POST').then(function (data) { state.attempt = null; state.completing = false; el.cancel.disabled = false; setLog(data.message); renderNodes(); renderProgress(); return refreshState(); }).catch(function (error) { state.completing = false; el.cancel.disabled = false; setLog(error.message || 'Не удалось отменить добычу.', 'is-error'); });
    }
    function renderBag() { const items = Object.values(state.inventory); el.bag.innerHTML = items.length ? items.map(function (item) { return '<li><img src="' + escapeHtml(item.image) + '" alt=""><span>' + escapeHtml(item.name) + '</span><span class="gathering-bag-count">×' + item.count + '</span></li>'; }).join('') : '<li class="empty">пока ничего</li>'; }
    function refreshState() {
        if (state.refreshing) return state.refreshing;
        state.refreshing = request(urls.state).then(applyState).catch(function () { setLog('Не удалось обновить общее поле карты.', 'is-error'); }).finally(function () { state.refreshing = null; });
        return state.refreshing;
    }
    function subscribeToGatheringMap() {
        if (!window.Echo || mapId < 1) { setLog('Не удалось подключиться к обновлениям карты.', 'is-error'); return; }
        window.Echo.channel('gathering.map.' + mapId).listen('.gathering.map.updated', refreshState);
        const connection = window.Echo.connector && window.Echo.connector.pusher ? window.Echo.connector.pusher.connection : null;
        if (connection) {
            connection.bind('connected', refreshState);
            connection.bind('unavailable', function () { setLog('Связь с картой потеряна. Выполняется переподключение.', 'is-warn'); });
        }
    }
    el.field.addEventListener('pointerdown', startPan); el.field.addEventListener('pointermove', movePan); el.field.addEventListener('pointerup', stopPan); el.field.addEventListener('pointercancel', stopPan);
    el.cancel.addEventListener('click', cancelGathering);
    window.addEventListener('pagehide', function () { try { window.parent.setMapHiddenForGathering(false); } catch (e) {} if (window.Echo) window.Echo.leave('gathering.map.' + mapId); if (!state.attempt) return; const form = new FormData(); form.append('_token', csrf); navigator.sendBeacon(urls.cancel, form); });
    applyState(initial); positionViewportOnce(); setLog(initial.message || 'Ресурсы доступны. Перемещайте карту и дважды нажмите на кружок, чтобы начать добычу.');
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', subscribeToGatheringMap, { once: true }); else subscribeToGatheringMap();
})();
</script>
</body>
</html>
