// public/js/dashboard.js

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
