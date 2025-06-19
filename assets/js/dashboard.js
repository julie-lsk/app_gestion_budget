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