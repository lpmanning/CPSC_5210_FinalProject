// webservice shortcut
const WS = 'webservice.php';
let allConcepts = [];
let units = [];
let deleteTargetId = null;

// element references
const loading      = document.getElementById('loading-state');
const manageList   = document.getElementById('manage-list');
const emptyState   = document.getElementById('empty-state');
const filterBar    = document.getElementById('unit-filter');
const statCount    = document.getElementById('stat-count');
const modalOverlay = document.getElementById('modal-overlay');
const deleteOverlay= document.getElementById('delete-overlay');

// load units and concepts on page load
document.addEventListener('DOMContentLoaded', () => {
    loadUnits().then(() => loadConcepts());

    document.getElementById('add-btn').addEventListener('click', openAddModal);
    document.getElementById('modal-close').addEventListener('click', closeModal);
    document.getElementById('modal-cancel').addEventListener('click', closeModal);
    document.getElementById('modal-save').addEventListener('click', saveConcept);
    document.getElementById('delete-close').addEventListener('click', closeDeleteModal);
    document.getElementById('cancel-delete-btn').addEventListener('click', closeDeleteModal);
    document.getElementById('confirm-delete-btn').addEventListener('click', confirmDelete);

    modalOverlay.addEventListener('click', e => { if (e.target === modalOverlay) closeModal(); });
    deleteOverlay.addEventListener('click', e => { if (e.target === deleteOverlay) closeDeleteModal(); });
});

// load units
function loadUnits() {
    return fetch(`${WS}?action=get_units`)
        .then(r => r.json())
        .then(data => {
            units = data;

            const allBtn = document.querySelector('.filter-btn[data-unit=""]');
            allBtn.addEventListener('click', () => {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                allBtn.classList.add('active');
                renderConcepts('');
            });

            // populate filter bar
            data.forEach(u => {
                const btn = document.createElement('button');
                btn.className = 'filter-btn';
                btn.dataset.unit = u.id;
                btn.textContent = `Unit ${u.unit_number} · ${u.name}`;
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    renderConcepts(u.id);
                });
                filterBar.appendChild(btn);
            });

            // populate modal select
            const sel = document.getElementById('f-unit');
            data.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.id;
                opt.textContent = `Unit ${u.unit_number} · ${u.name}`;
                sel.appendChild(opt);
            });
        });
}

// load concepts
function loadConcepts(unitId = '') {
    loading.style.display = 'block';
    manageList.style.display = 'none';
    emptyState.style.display = 'none';

    const url = unitId
        ? `${WS}?action=get_concepts&unit_id=${unitId}`
        : `${WS}?action=get_concepts`;

    fetch(url)
        .then(r => r.json())
        .then(data => {
            allConcepts = data;
            statCount.textContent = data.length;
            loading.style.display = 'none';
            renderConcepts(unitId);
        });
}

// create concept table
function renderConcepts(unitId = '') {
    const filtered = unitId
        ? allConcepts.filter(c => c.unit_id == unitId)
        : allConcepts;

    if (!filtered.length) {
        manageList.style.display = 'none';
        emptyState.style.display = 'block';
        return;
    }

    emptyState.style.display = 'none';
    manageList.style.display = 'block';
    manageList.innerHTML = '';

    // group by unit
    const grouped = {};
    filtered.forEach(c => {
        const key = `Unit ${c.unit_number} · ${c.unit_name}`;
        if (!grouped[key]) grouped[key] = [];
        grouped[key].push(c);
    });

    Object.entries(grouped).forEach(([unitLabel, concepts]) => {
        const section = document.createElement('div');
        section.innerHTML = `<div class="section-label">${unitLabel} <span style="margin-left:auto;font-size:7px;">${concepts.length} entries</span></div>`;

        const table = document.createElement('div');
        table.className = 'manage-table';

        concepts.forEach((c, i) => {
            const row = document.createElement('div');
            row.className = 'manage-row';
            row.innerHTML = `
                <div class="manage-row-num">${String(i+1).padStart(3,'0')}</div>
                <div class="manage-row-main">
                    <div class="manage-row-term">${c.term}</div>
                    <div class="manage-row-def">${c.definition.substring(0,90)}${c.definition.length > 90 ? '…' : ''}</div>
                </div>
                <div class="manage-row-actions">
                    <button class="btn-secondary edit-btn" data-id="${c.id}">Edit</button>
                    <button class="btn-danger delete-btn" data-id="${c.id}" data-term="${c.term}">Delete</button>
                </div>
            `;
            table.appendChild(row);
        });

        section.appendChild(table);
        manageList.appendChild(section);
    });

    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => openEditModal(btn.dataset.id));
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', () => openDeleteModal(btn.dataset.id, btn.dataset.term));
    });
}

