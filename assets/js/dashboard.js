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
                    // Met à jour la liste sans recharger la page
                    // (adapte le selecteur selon où tu affiches la liste)
                    const liste = document.querySelector('.list-group');
                    if (liste) {
                        liste.outerHTML = result.listHtml;
                    }

                    // Ferme le modal Bootstrap
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modal-ajout-categorie'));
                    if (modal) modal.hide();

                    // Affiche le message de succès
                    afficherAlertSucces(result.message);
                }
            })
            .catch(() => {
                alert("Erreur lors de l'enregistrement.");
            });
    }
});

function afficherAlertSucces(message) {
    // Ajoute le message dans #alert-zone (toujours présent)
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


