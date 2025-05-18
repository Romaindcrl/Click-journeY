document.addEventListener('DOMContentLoaded', function() {
    const sortSelect = document.getElementById('sort-select');
    const voyagesGrid = document.getElementById('voyages-grid');

    if (sortSelect && voyagesGrid) {
        sortSelect.addEventListener('change', function() {
            sortVoyages(this.value);
        });
    }

    function sortVoyages(sortValue) {
        const voyages = Array.from(voyagesGrid.querySelectorAll('.voyage-card'));

        voyages.sort((a, b) => {
            switch (sortValue) {
                case 'nom-asc':
                    return a.dataset.nom.localeCompare(b.dataset.nom);
                case 'nom-desc':
                    return b.dataset.nom.localeCompare(a.dataset.nom);
                case 'prix-asc':
                    return parseFloat(a.dataset.prix) - parseFloat(b.dataset.prix);
                case 'prix-desc':
                    return parseFloat(b.dataset.prix) - parseFloat(a.dataset.prix);
                case 'duree-asc':
                    return parseInt(a.dataset.duree) - parseInt(b.dataset.duree);
                case 'duree-desc':
                    return parseInt(b.dataset.duree) - parseInt(a.dataset.duree);
                default:
                    return 0;
            }
        });

        // Vider la grille
        voyagesGrid.innerHTML = '';

        // Réinsérer les éléments triés
        voyages.forEach(voyage => voyagesGrid.appendChild(voyage));
    }

    // Assurer que les liens fonctionnent correctement
    const allButtons = document.querySelectorAll('.btn-details, .btn-reserve');

    // Ajouter un écouteur d'événements pour chaque bouton
    allButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Obtenir l'URL
            const url = this.getAttribute('href');

            // Rediriger vers l'URL
            if (url) { 
                window.location.href = url;
            }

            // Ajouter un log pour déboguer
            console.log('Navigation vers: ' + url);
        });
    });

    // Initial sort if a sort option is selected (e.g., after page load with query params)
    if (sortSelect) {
        // Let PHP handle the initial state based on paginated data.
        // Client-side sort will only apply to the current page's items.
    }
}); 