// add modal
function openAddModal() {
    document.getElementById('modal-title').textContent = 'Add New Entry';
    document.getElementById('modal-save').textContent = 'Save Entry →';
    document.getElementById('f-id').value = '';
    document.getElementById('f-term').value = '';
    document.getElementById('f-definition').value = '';
    document.getElementById('f-example').value = '';
    document.getElementById('f-prompt').value = '';
    document.getElementById('f-unit').selectedIndex = 0;
    document.getElementById('modal-msg').innerHTML = '';
    modalOverlay.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

// edit modal
function openEditModal(id) {
    const concept = allConcepts.find(c => c.id == id);
    if (!concept) return;

    document.getElementById('modal-title').textContent = 'Edit Entry';
    document.getElementById('modal-save').textContent = 'Save Changes →';
    document.getElementById('f-id').value = concept.id;
    document.getElementById('f-term').value = concept.term;
    document.getElementById('f-definition').value = concept.definition;
    document.getElementById('f-example').value = concept.example || '';
    document.getElementById('f-prompt').value = concept.ai_prompt_template || '';
    document.getElementById('modal-msg').innerHTML = '';

    // set unit 
    const sel = document.getElementById('f-unit');
    for (let i = 0; i < sel.options.length; i++) {
        if (sel.options[i].value == concept.unit_id) {
            sel.selectedIndex = i;
            break;
        }
    }

    modalOverlay.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    modalOverlay.style.display = 'none';
    document.body.style.overflow = '';
}

// add or edit conept (save)
function saveConcept() {
    const id         = document.getElementById('f-id').value;
    const unit_id    = document.getElementById('f-unit').value;
    const term       = document.getElementById('f-term').value.trim();
    const definition = document.getElementById('f-definition').value.trim();
    const example    = document.getElementById('f-example').value.trim();
    const prompt     = document.getElementById('f-prompt').value.trim();
    const msgBox     = document.getElementById('modal-msg');
    const saveBtn    = document.getElementById('modal-save');

    if (!unit_id || !term || !definition) {
        msgBox.innerHTML = '<div class="error-msg">Unit, term and definition are required.</div>';
        return;
    }

    saveBtn.textContent = 'Saving...';
    const action = id ? 'edit_concept' : 'add_concept';
    let body = `action=${action}&unit_id=${encodeURIComponent(unit_id)}&term=${encodeURIComponent(term)}&definition=${encodeURIComponent(definition)}&example=${encodeURIComponent(example)}&ai_prompt_template=${encodeURIComponent(prompt)}`;
    if (id) body += `&id=${id}`;

    fetch(WS, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeModal();
            loadConcepts();
        } else {
            msgBox.innerHTML = `<div class="error-msg">${data.error || 'Something went wrong.'}</div>`;
            saveBtn.textContent = 'Save Entry →';
        }
    });
}

// delete modal
function openDeleteModal(id, term) {
    deleteTargetId = id;
    document.getElementById('delete-term-name').textContent = term;
    deleteOverlay.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    deleteOverlay.style.display = 'none';
    document.body.style.overflow = '';
    deleteTargetId = null;
}

function confirmDelete() {
    if (!deleteTargetId) return;
    const btn = document.getElementById('confirm-delete-btn');
    btn.textContent = 'Deleting...';

    fetch(WS, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=delete_concept&id=${deleteTargetId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeDeleteModal();
            loadConcepts();
        } else {
            btn.textContent = 'Error — try again';
        }
    });
}

