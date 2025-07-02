import Chart from 'chart.js/auto';

// Affichage dynamique des pages du dashboard
window.afficherContenu = function(lien) {
    const targetId = lien.getAttribute('data-target');
    let container = document.getElementById('content');

    // MAJ hash dans l'URL !
    window.location.hash = targetId;

    // MAJ de la classe "active" selon l'élément cliqué
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
    });
    lien.classList.add('active');

    container.innerHTML = '<p>Chargement...</p>';

    // Récupère selon le menu cliqué
    fetch(`/dashboard/content/${targetId}`)
        .then(response => {
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

// ------------------ MODALS & FORMULAIRES ------------------ //

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
                const liste = document.querySelector('.list-group');
                if (liste) {
                    liste.outerHTML = result.listHtml;
                }
                const modal = bootstrap.Modal.getInstance(document.getElementById('modal-confirm-suppression'));
                if (modal) modal.hide();
                afficherAlertSucces(result.message || 'Catégorie supprimée avec succès !');
            } else {
                alert(result.message || "Erreur lors de la suppression.");
            }
        })
        .catch(() => alert("Erreur lors de la suppression."));
});

// ---------------------- CHARTS ---------------------- //
// Les graphiques sont initialisés à chaque fois que la page dashboard est rechargée
document.addEventListener('revenusParMoisLoaded', function () {
    // Revenus par mois
    const canvasRevenus = document.getElementById('revenusChart');
    if (canvasRevenus && window.revenusParMois) {
        const data = window.revenusParMois;
        const labels = data.map(item => item.mois);
        const totals = data.map(item => parseFloat(item.total));

        new Chart(canvasRevenus.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenus par mois',
                    data: totals,
                    borderWidth: 1,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Dépenses par mois (exemple statique)
    const canvasDepenses = document.getElementById('depensesChart');
    if (canvasDepenses) {
        new Chart(canvasDepenses.getContext('2d'), {
            type: 'line',
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
                datasets: [{
                    label: 'Dépenses (exemple)',
                    data: [900, 1100, 950, 1200, 1000, 1100],
                    fill: false,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.3)',
                    tension: 0.2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true }
                }
            }
        });
    }

    // Dépenses par catégorie (exemple statique)
    const canvasDepCat = document.getElementById('depensesCategorieChart');
    if (canvasDepCat) {
        new Chart(canvasDepCat.getContext('2d'), {
            type: 'pie',
            data: {
                labels: ['Loyer', 'Courses', 'Transport', 'Santé', 'Autres'],
                datasets: [{
                    label: 'Dépenses par catégorie',
                    data: [700, 350, 150, 100, 200],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 205, 86, 0.6)',
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(201, 203, 207, 0.6)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true }
                }
            }
        });
    }

    // Revenus par catégorie (exemple statique)
    const canvasRevCat = document.getElementById('revenusCategorieChart');
    if (canvasRevCat) {
        new Chart(canvasRevCat.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Salaire', 'Allocations', 'Ventes', 'Autres'],
                datasets: [{
                    label: 'Revenus par catégorie',
                    data: [1200, 350, 250, 100],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(255, 159, 64, 0.6)',
                        'rgba(153, 102, 255, 0.6)',
                        'rgba(255, 205, 86, 0.6)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true }
                }
            }
        });
    }
});


