// Affichage dynamique des pages du dashboard
window.afficherContenu = function(lien) {
    const targetId = lien.getAttribute('data-target');
    let container = document.getElementById('content');

    // MAJ de la classe "active" selon l'élément cliqué
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
    });
    lien.classList.add('active');

    container.innerHTML = '<p>Chargement...</p>';

    // Récupère selon le menu cliqué
    fetch(`/dashboard/content/${targetId}`)
        .then(response => {
            // Si erreur
            if (!response.ok) throw new Error('Erreur lors du chargement');
            return response.text();
        })
        .then(html => {
            container.innerHTML = html;
        })
        .catch(error => {
            console.error(error);
            container.innerHTML = `<div class="alert alert-danger">Erreur de chargement du contenu.</div>`;
        });
}

window.ouvrirModalAjoutCategorie = function() {
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

window.ouvrirModalEditCategorie = function(id) {
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

document.addEventListener('submit', function (e) {
    // On cible uniquement le formulaire du modal
    if (e.target && e.target.id === 'form-ajout-categorie') {
        e.preventDefault();

        const form = e.target;
        const data = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: data,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {

                    const liste = document.querySelector('.list-group');
                    if (liste) {
                        liste.outerHTML = result.listHtml;
                    }

                    const modal = bootstrap.Modal.getInstance(document.getElementById('modal-ajout-categorie'));
                    if (modal) modal.hide();

                    afficherAlertSucces(result.message);
                }
            })
            .catch(() => {
                alert("Erreur lors de l'enregistrement.");
            });
    }
});

document.addEventListener('click', function (e) {
    if (e.target && e.target.classList.contains('btn-modifier-categorie')) {
        e.preventDefault();
        const id = e.target.getAttribute('data-id');
        window.ouvrirModalEditCategorie(id);
    }
});

document.addEventListener('submit', function (e) {
    if (e.target && e.target.id === 'form-edit-categorie') {
        e.preventDefault();
        const form = e.target;
        const data = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: data,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Met à jour la liste des catégories
                    const liste = document.querySelector('.list-group');
                    if (liste) {
                        liste.outerHTML = result.listHtml;
                    }

                    const modal = bootstrap.Modal.getInstance(document.getElementById('modal-ajout-categorie'));
                    if (modal) modal.hide();

                    afficherAlertSucces(result.message);
                } else {
                    alert(result.message || "Erreur lors de la modification.");
                }
            })
            .catch(() => {
                alert("Erreur lors de la modification.");
            });
    }
});

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

let categorieIdToDelete = null;
let categorieTokenToDelete = null;

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btn-supprimer-categorie')) {
        e.preventDefault();
        categorieIdToDelete = e.target.getAttribute('data-id');
        categorieTokenToDelete = e.target.getAttribute('data-token');
        const modal = new bootstrap.Modal(document.getElementById('modal-confirm-suppression'));
        modal.show();
    }
});

document.getElementById('btn-confirm-suppression').addEventListener('click', function() {
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
                // Met à jour la liste, SANS recharger toute la vue !
                const liste = document.querySelector('.list-group');
                if (liste) {
                    liste.outerHTML = result.listHtml;
                }
                // Ferme le modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('modal-confirm-suppression'));
                if (modal) modal.hide();
                // Affiche le message de succès
                afficherAlertSucces(result.message || 'Catégorie supprimée avec succès !');
            } else {
                alert(result.message || "Erreur lors de la suppression.");
            }
        })
        .catch(() => alert("Erreur lors de la suppression."));
});

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('content');
    const navLinks = document.querySelectorAll('.dashboard-nav a[data-target]');

    function afficherContenu(link) {
        const page = link.dataset.target;

        // MAJ de la bordure “active” dans la nav
        navLinks.forEach(a => {
            a.classList.remove('border', 'border-dark', 'rounded');
        });
        link.classList.add('border', 'border-dark', 'rounded');

        // Indicateur pendant le chargement
        container.innerHTML = '<p>Chargement du contenu…</p>';

        fetch(`/dashboard/content/${page}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                container.innerHTML = html;
            })
            .catch(err => {
                console.error('Erreur de chargement', err);
                container.innerHTML = `
          <div class="alert alert-danger">
            Erreur de chargement du contenu.
          </div>
        `;
            });
    }

    // Brancher les clics
    navLinks.forEach(a => {
        a.addEventListener('click', e => {
            e.preventDefault();
            afficherContenu(a);
        });
    });

    // au chargement
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('content');

        // submit AJAX pour la création de note
        container.addEventListener('submit', e => {
            if (!e.target.matches('form[name="note"]')) return;
            e.preventDefault();
            const form = e.target;
            const data = new FormData(form);
            fetch(form.action, {
                method: 'POST',
                headers: {'X-Requested-With':'XMLHttpRequest'},
                body: data
            })
                .then(r => r.json())
                .then(json => {
                    if (json.success) {
                        container.innerHTML = json.html;
                    } else {
                        alert('Erreur : '+ json.errors);
                    }
                });
        });

        // click AJAX pour la suppression de note
        container.addEventListener('click', e => {
            if (!e.target.matches('.note-delete')) return;
            e.preventDefault();
            if (!confirm('Confirmer la suppression ?')) return;
            const url   = e.target.dataset.url;
            const token = e.target.closest('.note-delete').parentNode.querySelector('.note-token').value;
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With':'XMLHttpRequest',
                    'Content-Type':'application/x-www-form-urlencoded'
                },
                body: 'token='+encodeURIComponent(token)
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

});
