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
    // Anciennes cases à cocher statiques supprimées

    // Prix de base
    const basePrice = parseInt(pricePerPerson.getAttribute('data-base'));
    
    // Supprimer gestion statique des options des voyageurs et ajouter génération dynamique
    const activitiesData = window.ACTIVITES_DATA || [];
    const optionsContainer = document.getElementById('options-container');
    const selectedActivitiesContainer = document.getElementById('selected-activities-container');
    const selectedActivitiesList = document.getElementById('selected-activities-list');

    // Générer les contrôles de sélection du nombre de bénéficiaires pour chaque activité
    function renderOptions() {
        optionsContainer.innerHTML = '';
        const participants = parseInt(nbParticipantsSelect.value, 10);
        activitiesData.forEach((activite, index) => {
            const itemDiv = document.createElement('div');
            itemDiv.classList.add('option-item');
            const label = document.createElement('label');
            label.setAttribute('for', 'activite_count_' + index);
            label.textContent = activite.nom;
            itemDiv.appendChild(label);
            const controlsDiv = document.createElement('div');
            controlsDiv.classList.add('option-controls');
            const minusButton = document.createElement('button');
            minusButton.textContent = '-';
            minusButton.type = 'button'; // Important pour éviter la soumission du formulaire
            minusButton.addEventListener('click', function() {
                const input = this.nextElementSibling;
                let currentValue = parseInt(input.value, 10);
                if (currentValue > 0) {
                    input.value = currentValue - 1;
                    updateTotalPrice();
                }
            });
            controlsDiv.appendChild(minusButton);
            const quantityInput = document.createElement('input');
            quantityInput.type = 'number';
            quantityInput.id = 'activite_count_' + index;
            quantityInput.name = 'activites_counts[' + index + ']';
            quantityInput.value = 0;
            quantityInput.min = 0;
            quantityInput.max = participants;
            quantityInput.setAttribute('data-price', activite.prix);
            quantityInput.readOnly = true; // Rendre le champ readonly pour forcer l'utilisation des boutons
            quantityInput.classList.add('quantity-input');
            quantityInput.addEventListener('change', updateTotalPrice);
            controlsDiv.appendChild(quantityInput);
            const plusButton = document.createElement('button');
            plusButton.textContent = '+';
            plusButton.type = 'button'; // Important pour éviter la soumission du formulaire
            plusButton.addEventListener('click', function() {
                const input = this.previousElementSibling;
                let currentValue = parseInt(input.value, 10);
                if (currentValue < parseInt(input.max, 10)) {
                    input.value = currentValue + 1;
                    updateTotalPrice();
                }
            });
            controlsDiv.appendChild(plusButton);
            itemDiv.appendChild(controlsDiv);
            const priceDiv = document.createElement('div');
            priceDiv.classList.add('activity-price');
            priceDiv.textContent = new Intl.NumberFormat('fr-FR').format(activite.prix) + ' €';
            itemDiv.appendChild(priceDiv);
            optionsContainer.appendChild(itemDiv);
        });
    }

    // Calculer et mettre à jour le prix total en fonction des sélections
    function updateTotalPrice() {
        const participants = parseInt(nbParticipantsSelect.value, 10);
        let total = basePrice * participants;
        const quantityInputs = optionsContainer.querySelectorAll('.quantity-input');
        quantityInputs.forEach(input => {
            const qty = parseInt(input.value, 10);
            const price = parseInt(input.getAttribute('data-price'), 10);
            total += price * qty;
        });
        totalPriceDisplay.textContent = new Intl.NumberFormat('fr-FR').format(total) + ' €';
        totalPriceInput.value = total;
        nbParticipantsDisplay.textContent = participants;
        updateSelectedActivities();
    }

    // Mettre à jour la liste des options sélectionnées dans le récapitulatif
    function updateSelectedActivities() {
        selectedActivitiesList.innerHTML = '';
        let anySelected = false;
        const quantityInputs = optionsContainer.querySelectorAll('.quantity-input');
        quantityInputs.forEach((input, index) => {
            const qty = parseInt(input.value, 10);
            if (qty > 0) {
                anySelected = true;
                const activite = activitiesData[index];
                const itemDiv = document.createElement('div');
                itemDiv.classList.add('recap-item');
                itemDiv.innerHTML = `<span>${qty} voyageur${qty > 1 ? 's' : ''} : ${activite.nom}</span><strong>${new Intl.NumberFormat('fr-FR').format(activite.prix * qty)} €</strong>`;
                selectedActivitiesList.appendChild(itemDiv);
            }
        });
        selectedActivitiesContainer.style.display = anySelected ? 'block' : 'none';
    }

    // Écouteur pour changement du nombre de participants
    nbParticipantsSelect.addEventListener('change', function() {
        renderOptions();
        updateTotalPrice();
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
    renderOptions();
    updateTotalPrice();
}); 