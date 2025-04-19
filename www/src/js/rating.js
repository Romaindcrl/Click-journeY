/**
 * Script pour gérer le système d'évaluation des voyages
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les variables pour le système de notation
    const stars = document.querySelectorAll('.star');
    const reviewComment = document.getElementById('review-comment');
    const submitButton = document.getElementById('submit-review');
    const reviewMessage = document.getElementById('review-message');
    const modal = document.getElementById('review-modal');
    const closeBtn = document.querySelector('.close');
    
    let selectedRating = 0;
    let voyageId = '';
    
    // Initialiser les gestionnaires d'événements pour les boutons d'ouverture du modal
    const reviewButtons = document.querySelectorAll('.open-review');
    reviewButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            voyageId = this.getAttribute('data-voyage-id');
            const voyageName = this.closest('.order-card').querySelector('h4').textContent;
            
            // Mettre à jour les éléments du modal
            const voyageNameEl = document.getElementById('voyage-name');
            if (voyageNameEl) {
                voyageNameEl.textContent = voyageName;
            }
            resetModal();
            
            // Afficher le modal
            modal.style.display = 'block';
        });
    });
    
    // Fermer le modal en cliquant sur X
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }
    
    // Fermer le modal en cliquant en dehors
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Gestion de la sélection des étoiles
    stars.forEach(star => {
        // Effet au survol
        star.addEventListener('mouseover', function() {
            const value = parseInt(this.getAttribute('data-value'));
            highlightStars(value);
        });
        
        // Réinitialisation au retrait du survol
        star.addEventListener('mouseout', function() {
            highlightStars(selectedRating);
        });
        
        // Sélection d'une note
        star.addEventListener('click', function() {
            selectedRating = parseInt(this.getAttribute('data-value'));
            highlightStars(selectedRating);
        });
    });
    
    // Soumission de l'avis
    if (submitButton) {
        submitButton.addEventListener('click', function() {
            if (selectedRating === 0) {
                showMessage('Veuillez sélectionner une note', 'error');
                return;
            }
            
            const commentText = reviewComment.value.trim();
            
            // Préparer les données de l'avis
            const reviewData = {
                voyage_id: voyageId,
                rating: selectedRating,
                comment: commentText,
                date: new Date().toISOString().split('T')[0]
            };
            
            // Envoyer l'avis au serveur
            sendReview(reviewData);
        });
    }
    
    // Fonction pour envoyer l'avis au serveur
    function sendReview(reviewData) {
        fetch('../api/add_review.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(reviewData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('Merci pour votre avis !', 'success');
                setTimeout(function() {
                    modal.style.display = 'none';
                    resetModal();
                }, 2000);
            } else {
                showMessage(data.message || 'Une erreur est survenue', 'error');
            }
        })
        .catch(error => {
            showMessage('Erreur de connexion', 'error');
            console.error('Erreur:', error);
        });
    }
    
    // Fonction pour mettre en évidence les étoiles
    function highlightStars(count) {
        stars.forEach(star => {
            const value = parseInt(star.getAttribute('data-value'));
            if (value <= count) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });
    }
    
    // Fonction pour afficher un message
    function showMessage(text, type) {
        reviewMessage.textContent = text;
        reviewMessage.className = type;
        reviewMessage.style.display = 'block';
    }
    
    // Fonction pour réinitialiser le modal
    function resetModal() {
        selectedRating = 0;
        highlightStars(0);
        reviewComment.value = '';
        reviewMessage.style.display = 'none';
    }
    
    // Fonction pour obtenir l'ID de l'utilisateur (non utilisée car l'ID est récupéré de la session côté serveur)
    function getUserId() {
        return document.querySelector('[data-user-id]')?.getAttribute('data-user-id') || '';
    }
    
    // Message de log en français
    console.log("Système d'évaluation initialisé avec succès");
}); 