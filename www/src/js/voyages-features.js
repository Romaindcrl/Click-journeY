document.addEventListener('DOMContentLoaded', function() {
    const sortSelect = document.getElementById('sort-select');
    const voyagesGrid = document.getElementById('voyages-grid');

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