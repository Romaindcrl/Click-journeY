/**
 * Script pour la page de personnalisation
 */
document.addEventListener('DOMContentLoaded', function() {
    // Éléments DOM
    const nbParticipantsSelect = document.getElementById('nb_participants');
    const nbParticipantsDisplay = document.getElementById('nb-participants');
    const pricePerPerson = document.getElementById('price-per-person');
    const totalPriceDisplay = document.getElementById('total-price');
    const totalPriceInput = document.getElementById('prix_total_input');
    const activityCheckboxes = document.querySelectorAll('.activity-checkbox input[type="checkbox"]');
    
    // Prix de base
    const basePrice = parseInt(pricePerPerson.getAttribute('data-base'));
    
    // Fonction pour calculer le prix total
    function updateTotalPrice() {
        // Récupération du nombre de participants
        const participants = parseInt(nbParticipantsSelect.value);
        
        // Calcul du prix de base
        let total = basePrice * participants;
        
        // Ajout des activités sélectionnées
        activityCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                total += parseInt(checkbox.getAttribute('data-price')) * participants;
            }
        });
        
        // Mise à jour de l'affichage
        totalPriceDisplay.textContent = new Intl.NumberFormat('fr-FR').format(total) + ' €';
        totalPriceInput.value = total;
        nbParticipantsDisplay.textContent = participants;
    }
    
    // Écouteurs d'événements
    nbParticipantsSelect.addEventListener('change', updateTotalPrice);
    
    activityCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // Animation pour les cases à cocher
            const label = this.nextElementSibling;
            if (this.checked) {
                label.classList.add('selected');
                this.parentElement.classList.add('checked');
            } else {
                label.classList.remove('selected');
                this.parentElement.classList.remove('checked');
            }
            
            updateTotalPrice();
        });
        
        // Animation au survol
        checkbox.parentElement.addEventListener('mouseenter', function() {
            this.classList.add('hover');
        });
        
        checkbox.parentElement.addEventListener('mouseleave', function() {
            this.classList.remove('hover');
        });
    });
    
    // Validation du formulaire
    const form = document.getElementById('personnalisation-form');
    form.addEventListener('submit', function(e) {
        const dateDepart = document.getElementById('date_depart').value;
        
        if (!dateDepart) {
            e.preventDefault();
            alert('Veuillez sélectionner une date de départ');
            return;
        }
        
        // Vérification que la date est dans le futur
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const selectedDate = new Date(dateDepart);
        
        if (selectedDate < today) {
            e.preventDefault();
            alert('La date de départ doit être dans le futur');
            return;
        }
    });
    
    // Initialisation
    updateTotalPrice();
}); 