// webservice shortcut var
const WS = 'webservice.php';

//wait for content
document.addEventListener('DOMContentLoaded', loadBookmarks);

// get bookmarks data
function loadBookmarks() {
    fetch(`${WS}?action=get_bookmarks`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('loading-state').style.display = 'none';
            
            // is nothing saved, show "No Bookmarks" and exit
            if (!data.length) {
                document.getElementById('empty-state').style.display = 'block';
                document.getElementById('stat-count').textContent = '0';
                document.getElementById('stat-units').textContent = '0';
                return;
            }

            document.getElementById('stat-count').textContent = data.length;

            //count units covered by bookmarks that are saved
            const units = [...new Set(data.map(c => c.unit_number))];
            document.getElementById('stat-units').textContent = units.length;

            // group by unit
            const grouped = {};
            data.forEach(c => {
                const key = `Unit ${c.unit_number} · ${c.unit_name}`;
                if (!grouped[key]) grouped[key] = [];
                grouped[key].push(c);
            });

            // create bookmark containers
            const list = document.getElementById('bookmarks-list');
            list.style.display = 'block';

            // loop over each unit to get terms
            Object.entries(grouped).forEach(([unitLabel, concepts]) => {
                const section = document.createElement('div');
                section.className = 'bookmark-unit-section';
                section.innerHTML = `<div class="section-label">${unitLabel}</div>`;

                const grid = document.createElement('div');
                grid.className = 'concepts-grid';

                concepts.forEach((c, i) => {
                    const num = String(i + 1).padStart(3, '0');
                    const card = document.createElement('div');
                    card.className = 'concept-card';
                    card.dataset.id = c.id;
                    card.innerHTML = `
                        <div class="concept-card-num">
                            <span>${num}</span>
                            <span class="meta-tag">Unit ${c.unit_number}</span>
                        </div>
                        <h3>${c.term}</h3>
                        <p>${c.definition.substring(0, 110)}${c.definition.length > 110 ? '…' : ''}</p>
                        <div class="bookmark-card-foot">
                            <button class="btn-entry remove-btn" data-id="${c.id}">Remove Bookmark ✕</button>
                        </div>
                    `;
                    grid.appendChild(card);
                });

                section.appendChild(grid);
                list.appendChild(section);
            });

            // remove bookmark listeners
            document.querySelectorAll('.remove-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    removeBookmark(btn.dataset.id, btn);
                });
            });
        });
}

// change button text, changes database info
function removeBookmark(conceptId, btn) {
    btn.textContent = 'Removing...';
    fetch(WS, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=remove_bookmark&concept_id=${conceptId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const card = document.querySelector(`.concept-card[data-id="${conceptId}"]`);
            if (card) card.remove();
            //reload, recount
            document.getElementById('loading-state').style.display = 'block';
            document.getElementById('bookmarks-list').style.display = 'none';
            loadBookmarks();
        }
    });
}

