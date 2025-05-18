// Attend que le DOM soit entièrement chargé avant d'exécuter le script.
document.addEventListener('DOMContentLoaded', function() {
    // Récupère les éléments du DOM nécessaires pour la modale et le formulaire d'avis.
    const modal = document.getElementById('review-modal');
    const closeBtn = document.querySelector('.close');
    const stars = document.querySelectorAll('.rating-stars .rating-star');
    const ratingInput = document.getElementById('rating-value');
    const reviewForm = document.getElementById('review-form');
    const reviewMessage = document.getElementById('review-message');

    // Fonction pour ouvrir la modale d'avis.
    // Elle prend l'ID du voyage en paramètre et l'assigne à un champ caché du formulaire.
    // Affiche la modale et empêche le défilement de la page en arrière-plan.
    window.openReviewModal = function(voyageId) {
        document.getElementById('voyage-id').value = voyageId;
        if (modal) modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    };

    // Fonction pour fermer la modale d'avis.
    // Cache la modale, restaure le défilement de la page, réinitialise le formulaire,
    // les étoiles de notation et le message d'avis.
    function closeModal() {
        if (modal) modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        if (reviewForm) reviewForm.reset();
        if (stars) stars.forEach(star => star.classList.remove('active'));
        if (ratingInput) ratingInput.value = 0;
        if (reviewMessage) {
            reviewMessage.style.display = 'none';
            reviewMessage.className = '';
        }
    }

    // Ajoute un écouteur d'événement sur le bouton de fermeture pour appeler la fonction closeModal.
    if (closeBtn) closeBtn.addEventListener('click', closeModal);

    // Gère la logique de notation par étoiles.
    // Ajoute des écouteurs d'événements sur chaque étoile.
    if (stars) {
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const value = this.getAttribute('data-value'); // Récupère la valeur de l'étoile cliquée.
                if (ratingInput) ratingInput.value = value; // Met à jour la valeur du champ de notation caché.
                // Met à jour l'apparence des étoiles (active ou inactive).
                stars.forEach(s => {
                    if (s.getAttribute('data-value') <= value) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
            });
        });
    }

    // Gère la soumission du formulaire d'avis.
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Empêche la soumission standard du formulaire.
            const messageDiv = document.getElementById('review-message');
            // Affiche un message de succès (simulation car il n'y a pas de traitement côté serveur ici).
            if (messageDiv) {
                messageDiv.textContent = 'Avis soumis avec succès ! (Simulation)';
                messageDiv.className = 'success';
                messageDiv.style.display = 'block';
            }
            // Ferme la modale après un délai de 2 secondes.
            setTimeout(() => {
                closeModal();
                if (messageDiv) messageDiv.style.display = 'none';
            }, 2000);
        });
    }

    // Ajoute un écouteur d'événement pour fermer la modale si l'utilisateur clique en dehors de son contenu.
    window.addEventListener('click', function(e) {
        if (modal && e.target === modal) {
            if (closeBtn) closeBtn.click(); // Simule un clic sur le bouton de fermeture.
        }
    });
}); 