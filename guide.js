// webservice shortcut
const WS = 'webservice.php';
let allConcepts = [];
let currentConceptId = null;

// element references
const grid = document.getElementById('concepts-grid');
const loading = document.getElementById('loading-state');
const empty = document.getElementById('empty-state');
const filterBar = document.getElementById('unit-filter');
const searchInput = document.getElementById('search-input');
const statCount = document.getElementById('stat-count');
const footUnit = document.getElementById('footbar-unit');
const overlay = document.getElementById('detail-overlay');

// loads units and concepts on page load
document.addEventListener('DOMContentLoaded', () => {
    loadUnits();
    loadConcepts();
    document.getElementById('detail-close').addEventListener('click', closeDetail);
    overlay.addEventListener('click', (e) => { if (e.target === overlay) closeDetail(); });
    searchInput.addEventListener('input', filterAndRender);

    if (ROLE === 'student') {
        document.getElementById('get-prompt-btn').addEventListener('click', getPrompt);
        document.getElementById('bookmark-btn').addEventListener('click', addBookmark);
        document.getElementById('save-progress-btn').addEventListener('click', saveProgress);
    }
});

// retrieve and create filter for all units
function loadUnits() {
    fetch(`${WS}?action=get_units`)
        .then(r => r.json())
        .then(units => {
            units.forEach(u => {
                const btn = document.createElement('button');
                btn.className = 'filter-btn';
                btn.dataset.unit = u.id;
                btn.textContent = `Unit ${u.unit_number} · ${u.name}`;
                btn.addEventListener('click', () => selectUnit(btn, u.id, u.name));
                filterBar.appendChild(btn);
            });

            const allBtn = document.querySelector('.filter-btn[data-unit=""]');
            allBtn.addEventListener('click', () => selectUnit(allBtn, '', 'All Units'));
        });
}

// retrieve concepts
function loadConcepts(unitId = '') {
    loading.style.display = 'block';
    grid.style.display = 'none';
    empty.style.display = 'none';

    const url = unitId
        ? `${WS}?action=get_concepts&unit_id=${unitId}`
        : `${WS}?action=get_concepts`;

    fetch(url)
        .then(r => r.json())
        .then(data => {
            allConcepts = data;
            statCount.textContent = data.length;
            loading.style.display = 'none';
            filterAndRender();
        })
        .catch(() => {
            loading.style.display = 'none';
            empty.style.display = 'block';
        });
}

// search based on user input
function filterAndRender() {
    const q = searchInput.value.toLowerCase().trim();
    const filtered = q
        ? allConcepts.filter(c =>
            c.term.toLowerCase().includes(q) ||
            c.definition.toLowerCase().includes(q) ||
            c.unit_name.toLowerCase().includes(q)
          )
        : allConcepts;

    statCount.textContent = filtered.length;

    if (filtered.length === 0) {
        grid.style.display = 'none';
        empty.style.display = 'block';
        return;
    }

    empty.style.display = 'none';
    grid.style.display = 'grid';
    renderConcepts(filtered);
}

// rebuild grid with given list
function renderConcepts(concepts) {
    grid.innerHTML = '';
    concepts.forEach((c, i) => {
        const num = String(i + 1).padStart(3, '0');
        const card = document.createElement('div');
        card.className = 'concept-card';
        card.innerHTML = `
            <div class="concept-card-num">
                <span>${num}</span>
                <span class="meta-tag">Unit ${c.unit_number}</span>
            </div>
            <h3>${c.term}</h3>
            <p>${c.definition.substring(0, 110)}${c.definition.length > 110 ? '…' : ''}</p>
            <div class="btn-entry" style="cursor:pointer;">View Entry →</div>
        `;
        card.addEventListener('click', () => openDetail(c));
        grid.appendChild(card);
    });
}

// fill in details
function selectUnit(btn, unitId, unitName) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    footUnit.textContent = unitId ? `Unit ${unitId} · ${unitName}` : 'All Units';
    loadConcepts(unitId);
    searchInput.value = '';
}

// open details
function openDetail(concept) {
    currentConceptId = concept.id;

    document.getElementById('detail-unit').textContent = `Unit ${concept.unit_number}`;
    document.getElementById('detail-num').textContent = `· ${concept.unit_name}`;
    document.getElementById('detail-term').textContent = concept.term;
    document.getElementById('detail-unit-name').textContent = concept.unit_name;
    document.getElementById('detail-definition').textContent = concept.definition;

    const exWrap = document.getElementById('detail-example-wrap');
    if (concept.example) {
        document.getElementById('detail-example').textContent = concept.example;
        exWrap.style.display = 'block';
    } else {
        exWrap.style.display = 'none';
    }

    // reset prompt area
    if (ROLE === 'student') {
        document.getElementById('prompt-area').innerHTML =
            `<button class="btn-primary" id="get-prompt-btn" style="width:auto;">Get Practice Prompt →</button>`;
        document.getElementById('get-prompt-btn').addEventListener('click', getPrompt);
        document.getElementById('bookmark-btn').textContent = 'Bookmark Entry';
        document.getElementById('progress-select').value = 'not_started';
    }

    overlay.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
// hide detail
function closeDetail() {
    overlay.style.display = 'none';
    document.body.style.overflow = '';
    currentConceptId = null;
}

// get AI prompt
function getPrompt() {
    if (!currentConceptId) return;
    const area = document.getElementById('prompt-area');
    area.innerHTML = '<p style="font-family:\'Courier Prime\',monospace;font-size:9px;color:#888;letter-spacing:.08em;">Loading prompt...</p>';

    fetch(`${WS}?action=get_prompt&concept_id=${currentConceptId}`)
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                area.innerHTML = `<p class="error-msg">${data.error}</p>`;
                return;
            }
            area.innerHTML = `
                <div class="prompt-box">
                    <p id="prompt-text">${data.prompt}</p>
                    <button class="btn-secondary" id="copy-btn">Copy to Clipboard</button>
                </div>
            `;
            document.getElementById('copy-btn').addEventListener('click', () => {
                navigator.clipboard.writeText(data.prompt).then(() => {
                    document.getElementById('copy-btn').textContent = 'Copied ✓';
                    setTimeout(() => {
                        document.getElementById('copy-btn').textContent = 'Copy to Clipboard';
                    }, 2000);
                });
            });
        });
}

// add bookmark
function addBookmark() {
    if (!currentConceptId) return;
    const btn = document.getElementById('bookmark-btn');
    btn.textContent = 'Saving...';

    fetch(`${WS}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=add_bookmark&concept_id=${currentConceptId}`
    })
    .then(r => r.json())
    .then(data => {
        btn.textContent = data.success ? 'Bookmarked ✓' : (data.message || 'Error');
    });
}

// save progress
function saveProgress() {
    if (!currentConceptId) return;
    const status = document.getElementById('progress-select').value;
    const btn = document.getElementById('save-progress-btn');
    btn.textContent = 'Saving...';

    fetch(`${WS}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update_progress&concept_id=${currentConceptId}&status=${status}`
    })
    .then(r => r.json())
    .then(data => {
        btn.textContent = data.success ? 'Saved ✓' : 'Error';
        setTimeout(() => { btn.textContent = 'Save'; }, 2000);
    });
}

