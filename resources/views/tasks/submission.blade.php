@extends('layouts.app-master')

@push('css')
    <script src="{{ asset('assets/js/tailwindcss-cdn.js') }}"></script>
<style>
    :root {
        --primary: #012341;
        --primary-light: #d0e4f4;
        --primary-mid: #1a4a72;
        --accent: #0369a1;
        --red-fail: #dc2626;
        --border: #e2e8f0;
        --text-main: #1a202c;
        --text-muted: #718096;
        --bg-soft: #f7f9fc;
    }

    .app-shell {
        margin: 0 auto;
        min-height: 100vh;
        background: #fff;
        display: flex;
        flex-direction: column;
    }

    /* ── TOP BAR ── */
    .top-bar {
        background: var(--primary);
        padding: 14px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        position: sticky;
        top: 0;
        z-index: 50;
        box-shadow: 0 2px 8px rgba(1,35,65,.25);
    }
    .back-btn {
        width: 36px; height: 36px;
        border-radius: 50%;
        border: 1.5px solid rgba(255,255,255,.3);
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; background: rgba(255,255,255,.1);
        transition: background .15s; flex-shrink: 0;
        color: #fff;
    }
    .back-btn:hover { background: rgba(255,255,255,.2); }
    .top-bar-title {
        font-size: 16px; font-weight: 700; color: #fff;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        flex: 1;
    }
    .top-bar-meta {
        font-size: 12px; color: rgba(255,255,255,.7); white-space: nowrap;
    }

    /* ── PROGRESS ── */
    .progress-bar-wrap {
        background: #fff;
        padding: 12px 20px 0;
        border-bottom: 1px solid var(--border);
    }
    .progress-meta {
        display: flex; justify-content: space-between;
        font-size: 12px; color: var(--text-muted);
        margin-bottom: 8px; font-weight: 500;
    }
    .progress-track {
        height: 5px; background: #e2e8f0;
        border-radius: 10px; overflow: hidden;
    }
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--primary), var(--accent));
        border-radius: 10px;
        transition: width .5s cubic-bezier(.4,0,.2,1);
    }

    /* ── PAGE DOTS ── */
    .page-dots {
        display: flex; gap: 6px;
        padding: 10px 20px 12px;
        justify-content: center;
        flex-wrap: wrap;
        background: #fff;
        border-bottom: 1px solid var(--border);
    }
    .page-dot {
        height: 7px; border-radius: 10px;
        background: #e2e8f0; transition: all .3s;
        cursor: pointer;
    }
    .page-dot.active { background: var(--primary); width: 22px; }
    .page-dot.done { background: #86c8f5; }
    .page-dot:not(.active):not(.done) { width: 7px; }

    /* ── FORM BODY ── */
    .form-body { flex: 1; padding: 0 0 100px; overflow-y: auto; }

    /* ── PAGE HEADER ── */
    .page-header {
        padding: 16px 20px 8px;
        display: flex; align-items: center; gap: 10px;
        background: var(--bg-soft);
        border-bottom: 1px solid var(--border);
    }
    .page-badge {
        background: var(--primary); color: #fff;
        font-size: 12px; font-weight: 700;
        padding: 4px 12px; border-radius: 20px;
        letter-spacing: .04em;
    }
    .page-title-lbl { font-size: 13px; color: var(--text-muted); font-weight: 500; }

    /* ── QUESTION CARD ── */
    .question-card {
        border-bottom: 1px solid #eef0f3;
        padding: 20px 20px 16px;
        background: #fff; transition: background .15s;
    }
    .question-card:last-child { border-bottom: none; }

    .card-divider { height: 8px; background: var(--bg-soft); }

    .question-index {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 11px; font-weight: 600;
        color: var(--primary); background: var(--primary-light);
        padding: 2px 10px; border-radius: 20px;
        margin-bottom: 10px; letter-spacing: .04em;
        text-transform: uppercase;
    }
    .question-label {
        font-size: 15px; font-weight: 600;
        color: #2d3748; line-height: 1.5; margin-bottom: 14px;
    }
    .required-star { color: var(--red-fail); margin-left: 3px; }

    /* ── FIELD: HEADER / PARAGRAPH ── */
    .fb-header {
        font-size: 18px; font-weight: 700;
        color: var(--primary); margin-bottom: 4px;
        border-bottom: 2px solid var(--primary-light);
        padding-bottom: 6px;
    }
    .fb-header.h2 { font-size: 16px; }
    .fb-header.h3 { font-size: 14px; }
    .fb-paragraph {
        font-size: 14px; color: #4a5568; line-height: 1.6;
    }

    /* ── FIELD: RADIO-GROUP ── */
    .radio-options { display: flex; flex-direction: column; gap: 10px; }
    .radio-option {
        display: flex; align-items: center;
        gap: 14px; cursor: pointer; padding: 2px 0;
    }
    .radio-custom {
        width: 22px; height: 22px; border-radius: 50%;
        border: 2px solid #cbd5e0;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; transition: border-color .2s;
    }
    .radio-inner {
        width: 10px; height: 10px; border-radius: 50%;
        background: var(--primary); display: none;
    }
    .radio-option.selected .radio-custom { border-color: var(--primary); }
    .radio-option.selected .radio-custom .radio-inner { display: block; }
    .radio-option.fail-selected .radio-custom { border-color: var(--red-fail); }
    .radio-option.fail-selected .radio-custom .radio-inner { background: var(--red-fail); display: block; }
    .radio-option-text { font-size: 15px; font-weight: 500; color: #4a5568; transition: color .15s; }
    .radio-option.selected .radio-option-text { color: var(--primary); font-weight: 600; }
    .radio-option.fail-selected .radio-option-text { color: var(--red-fail); font-weight: 600; }

    /* ── FIELD: CHECKBOX-GROUP ── */
    .checkbox-options { display: flex; flex-direction: column; gap: 10px; }
    .checkbox-option {
        display: flex; align-items: center;
        gap: 12px; cursor: pointer; padding: 2px 0;
    }
    .checkbox-custom {
        width: 20px; height: 20px; border-radius: 5px;
        border: 2px solid #cbd5e0;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; transition: all .2s; background: #fff;
    }
    .checkbox-option.checked .checkbox-custom {
        background: var(--primary); border-color: var(--primary);
    }
    .checkbox-option.checked .checkbox-custom::after {
        content: ''; display: block;
        width: 5px; height: 9px;
        border: 2px solid #fff; border-top: none; border-left: none;
        transform: rotate(45deg) translate(-1px, -1px);
    }
    .checkbox-option-text { font-size: 15px; font-weight: 500; color: #4a5568; }
    .checkbox-option.checked .checkbox-option-text { color: var(--primary); font-weight: 600; }

    /* ── FIELD: TEXT / NUMBER / EMAIL / etc ── */
    .fb-input {
        width: 100%; border: 1.5px solid var(--border);
        border-radius: 10px; padding: 11px 14px;
        font-size: 14px; color: #2d3748;
        outline: none; transition: border-color .2s, box-shadow .2s;
        background: var(--bg-soft); font-family: inherit;
    }
    .fb-input:focus {
        border-color: var(--accent); background: #fff;
        box-shadow: 0 0 0 3px rgba(3,105,161,.1);
    }
    .fb-input::placeholder { color: #a0aec0; }
    .fb-input.input-error { border-color: var(--red-fail); }

    /* ── FIELD: TEXTAREA ── */
    .fb-textarea {
        width: 100%; border: 1.5px solid var(--border);
        border-radius: 10px; padding: 11px 14px;
        font-size: 14px; color: #2d3748;
        outline: none; transition: border-color .2s, box-shadow .2s;
        background: var(--bg-soft); font-family: inherit;
        resize: vertical; min-height: 80px;
    }
    .fb-textarea:focus {
        border-color: var(--accent); background: #fff;
        box-shadow: 0 0 0 3px rgba(3,105,161,.1);
    }
    .fb-textarea::placeholder { color: #a0aec0; }
    .fb-textarea.input-error { border-color: var(--red-fail); }

    /* ── FIELD: SELECT ── */
    .fb-select {
        width: 100%; border: 1.5px solid var(--border);
        border-radius: 10px; padding: 11px 40px 11px 14px;
        font-size: 14px; color: #2d3748;
        outline: none; transition: border-color .2s, box-shadow .2s;
        background: var(--bg-soft) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23718096' stroke-width='2' viewBox='0 0 24 24'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E") no-repeat right 14px center;
        -webkit-appearance: none; appearance: none;
        font-family: inherit; cursor: pointer;
    }
    .fb-select:focus {
        border-color: var(--accent); background-color: #fff;
        box-shadow: 0 0 0 3px rgba(3,105,161,.1);
    }
    .fb-select.input-error { border-color: var(--red-fail); }

    /* ── FIELD: DATE ── */
    .fb-date {
        width: 100%; border: 1.5px solid var(--border);
        border-radius: 10px; padding: 11px 14px;
        font-size: 14px; color: #2d3748;
        outline: none; transition: border-color .2s, box-shadow .2s;
        background: var(--bg-soft); font-family: inherit;
    }
    .fb-date:focus {
        border-color: var(--accent); background: #fff;
        box-shadow: 0 0 0 3px rgba(3,105,161,.1);
    }
    .fb-date.input-error { border-color: var(--red-fail); }

    /* ── FIELD: FILE UPLOAD ── */
    .upload-section { margin-top: 4px; }
    .upload-label-txt {
        font-size: 13px; font-weight: 600; color: #4a5568;
        margin-bottom: 8px;
        display: flex; align-items: center; gap: 6px;
    }
    .upload-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 18px;
        background: var(--primary-light);
        border: 1.5px dashed #7ab8e8;
        border-radius: 12px;
        color: var(--primary); font-size: 14px; font-weight: 600;
        cursor: pointer; transition: all .2s;
        font-family: inherit; width: 100%; justify-content: center;
        height: 50px;
    }
    .upload-btn:hover { background: #b8d9f0; border-color: var(--accent); }
    .upload-btn.na-mode { opacity: 0.4; cursor: not-allowed; pointer-events: none; }
    .upload-btn.disabled { opacity: 0.4; cursor: not-allowed; pointer-events: none; }
    .photo-previews { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
    .photo-thumb {
        position: relative; width: 72px; height: 72px;
        border-radius: 10px; overflow: hidden;
        border: 2px solid var(--border);
    }
    .photo-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .photo-remove {
        position: absolute; top: 2px; right: 2px;
        width: 18px; height: 18px;
        background: rgba(0,0,0,.6); border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; color: #fff; font-size: 11px;
    }

    /* ── FIELD: STAR RATING ── */
    .star-rating { display: flex; gap: 6px; }
    .star-item {
        font-size: 28px; cursor: pointer; color: #cbd5e0;
        transition: color .15s, transform .1s;
        user-select: none;
    }
    .star-item:hover, .star-item.active { color: #f6ad55; }
    .star-item:hover { transform: scale(1.15); }

    /* ── FIELD: BUTTON ── */
    .fb-button {
        padding: 10px 22px; border-radius: 10px;
        font-size: 14px; font-weight: 600; font-family: inherit;
        cursor: pointer; border: none; transition: opacity .2s;
        background: var(--primary); color: #fff;
    }
    .fb-button:hover { opacity: .85; }
    .fb-button.btn-danger { background: var(--red-fail); }
    .fb-button.btn-default { background: #6b7280; }
    .fb-button.btn-success { background: #16a34a; }
    .fb-button.btn-warning { background: #d97706; }

    /* ── FIELD: NUMBER ── */
    .number-wrap { position: relative; display: flex; align-items: center; }
    .number-wrap .fb-input { padding-right: 40px; }
    .number-spin {
        position: absolute; right: 0;
        display: flex; flex-direction: column;
        height: 100%; border-left: 1px solid var(--border);
    }
    .spin-btn {
        flex: 1; width: 36px; display: flex; align-items: center; justify-content: center;
        cursor: pointer; color: var(--text-muted); font-size: 10px;
        transition: background .15s; user-select: none;
    }
    .spin-btn:hover { background: var(--primary-light); color: var(--primary); }
    .spin-btn:first-child { border-bottom: 1px solid var(--border); border-radius: 0 10px 0 0; }
    .spin-btn:last-child { border-radius: 0 0 10px 0; }

    /* ── FIELD LABEL ROW (shared) ── */
    .field-label-row {
        font-size: 13px; font-weight: 600; color: #4a5568;
        margin-bottom: 6px; display: flex; align-items: center; gap: 4px;
    }

    /* ── VALIDATION ERROR ── */
    .field-error {
        font-size: 12px; color: var(--red-fail);
        margin-top: 5px; display: none; font-weight: 500;
        align-items: center; gap: 4px;
    }
    .field-error.show { display: flex; }
    .field-error svg { flex-shrink: 0; }

    /* ── NA DIMMER ── */
    .na-dimmed { opacity: 0.42; pointer-events: none; transition: opacity .2s; }

    /* ── BOTTOM NAV ── */
    .bottom-nav {
        /* position: fixed; bottom: 0; left: 50%;
        transform: translateX(-50%);
        width: 100%;
        background: #fff;
        border-top: 1.5px solid var(--border);
        padding: 12px 20px; */
        display: flex; gap: 12px; z-index: 50;
    }
    .btn-nav {
        flex: 1; padding: 13px 0; border-radius: 12px;
        font-size: 15px; font-weight: 700; font-family: inherit;
        cursor: pointer; border: none; transition: all .2s;
        display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .btn-prev { background: var(--primary-light); color: var(--primary); border: 1.5px solid #90c4e8; }
    .btn-prev:hover { background: #b8d9f0; }
    .btn-next { background: var(--primary); color: #fff; box-shadow: 0 4px 14px rgba(1,35,65,.3); }
    .btn-next:hover { background: var(--primary-mid); }
    .btn-submit { background: #16a34a; color: #fff; box-shadow: 0 4px 14px rgba(22,163,74,.3); flex: 2; }
    .btn-submit:hover { background: #15803d; }

    /* ── TOAST ── */
    .toast {
        position: fixed; bottom: 90px; left: 50%;
        transform: translateX(-50%) translateY(20px);
        background: #1e293b; color: #fff;
        padding: 11px 22px; border-radius: 30px;
        font-size: 14px; font-weight: 500;
        opacity: 0; pointer-events: none;
        transition: all .3s; z-index: 999;
        white-space: nowrap; max-width: 90vw; text-align: center;
    }
    .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }

    /* ── MODAL ── */
    .modal-overlay {
        position: fixed; inset: 0;
        background: rgba(0,0,0,.5); z-index: 200;
        display: flex; align-items: flex-end; justify-content: center;
        opacity: 0; pointer-events: none; transition: opacity .3s;
    }
    .modal-overlay.open { opacity: 1; pointer-events: all; }
    .modal-card {
        background: #fff; border-radius: 24px 24px 0 0;
        padding: 28px 24px 40px;
        width: 100%; transform: translateY(100%);
        transition: transform .35s cubic-bezier(.4,0,.2,1);
    }
    .modal-overlay.open .modal-card { transform: translateY(0); }
    .modal-icon {
        width: 64px; height: 64px; border-radius: 50%;
        background: #dcfce7;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 16px;
    }
    .modal-title { text-align: center; font-size: 20px; font-weight: 700; color: #16a34a; margin-bottom: 6px; }
    .modal-sub { text-align: center; font-size: 14px; color: var(--text-muted); margin-bottom: 18px; }
    .json-output {
        background: #0f172a; color: #86efac;
        font-family: 'Courier New', monospace; font-size: 11px;
        border-radius: 12px; padding: 14px;
        max-height: 200px; overflow-y: auto;
        white-space: pre; margin-bottom: 18px;
    }
    .btn-close-modal {
        width: 100%; padding: 14px;
        background: var(--primary); color: #fff;
        border: none; border-radius: 12px;
        font-size: 15px; font-weight: 700; font-family: inherit;
        cursor: pointer;
    }

    /* ── AUTOCOMPLETE ── */
    .autocomplete-wrap { position: relative; }
    .autocomplete-list {
        position: absolute; top: calc(100% + 4px); left: 0; right: 0;
        background: #fff; border: 1.5px solid var(--border);
        border-radius: 10px; z-index: 100;
        max-height: 180px; overflow-y: auto;
        box-shadow: 0 4px 16px rgba(0,0,0,.1);
        display: none;
    }
    .autocomplete-list.open { display: block; }
    .autocomplete-item {
        padding: 10px 14px; font-size: 14px; cursor: pointer;
        transition: background .1s; color: #2d3748;
    }
    .autocomplete-item:hover { background: var(--primary-light); color: var(--primary); }
</style>
@endpush

@section('content')
<div class="app-shell">
    <!-- Top Bar -->
    <div class="top-bar">
        <button class="back-btn" onclick="goBack()" title="Back">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M19 12H5M12 5l-7 7 7 7"/>
            </svg>
        </button>
        <div class="top-bar-title" id="topBarTitle">Inspection Checklist</div>
        <div class="top-bar-meta" id="topBarMeta"></div>
    </div>

    <!-- Progress -->
    <div class="progress-bar-wrap">
        <div class="progress-meta">
            <span id="progressLabel">Question 0 of 0</span>
            <span id="progressPct">0%</span>
        </div>
        <div class="progress-track">
            <div class="progress-fill" id="progressFill" style="width:0%"></div>
        </div>
    </div>

    <!-- Page dots -->
    <div class="page-dots" id="pageDots"></div>

    <!-- Form body -->
    <div class="form-body" id="formBody"></div>
</div>

<!-- Bottom nav -->
<div class="bottom-nav" id="bottomNav">
    <button class="btn-nav btn-prev" id="btnPrev" onclick="prevPage()">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="M19 12H5M12 5l-7 7 7 7"/>
        </svg>
        Previous
    </button>
    <button class="btn-nav btn-next" id="btnNext" onclick="nextPage()">
        Next
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="M5 12h14M12 5l7 7-7 7"/>
        </svg>
    </button>
</div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<!-- Modal -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal-card">
        <div class="modal-icon">
            <svg width="32" height="32" fill="none" stroke="#16a34a" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M20 6L9 17l-5-5"/>
            </svg>
        </div>
        <div class="modal-title">Inspection Submitted!</div>
        <div class="modal-sub">All responses have been recorded successfully.</div>
        <div class="json-output" id="jsonOutput"></div>
        <button class="btn-close-modal" onclick="closeModal()">Done</button>
    </div>
</div>
@endsection

@push('js')
<script>
const FORM_DATA = @json($task->parent->parent->checklist->schema);

const STORAGE_KEY = 'inspection_form_{{ $task->id ?? "default" }}';
let currentPage = 0;
let answers    = {};
let fileStore  = {};
let pagesDone  = new Set();

function loadState() {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (!raw) return;
        const s = JSON.parse(raw);
        currentPage = s.currentPage || 0;
        answers     = s.answers     || {};
        pagesDone   = new Set(s.pagesDone || []);
    } catch(e) { console.warn('loadState error', e); }
}

function saveState() {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify({
            currentPage,
            answers,
            pagesDone: [...pagesDone]
        }));
    } catch(e) { console.warn('saveState error', e); }
}

function groupByClass(fields) {
    const map = new Map();
    fields.forEach(f => {
        if (!map.has(f.className)) map.set(f.className, []);
        map.get(f.className).push(f);
    });
    return [...map.values()];
}

function isStandalone(f) {
    return ['header','paragraph','hidden','button'].includes(f.type);
}

function totalQuestions() {
    let n = 0;
    FORM_DATA.forEach(page => {
        groupByClass(page).forEach(grp => {
            const lead = grp[0];
            if (!isStandalone(lead)) n++;
        });
    });
    return n;
}

function answeredQuestions() {
    let count = 0;
    FORM_DATA.forEach(page => {
        groupByClass(page).forEach(grp => {
            const lead = grp.find(f => !isStandalone(f));
            if (!lead) return;
            const val = answers[lead.name];
            if (val !== undefined && val !== '' && val !== null) {
                if (Array.isArray(val) && val.length === 0) return;
                count++;
            }
        });
    });
    return count;
}

function esc(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g,'&amp;')
        .replace(/</g,'&lt;')
        .replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;')
        .replace(/'/g,'&#39;');
}

function showToast(msg, duration = 2800) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    clearTimeout(t._timer);
    t._timer = setTimeout(() => t.classList.remove('show'), duration);
}

function errorHTML(id, msg) {
    return `<div class="field-error" id="err-${id}">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
        ${msg || 'This field is required.'}
    </div>`;
}

function render() {
    renderPageDots();
    renderNav();
    renderForm();
    updateProgress();
    saveState();
}

function renderPageDots() {
    const el = document.getElementById('pageDots');
    el.innerHTML = FORM_DATA.map((_, i) => {
        let cls = 'page-dot';
        if (i === currentPage) cls += ' active';
        else if (pagesDone.has(i)) cls += ' done';
        return `<div class="${cls}" onclick="jumpPage(${i})" title="Page ${i+1}"></div>`;
    }).join('');
}

function renderNav() {
    const btnPrev = document.getElementById('btnPrev');
    const btnNext = document.getElementById('btnNext');
    const isLast  = currentPage === FORM_DATA.length - 1;

    btnPrev.style.display = currentPage === 0 ? 'none' : 'flex';

    btnNext.className = `btn-nav ${isLast ? 'btn-submit' : 'btn-next'}`;
    btnNext.innerHTML = isLast
        ? `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg> Submit`
        : `Next <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>`;

    document.getElementById('topBarTitle').textContent =
        `Page ${currentPage+1} of ${FORM_DATA.length} — Inspection`;
    document.getElementById('topBarMeta').textContent =
        `${answeredQuestions()}/${totalQuestions()} answered`;
}

function updateProgress() {
    const total = totalQuestions();
    const done  = answeredQuestions();
    const pct   = total ? Math.round(done / total * 100) : 0;
    document.getElementById('progressLabel').textContent = `Question ${done} of ${total}`;
    document.getElementById('progressPct').textContent   = pct + '%';
    document.getElementById('progressFill').style.width  = pct + '%';
    document.getElementById('topBarMeta').textContent    = `${done}/${total} answered`;
}

function renderForm() {
    const body   = document.getElementById('formBody');
    const page   = FORM_DATA[currentPage];
    const groups = groupByClass(page);

    const answerableCount = groups.filter(g => !isStandalone(g[0])).length;

    let html = `<div class="page-header">
        <span class="page-badge">Page ${currentPage+1}</span>
        <span class="page-title-lbl">${answerableCount} question${answerableCount !== 1 ? 's' : ''} on this page</span>
    </div>`;

    let qNum = getPageStartIndex();

    groups.forEach((grp, gIdx) => {
        const isAlone = isStandalone(grp[0]) && grp.length === 1;

        const radioLead = grp.find(f => f.type === 'radio-group');
        const naVal = radioLead ? (answers[radioLead.name] ?? null) : null;
        const isNA  = naVal === 'na';

        if (isAlone) {
            html += renderStandaloneField(grp[0]);
        } else {
            html += `<div class="question-card" id="card-${esc(grp[0].className)}">`;

            let cardQNum = null;
            grp.forEach(field => {
                if (!isStandalone(field)) {
                    if (cardQNum === null) {
                        cardQNum = ++qNum;
                        html += `<div class="question-index">Q${cardQNum}</div>`;
                        const lead = grp.find(f => !isStandalone(f));
                        if (lead) {
                            const req = lead.required;
                            html += `<div class="question-label">${esc(lead.label || '')}${req ? '<span class="required-star">*</span>' : ''}</div>`;
                        }
                    }
                    html += renderField(field, isNA);
                }
            });

            html += `</div>`;
        }

        if (gIdx < groups.length - 1) html += `<div class="card-divider"></div>`;
    });

    body.innerHTML = html;
    body.scrollTop = 0;

    bindDynamicEvents();
}

function getPageStartIndex() {
    let n = 0;
    for (let i = 0; i < currentPage; i++) {
        groupByClass(FORM_DATA[i]).forEach(grp => {
            if (!isStandalone(grp[0])) n++;
        });
    }
    return n;
}

function renderStandaloneField(f) {
    switch(f.type) {
        case 'header': {
            const tag = f.subtype || 'h2';
            const cls = tag === 'h1' ? '' : tag === 'h3' ? 'h3' : 'h2';
            return `<div class="question-card"><div class="fb-header ${cls}">${esc(f.label)}</div></div>`;
        }
        case 'paragraph':
            return `<div class="question-card"><p class="fb-paragraph">${f.label || ''}</p></div>`;
        case 'hidden':
            return `<input type="hidden" name="${esc(f.name)}" value="${esc(f.value || '')}">`;
        case 'button':
            return `<div class="question-card"><button class="fb-button btn-${esc(f.subtype||'default')}" type="button">${esc(f.label)}</button></div>`;
        default:
            return '';
    }
}

function renderField(f, isNA) {
    switch(f.type) {
        case 'radio-group':    return renderRadioGroup(f);
        case 'checkbox-group': return renderCheckboxGroup(f, isNA);
        case 'select':         return renderSelect(f, isNA);
        case 'file':           return renderFileUpload(f, isNA);
        case 'textarea':       return renderTextarea(f, isNA);
        case 'text':           return renderTextInput(f, isNA);
        case 'number':         return renderNumber(f, isNA);
        case 'date':           return renderDate(f, isNA);
        case 'hidden':         return `<input type="hidden" name="${esc(f.name)}" value="${esc(f.value||'')}">`;
        case 'header':         return `<div class="fb-header h3">${esc(f.label)}</div>`;
        case 'paragraph':      return `<p class="fb-paragraph">${f.label || ''}</p>`;
        case 'autocomplete':   return renderAutocomplete(f, isNA);
        case 'starRating':     return renderStarRating(f, isNA);
        case 'button':         return `<button class="fb-button btn-${esc(f.subtype||'default')}" type="button">${esc(f.label)}</button>`;
        default:               return renderTextInput(f, isNA); // fallback
    }
}

function renderRadioGroup(f) {
    const selVal = answers[f.name] ?? null;
    let html = `<div class="radio-options" id="radio-${esc(f.name)}">`;
    (f.values || []).forEach(opt => {
        const isSel  = selVal === opt.value;
        const isFail = opt.value === '0' && isSel;
        html += `<label class="radio-option ${isSel ? (isFail ? 'fail-selected' : 'selected') : ''}"
            onclick="selectRadio('${esc(f.name)}','${esc(opt.value)}','${esc(opt.label)}')">
            <div class="radio-custom"><div class="radio-inner"></div></div>
            <span class="radio-option-text">${esc(opt.label)}</span>
        </label>`;
    });
    html += `</div>${errorHTML(f.name, 'Please select an option.')}`;
    return html;
}

function renderCheckboxGroup(f, isNA) {
    const selVals = answers[f.name] || [];
    const dimCls  = isNA ? 'na-dimmed' : '';
    let html = `<div class="checkbox-options ${dimCls}" id="chk-${esc(f.name)}">`;
    (f.values || []).forEach((opt, oi) => {
        const isChecked = selVals.includes(opt.value);
        html += `<label class="checkbox-option ${isChecked ? 'checked' : ''}"
            onclick="toggleCheckbox('${esc(f.name)}','${esc(opt.value)}')">
            <div class="checkbox-custom"></div>
            <span class="checkbox-option-text">${esc(opt.label)}</span>
        </label>`;
    });
    html += `</div>${errorHTML(f.name, 'Please select at least one option.')}`;
    return html;
}

function renderSelect(f, isNA) {
    const val    = answers[f.name] ?? '';
    const dimCls = isNA ? 'na-dimmed' : '';
    let html = `<div class="${dimCls}" id="sel-wrap-${esc(f.name)}">
        <select class="fb-select" id="${esc(f.name)}"
            onchange="setAnswer('${esc(f.name)}', this.value, this.options[this.selectedIndex].text)">
            <option value="">-- Select an option --</option>`;
    (f.values || []).forEach(opt => {
        html += `<option value="${esc(opt.value)}" ${val === opt.value ? 'selected' : ''}>${esc(opt.label)}</option>`;
    });
    html += `</select>${errorHTML(f.name, 'Please select an option.')}</div>`;
    return html;
}

function renderTextarea(f, isNA) {
    const val    = answers[f.name] ?? '';
    const dimCls = isNA ? 'na-dimmed' : '';
    const rows   = f.rows || 4;
    const ph     = f.placeholder || 'Add remarks here...';
    return `<div class="${dimCls}" id="ta-wrap-${esc(f.name)}">
        <textarea class="fb-textarea" id="${esc(f.name)}" rows="${rows}"
            placeholder="${esc(ph)}"
            onchange="setAnswer('${esc(f.name)}', this.value)">${esc(val)}</textarea>
        ${errorHTML(f.name)}
    </div>`;
}

function renderTextInput(f, isNA) {
    const val    = answers[f.name] ?? '';
    const dimCls = isNA ? 'na-dimmed' : '';
    const type   = f.subtype || 'text';
    const ph     = f.placeholder || '';
    const maxLen = f.maxlength ? `maxlength="${f.maxlength}"` : '';
    const minLen = f.minlength ? `minlength="${f.minlength}"` : '';
    const pattern= f.pattern   ? `pattern="${esc(f.pattern)}"` : '';
    return `<div class="${dimCls}" id="inp-wrap-${esc(f.name)}">
        <input type="${type}" class="fb-input" id="${esc(f.name)}"
            value="${esc(val)}" placeholder="${esc(ph)}"
            ${maxLen} ${minLen} ${pattern}
            oninput="setAnswer('${esc(f.name)}', this.value)">
        ${errorHTML(f.name)}
    </div>`;
}

function renderNumber(f, isNA) {
    const val    = answers[f.name] ?? (f.value || '');
    const dimCls = isNA ? 'na-dimmed' : '';
    const min    = f.min !== undefined ? `min="${f.min}"` : '';
    const max    = f.max !== undefined ? `max="${f.max}"` : '';
    const step   = f.step ? `step="${f.step}"` : '';
    const ph     = f.placeholder || '0';
    return `<div class="${dimCls}" id="num-wrap-${esc(f.name)}">
        <div class="number-wrap">
            <input type="number" class="fb-input" id="${esc(f.name)}"
                value="${esc(val)}" placeholder="${esc(ph)}"
                ${min} ${max} ${step}
                oninput="setAnswer('${esc(f.name)}', this.value)">
            <div class="number-spin">
                <div class="spin-btn" onclick="spinNumber('${esc(f.name)}',1)">▲</div>
                <div class="spin-btn" onclick="spinNumber('${esc(f.name)}',-1)">▼</div>
            </div>
        </div>
        ${errorHTML(f.name)}
    </div>`;
}

function renderDate(f, isNA) {
    const val    = answers[f.name] ?? '';
    const dimCls = isNA ? 'na-dimmed' : '';
    const min    = f.min ? `min="${f.min}"` : '';
    const max    = f.max ? `max="${f.max}"` : '';
    return `<div class="${dimCls}" id="date-wrap-${esc(f.name)}">
        <input type="date" class="fb-date" id="${esc(f.name)}"
            value="${esc(val)}" ${min} ${max}
            onchange="setAnswer('${esc(f.name)}', this.value)">
        ${errorHTML(f.name)}
    </div>`;
}

function renderFileUpload(f, isNA) {
    const files  = fileStore[f.name] || [];
    const naMode = isNA ? 'na-mode' : '';
    const dimCls = isNA ? 'na-dimmed' : '';
    let html = `<div class="upload-section ${dimCls}" id="upload-section-${esc(f.name)}">
        <div class="upload-label-txt">
            <svg width="15" height="15" fill="none" stroke="#4a5568" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/>
            </svg>
            ${esc(f.label)}
        </div>
        <button class="upload-btn ${naMode}" onclick="triggerUpload('${esc(f.name)}')" type="button">
            <svg fill="none" stroke="currentColor" height="20" stroke-width="2" viewBox="0 0 24 24">
                <path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/>
                <circle cx="12" cy="13" r="4"/>
            </svg>
            Upload Image
        </button>
        <input type="file" id="file-input-${esc(f.name)}" accept="image/*"
            ${f.multiple ? 'multiple' : ''} style="display:none"
            onchange="handleFileChange('${esc(f.name)}', this)">
        <div class="photo-previews" id="previews-${esc(f.name)}">`;
    files.forEach((file, fi) => {
        html += `<div class="photo-thumb">
            <img src="${file.dataUrl}" alt="${esc(file.name)}">
            <div class="photo-remove" onclick="removeFile('${esc(f.name)}',${fi})">✕</div>
        </div>`;
    });
    html += `</div></div>`;
    return html;
}

function renderAutocomplete(f, isNA) {
    const val    = answers[f.name] ?? '';
    const dimCls = isNA ? 'na-dimmed' : '';
    const ph     = f.placeholder || 'Type to search...';
    const opts   = (f.values || []).map(o => `"${esc(o.label)}"`).join(',');
    return `<div class="${dimCls} autocomplete-wrap" id="ac-wrap-${esc(f.name)}">
        <input type="text" class="fb-input" id="${esc(f.name)}"
            value="${esc(val)}" placeholder="${esc(ph)}" autocomplete="off"
            oninput="filterAutocomplete('${esc(f.name)}', this.value, [${opts}])"
            onblur="hideAutocomplete('${esc(f.name)}')">
        <div class="autocomplete-list" id="ac-list-${esc(f.name)}"></div>
        ${errorHTML(f.name)}
    </div>`;
}

function renderStarRating(f, isNA) {
    const val    = parseInt(answers[f.name] || 0);
    const max    = parseInt(f.maxStars || f.max || 5);
    const dimCls = isNA ? 'na-dimmed' : '';
    let html = `<div class="${dimCls}" id="star-wrap-${esc(f.name)}">
        <div class="star-rating" id="stars-${esc(f.name)}">`;
    for (let i = 1; i <= max; i++) {
        html += `<span class="star-item ${i <= val ? 'active' : ''}"
            onclick="selectStar('${esc(f.name)}',${i},${max})"
            onmouseover="hoverStar('${esc(f.name)}',${i},${max})"
            onmouseout="resetStarHover('${esc(f.name)}',${max})">★</span>`;
    }
    html += `</div>${errorHTML(f.name)}</div>`;
    return html;
}

function selectRadio(name, value, label) {
    answers[name]           = value;
    answers[name + '_label']= label;

    hideError(name);

    const page   = FORM_DATA[currentPage];
    const groups = groupByClass(page);
    const grp    = groups.find(g => g.some(f => f.name === name));
    if (!grp) return;

    const card = document.getElementById('card-' + grp[0].className);
    if (card) {
        card.querySelectorAll('.radio-option').forEach(el => {
            el.classList.remove('selected','fail-selected');
        });
        card.querySelectorAll('.radio-option').forEach(el => {
            const txt = el.querySelector('.radio-option-text');
            if (txt && txt.textContent.trim() === label.trim()) {
                el.classList.add(value === '0' ? 'fail-selected' : 'selected');
            }
        });

        const isNA = value === 'na';
        grp.forEach(sibField => {
            if (sibField.type === 'file') {
                const sec = document.getElementById('upload-section-' + sibField.name);
                const btn = sec ? sec.querySelector('.upload-btn') : null;
                if (sec) sec.classList.toggle('na-dimmed', isNA);
                if (btn) btn.classList.toggle('na-mode', isNA);
            }
            if (sibField.type === 'textarea') {
                const w = document.getElementById('ta-wrap-' + sibField.name);
                if (w) w.classList.toggle('na-dimmed', isNA);
            }
            if (sibField.type === 'text') {
                const w = document.getElementById('inp-wrap-' + sibField.name);
                if (w) w.classList.toggle('na-dimmed', isNA);
            }
            if (sibField.type === 'checkbox-group') {
                const w = document.getElementById('chk-' + sibField.name);
                if (w) w.classList.toggle('na-dimmed', isNA);
            }
            if (sibField.type === 'select') {
                const w = document.getElementById('sel-wrap-' + sibField.name);
                if (w) w.classList.toggle('na-dimmed', isNA);
            }
            if (sibField.type === 'number') {
                const w = document.getElementById('num-wrap-' + sibField.name);
                if (w) w.classList.toggle('na-dimmed', isNA);
            }
            if (sibField.type === 'date') {
                const w = document.getElementById('date-wrap-' + sibField.name);
                if (w) w.classList.toggle('na-dimmed', isNA);
            }
            if (sibField.type === 'starRating') {
                const w = document.getElementById('star-wrap-' + sibField.name);
                if (w) w.classList.toggle('na-dimmed', isNA);
            }
            if (sibField.type === 'autocomplete') {
                const w = document.getElementById('ac-wrap-' + sibField.name);
                if (w) w.classList.toggle('na-dimmed', isNA);
            }
        });
    }

    updateProgress();
    saveState();
}

function toggleCheckbox(name, value) {
    if (!answers[name] || !Array.isArray(answers[name])) answers[name] = [];
    const idx = answers[name].indexOf(value);
    if (idx === -1) answers[name].push(value);
    else            answers[name].splice(idx, 1);

    hideError(name);

    const container = document.getElementById('chk-' + name);
    if (container) {
        container.querySelectorAll('.checkbox-option').forEach(el => {
            const txt = el.querySelector('.checkbox-option-text');
            if (txt) {
                const page   = FORM_DATA[currentPage];
                const fields = page.flat ? page : page;
                
                let found = null;
                fields.forEach(f => { if (f.name === name) found = f; });
                if (found) {
                    const optLabel = (found.values || []).find(o => {
                        const optEl = container.querySelectorAll('.checkbox-option-text');
                        return true;
                    });
                }
                el.classList.toggle('checked', answers[name].some(v => {
                    const page2 = FORM_DATA[currentPage];
                    let lbl = null;
                    page2.forEach(f => {
                        if (f.name === name) {
                            const opt = (f.values||[]).find(o => o.value === v);
                            if (opt && opt.label === txt.textContent.trim()) lbl = true;
                        }
                    });
                    return !!lbl;
                }));
            }
        });
        reRenderCheckboxes(name);
    }

    updateProgress();
    saveState();
}

function reRenderCheckboxes(name) {
    const container = document.getElementById('chk-' + name);
    if (!container) return;
    const selVals = answers[name] || [];
    const page    = FORM_DATA[currentPage];
    let field = null;
    page.forEach(f => { if (f.name === name) field = f; });
    if (!field) return;
    container.innerHTML = (field.values || []).map(opt => {
        const isChecked = selVals.includes(opt.value);
        return `<label class="checkbox-option ${isChecked ? 'checked' : ''}"
            onclick="toggleCheckbox('${esc(name)}','${esc(opt.value)}')">
            <div class="checkbox-custom"></div>
            <span class="checkbox-option-text">${esc(opt.label)}</span>
        </label>`;
    }).join('');
}

function setAnswer(name, value, label) {
    answers[name] = value;
    if (label !== undefined) answers[name + '_label'] = label;
    hideError(name);
    updateProgress();
    saveState();
}

function spinNumber(name, dir) {
    const el  = document.getElementById(name);
    if (!el) return;
    const cur  = parseFloat(el.value || 0);
    const step = parseFloat(el.step || 1);
    const min  = el.min !== '' ? parseFloat(el.min) : -Infinity;
    const max  = el.max !== '' ? parseFloat(el.max) :  Infinity;
    const next = Math.min(max, Math.max(min, cur + dir * step));
    el.value = next;
    setAnswer(name, String(next));
}

function triggerUpload(name) {
    const el = document.getElementById('file-input-' + name);
    if (el) el.click();
}

function handleFileChange(name, input) {
    if (!fileStore[name]) fileStore[name] = [];
    const files  = Array.from(input.files);
    let loaded   = 0;
    const total  = files.length;
    if (total === 0) return;

    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            fileStore[name].push({ name: file.name, dataUrl: e.target.result, file });
            loaded++;
            if (loaded === total) refreshPreviews(name);
        };
        reader.onerror = () => { loaded++; if (loaded === total) refreshPreviews(name); };
        reader.readAsDataURL(file);
    });
    input.value = '';
}

function refreshPreviews(name) {
    const container = document.getElementById('previews-' + name);
    if (!container) return;
    container.innerHTML = (fileStore[name] || []).map((f, fi) =>
        `<div class="photo-thumb">
            <img src="${f.dataUrl}" alt="${esc(f.name)}">
            <div class="photo-remove" onclick="removeFile('${esc(name)}',${fi})">✕</div>
        </div>`
    ).join('');
}

function removeFile(name, idx) {
    (fileStore[name] || []).splice(idx, 1);
    refreshPreviews(name);
}

function selectStar(name, val, max) {
    answers[name] = val;
    hideError(name);
    updateStars(name, val, max);
    updateProgress();
    saveState();
}
function hoverStar(name, val, max)    { updateStars(name, val, max, true); }
function resetStarHover(name, max)    { updateStars(name, answers[name] || 0, max); }
function updateStars(name, val, max, hover) {
    const container = document.getElementById('stars-' + name);
    if (!container) return;
    container.querySelectorAll('.star-item').forEach((el, i) => {
        el.classList.toggle('active', i < val);
    });
}

function filterAutocomplete(name, query, options) {
    const list = document.getElementById('ac-list-' + name);
    if (!list) return;
    answers[name] = query;
    saveState();
    if (!query.trim()) { list.classList.remove('open'); return; }
    const matches = options.filter(o => o.toLowerCase().includes(query.toLowerCase()));
    if (matches.length === 0) { list.classList.remove('open'); return; }
    list.innerHTML = matches.map(o =>
        `<div class="autocomplete-item" onmousedown="pickAutocomplete('${esc(name)}','${esc(o)}')">${esc(o)}</div>`
    ).join('');
    list.classList.add('open');
}
function pickAutocomplete(name, val) {
    answers[name] = val;
    const inp = document.getElementById(name);
    if (inp) inp.value = val;
    const list = document.getElementById('ac-list-' + name);
    if (list) list.classList.remove('open');
    hideError(name);
    updateProgress();
    saveState();
}
function hideAutocomplete(name) {
    setTimeout(() => {
        const list = document.getElementById('ac-list-' + name);
        if (list) list.classList.remove('open');
    }, 200);
}

function showError(id) {
    const el = document.getElementById('err-' + id);
    if (el) el.classList.add('show');
}
function hideError(id) {
    const el = document.getElementById('err-' + id);
    if (el) el.classList.remove('show');
}
function markInputError(id, show) {
    const el = document.getElementById(id);
    if (el) el.classList.toggle('input-error', show);
}

function bindDynamicEvents() {
    document.querySelectorAll('.fb-textarea').forEach(el => {
        el.addEventListener('input', function() {
            setAnswer(this.id, this.value);
        });
    });
    document.querySelectorAll('.fb-input').forEach(el => {
        el.addEventListener('input', function() {
            setAnswer(this.id, this.value);
        });
    });
    document.querySelectorAll('.fb-date').forEach(el => {
        el.addEventListener('change', function() {
            setAnswer(this.id, this.value);
        });
    });
    document.querySelectorAll('.fb-select').forEach(el => {
        el.addEventListener('change', function() {
            setAnswer(this.id, this.value,
                this.options[this.selectedIndex]?.text || '');
        });
    });
}

function validateCurrentPage() {
    const page   = FORM_DATA[currentPage];
    const groups = groupByClass(page);
    let valid    = true;
    let firstErrField = null;

    groups.forEach(grp => {
        grp.forEach(field => {
            if (isStandalone(field)) return;
            const isNA = (function() {
                const radio = grp.find(f => f.type === 'radio-group');
                return radio && answers[radio.name] === 'na';
            })();
            if (isNA && field.type !== 'radio-group') return;

            if (!field.required) return;

            const val = answers[field.name];
            let fieldValid = true;

            switch(field.type) {
                case 'radio-group':
                    fieldValid = val !== undefined && val !== '' && val !== null;
                    break;
                case 'checkbox-group':
                    fieldValid = Array.isArray(val) && val.length > 0;
                    break;
                case 'file':
                    fieldValid = (fileStore[field.name] || []).length > 0;
                    break;
                case 'select':
                    fieldValid = val !== undefined && val !== '' && val !== null;
                    break;
                case 'starRating':
                    fieldValid = val !== undefined && val > 0;
                    break;
                default:
                    fieldValid = val !== undefined && String(val).trim() !== '';
                    break;
            }

            if (!fieldValid) {
                valid = false;
                showError(field.name);
                markInputError(field.name, true);
                if (!firstErrField) firstErrField = field.name;
            } else {
                hideError(field.name);
                markInputError(field.name, false);
            }
        });
    });

    if (!valid) {
        showToast('⚠️ Please fill in all required fields before proceeding.');
        if (firstErrField) {
            const errEl = document.getElementById('err-' + firstErrField);
            if (errEl) errEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    return valid;
}

function nextPage() {
    if (!validateCurrentPage()) return;
    pagesDone.add(currentPage);
    if (currentPage < FORM_DATA.length - 1) {
        currentPage++;
        render();
        window.scrollTo(0,0);
    } else {
        submitForm();
    }
}

function prevPage() {
    if (currentPage > 0) {
        currentPage--;
        render();
        window.scrollTo(0,0);
    }
}

function jumpPage(i) {
    if (pagesDone.has(i) || i <= currentPage) {
        currentPage = i;
        render();
        window.scrollTo(0,0);
    } else {
        showToast('Please complete the current page first.');
    }
}

function goBack() {
    if (currentPage > 0) prevPage();
    else {
        if (window.history.length > 1) window.history.back();
        else showToast('You are on the first page.');
    }
}

function submitForm() {
    const result = [];
    let qIndex   = 0;

    FORM_DATA.forEach((page, pIdx) => {
        const groups = groupByClass(page);
        groups.forEach((grp, gIdx) => {
            const hasAnswerable = grp.some(f => !isStandalone(f));
            if (hasAnswerable) qIndex++;

            grp.forEach(field => {
                const isFile = field.type === 'file';

                const entry = {
                    name:      field.name,
                    type:      field.type,
                    page:      String(pIdx + 1),
                    index:     String(qIndex),
                    required:  field.required || false,
                    label:     field.label || '',
                    className: field.className,
                    access:    field.access || false,
                    isFile
                };

                if (isFile) {
                    entry.value    = (fileStore[field.name] || []).map(f => f.name);
                    entry.multiple = field.multiple || false;
                } else if (field.type === 'radio-group') {
                    entry.value       = answers[field.name] ?? '';
                    entry.value_label = answers[field.name + '_label'] ?? '';
                } else if (field.type === 'select') {
                    entry.value       = answers[field.name] ?? '';
                    entry.value_label = answers[field.name + '_label'] ?? '';
                } else if (field.type === 'checkbox-group') {
                    const selVals = answers[field.name] || [];
                    entry.value = selVals;
                    entry.value_labels = selVals.map(v => {
                        const opt = (field.values||[]).find(o => o.value === v);
                        return opt ? opt.label : v;
                    });
                } else if (field.type === 'hidden') {
                    entry.value = field.value || '';
                } else {
                    entry.value = answers[field.name] ?? '';
                }

                result.push(entry);
            });
        });
    });

    document.getElementById('jsonOutput').textContent = JSON.stringify(result, null, 2);
    document.getElementById('modalOverlay').classList.add('open');

    $.ajax({
        url: "{{ route('checklists-submission', $id) }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            data: JSON.stringify(result)
        },
        success: function (response) {
            if (response.status) {
                Swal.fire('Success', 'Form submitted successfully', 'success');
            } else {
                Swal.fire('Error', 'Something went wrong!', 'error');
                return false;
            }
        },
        error: function (xhr) {
            console.error(xhr.responseText);
        },
        complete: function () {

        }
    });

    localStorage.removeItem(STORAGE_KEY);
}

function closeModal() {
    document.getElementById('modalOverlay').classList.remove('open');
    currentPage = 0;
    answers     = {};
    fileStore   = {};
    pagesDone   = new Set();
    render();
}

loadState();
render();
</script>
@endpush