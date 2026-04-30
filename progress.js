// webservice shortcut, lookup constants

const WS = 'webservice.php';

const STATUS_LABELS = {
    'not_started': 'Not Started',
    'in_progress': 'In Progress',
    'mastered':    'Mastered'
};

const STATUS_COLORS = {
    'not_started': '#888888',
    'in_progress': '#5B2D8E',
    'mastered':    '#1b6b3a'
};

// load progress once pafe is done loading
document.addEventListener('DOMContentLoaded', loadProgress);

// gets state refs, resets to starting state
function loadProgress() {
    const list    = document.getElementById('progress-list');
    const loading = document.getElementById('loading-state');
    const empty   = document.getElementById('empty-state');

    loading.style.display = 'block';
    list.style.display    = 'none';
    list.innerHTML        = '';
    empty.style.display   = 'none';

    // get progress entries
    fetch(`${WS}?action=get_progress`)
        .then(r => r.json())
        .then(data => {
            loading.style.display = 'none';

            if (!Array.isArray(data) || data.length === 0) {
                empty.style.display = 'block';
                document.getElementById('stat-mastered').textContent = '0';
                document.getElementById('stat-progress').textContent  = '0';
                return;
            }

            updateCounts(data);
            
            // same grouping as bookmarks
            const grouped = {};
            data.forEach(c => {
                const key = `Unit ${c.unit_number} · ${c.unit_name}`;
                if (!grouped[key]) grouped[key] = [];
                grouped[key].push(c);
            });

            list.style.display = 'block';

            Object.entries(grouped).forEach(([unitLabel, concepts]) => {
                const section = document.createElement('div');
                section.innerHTML = `<div class="section-label">${unitLabel}</div>`;

                const grid = document.createElement('div');
                grid.className = 'concepts-grid';

                // for each concept - show num, status color lookup, label
                concepts.forEach((c, i) => {
                    const num   = String(i + 1).padStart(3, '0');
                    const color = STATUS_COLORS[c.status] || '#888';
                    const label = STATUS_LABELS[c.status] || c.status;
                    const card  = document.createElement('div');
                    card.className    = 'concept-card';
                    card.dataset.conceptId = c.id;
                    card.innerHTML = `
                        <div class="concept-card-num">
                            <span>${num}</span>
                            <span class="meta-tag status-badge" style="background:${color};color:#fff;border-color:${color};">${label}</span>
                        </div>
                        <h3>${c.term}</h3>
                        <div class="progress-update-row">
                            <select class="progress-inline-select">
                                <option value="not_started" ${c.status === 'not_started' ? 'selected' : ''}>Not Started</option>
                                <option value="in_progress" ${c.status === 'in_progress' ? 'selected' : ''}>In Progress</option>
                                <option value="mastered"    ${c.status === 'mastered'    ? 'selected' : ''}>Mastered</option>
                            </select>
                            <button class="btn-entry update-btn">Update</button>
                        </div>
                    `;

                    const btn    = card.querySelector('.update-btn');
                    const select = card.querySelector('.progress-inline-select');

                    btn.addEventListener('click', () => {
                        updateProgress(c.id, select.value, btn, card);
                    });

                    grid.appendChild(card);
                });

                section.appendChild(grid);
                list.appendChild(section);
            });
        })
        .catch(() => {
            loading.style.display = 'none';
            empty.style.display   = 'block';
        });
}

// update button text and disable to avoid multiple clicks
function updateProgress(conceptId, status, btn, card) {
    btn.textContent = 'Saving...';
    btn.disabled = true;

    // send update to ws
    fetch(WS, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update_progress&concept_id=${conceptId}&status=${status}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.textContent = 'Saved';
            btn.disabled = false;
            setTimeout(() => {
                loadProgress();
            }, 600);
        } else {
            btn.textContent = 'Error';
            btn.disabled = false;
            setTimeout(() => { btn.textContent = 'Update'; }, 1500);
        }
    })
    .catch(() => {
        btn.textContent = 'Error';
        btn.disabled = false;
        setTimeout(() => { btn.textContent = 'Update'; }, 1500);
    });
}

// counts num entries per status
function updateCounts(data) {
    const mastered   = data.filter(c => c.status === 'mastered').length;
    const inProgress = data.filter(c => c.status === 'in_progress').length;
    document.getElementById('stat-mastered').textContent = mastered;
    document.getElementById('stat-progress').textContent  = inProgress;
}

