// === Gestion du contenu dynamique du dashboard ===
window.afficherContenu = function (lien) {
    const targetId = lien.getAttribute('data-target');
    const container = document.getElementById('content');

    document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
    lien.classList.add('active');

    container.innerHTML = '<p>Chargement...</p>';

    fetch(`/dashboard/content/${targetId}`)
        .then(response => {
            if (!response.ok) throw new Error('Erreur lors du chargement');
            return response.text();
        })
        .then(html => {
            container.innerHTML = html;
            activerFiltresTransactions();
        })
        .catch(error => {
            console.error(error);
            container.innerHTML = `<div class="alert alert-danger">Erreur de chargement du contenu.</div>`;
        });
};

// === Filtres dynamiques sur les transactions ===
function activerFiltresTransactions() {
    const typeFilter = document.getElementById('filter-type');
    const categorieFilter = document.getElementById('filter-categorie');
    const button = document.getElementById('filter-button');
    const resetButton = document.getElementById('reset-button');
    const rows = document.querySelectorAll('#transactions-table tbody tr');

    if (button && typeFilter && categorieFilter && rows.length > 0) {
        button.addEventListener('click', () => {
            const selectedType = typeFilter.value;
            const selectedCategory = categorieFilter.value;

            rows.forEach(row => {
                const rowType = row.dataset.type;
                const rowCategory = row.dataset.categorie;

                const typeMatches = !selectedType || rowType === selectedType;
                const categoryMatches = !selectedCategory || rowCategory === selectedCategory;

                row.style.display = (typeMatches && categoryMatches) ? '' : 'none';
            });
        });
    }

    // Bouton de réinitialisation des filtres
    if (resetButton) {
        resetButton.onclick = () => {
            typeFilter.value = '';
            categorieFilter.value = '';

            rows.forEach(row => {
                row.style.display = ''; /* on réaffiche touts */
            });
        };
    }
}

// === Alertes de succès ===
function afficherAlertSucces(message) {
    const zone = document.getElementById('alert-zone');
    if (zone) {
        zone.innerHTML = `
            <div class="alert alert-success alert-dismissible fade show" role="alert" id="alert-success">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        `;
        setTimeout(() => {
            const alert = document.getElementById('alert-success');
            if (alert) {
                alert.classList.remove('show');
                alert.classList.add('hide');
                setTimeout(() => alert.remove(), 500);
            }
        }, 1000);
    }
}

// === Gestion des modals (catégories) ===
window.ouvrirModalAjoutCategorie = function () {
    const modal = new bootstrap.Modal(document.getElementById('modal-ajout-categorie'));
    const modalContent = document.getElementById('modal-ajout-categorie-content');
    modalContent.innerHTML = '<p>Chargement...</p>';

    fetch('/dashboard/content/categorie/new')
        .then(response => response.text())
        .then(html => {
            modalContent.innerHTML = html;
            modal.show();
        })
        .catch(() => {
            modalContent.innerHTML = '<div class="alert alert-danger">Erreur de chargement du formulaire.</div>';
        });
};

window.ouvrirModalEditCategorie = function (id) {
    const modal = new bootstrap.Modal(document.getElementById('modal-ajout-categorie'));
    const modalContent = document.getElementById('modal-ajout-categorie-content');
    modalContent.innerHTML = '<p>Chargement...</p>';

    fetch(`/dashboard/content/categorie/edit/${id}`)
        .then(response => response.text())
        .then(html => {
            modalContent.innerHTML = html;
            modal.show();
        })
        .catch(() => {
            modalContent.innerHTML = '<div class="alert alert-danger">Erreur de chargement du formulaire.</div>';
        });
};

// === Soumission formulaire modal - Ajout catégorie ===
document.addEventListener('submit', function (e) {
    if (e.target && e.target.id === 'form-ajout-categorie') {
        e.preventDefault();

        const form = e.target;
        const data = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: data,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const liste = document.querySelector('.list-group');
                    if (liste) liste.outerHTML = result.listHtml;

                    const modal = bootstrap.Modal.getInstance(document.getElementById('modal-ajout-categorie'));
                    if (modal) modal.hide();

                    afficherAlertSucces(result.message);
                }
            })
            .catch(() => alert("Erreur lors de l'enregistrement."));
    }
});

// === Bouton modifier catégorie ===
document.addEventListener('click', function (e) {
    if (e.target && e.target.classList.contains('btn-modifier-categorie')) {
        e.preventDefault();
        const id = e.target.getAttribute('data-id');
        window.ouvrirModalEditCategorie(id);
    }
});

// === Soumission formulaire modal - Modification catégorie ===
document.addEventListener('submit', function (e) {
    if (e.target && e.target.id === 'form-edit-categorie') {
        e.preventDefault();
        const form = e.target;
        const data = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: data,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const liste = document.querySelector('.list-group');
                    if (liste) liste.outerHTML = result.listHtml;

                    const modal = bootstrap.Modal.getInstance(document.getElementById('modal-ajout-categorie'));
                    if (modal) modal.hide();

                    afficherAlertSucces(result.message);
                } else {
                    alert(result.message || "Erreur lors de la modification.");
                }
            })
            .catch(() => alert("Erreur lors de la modification."));
    }
});

// === Suppression catégorie (modal de confirmation) ===
let categorieIdToDelete = null;
let categorieTokenToDelete = null;

document.addEventListener('click', function (e) {
    if (e.target.classList.contains('btn-supprimer-categorie')) {
        e.preventDefault();
        categorieIdToDelete = e.target.getAttribute('data-id');
        categorieTokenToDelete = e.target.getAttribute('data-token');
        const modal = new bootstrap.Modal(document.getElementById('modal-confirm-suppression'));
        modal.show();
    }
});

document.getElementById('btn-confirm-suppression').addEventListener('click', function () {
    if (!categorieIdToDelete || !categorieTokenToDelete) return;

    fetch(`/categorie/${categorieIdToDelete}/delete`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ _token: categorieTokenToDelete })
    })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const liste = document.querySelector('.list-group');
                if (liste) liste.outerHTML = result.listHtml;

                const modal = bootstrap.Modal.getInstance(document.getElementById('modal-confirm-suppression'));
                if (modal) modal.hide();

                afficherAlertSucces(result.message || 'Catégorie supprimée avec succès !');
            } else {
                alert(result.message || "Erreur lors de la suppression.");
            }
        })
        .catch(() => alert("Erreur lors de la suppression."));
});

// === Gestion des notes (soumission et suppression AJAX) ===
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('content');

    container.addEventListener('submit', e => {
        if (!e.target.matches('form[name="note"]')) return;
        e.preventDefault();
        const form = e.target;
        const data = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: data
        })
            .then(r => r.json())
            .then(json => {
                if (json.success) {
                    container.innerHTML = json.html;
                } else {
                    alert('Erreur : ' + json.errors);
                }
            });
    });

    container.addEventListener('click', e => {
        if (!e.target.matches('.note-delete')) return;
        e.preventDefault();
        if (!confirm('Confirmer la suppression ?')) return;

        const url = e.target.dataset.url;
        const token = e.target.closest('.note-delete').parentNode.querySelector('.note-token').value;

        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'token=' + encodeURIComponent(token)
        })
            .then(r => r.json())
            .then(json => {
                if (json.success) {
                    container.innerHTML = json.html;
                } else {
                    alert("Impossible de supprimer");
                }
            });
    });
});
