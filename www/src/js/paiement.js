// Script pour la page de paiement
// Gère la validation et le formatage du formulaire de paiement

document.addEventListener('DOMContentLoaded', function() {
    // Récupération des éléments du formulaire
    const form = document.getElementById('paiement-form');
    const cardNumberInput = document.getElementById('card_number');
    const cvvInput = document.getElementById('cvv');
    const expiryMonthSelect = document.getElementById('expiry_month');
    const expiryYearSelect = document.getElementById('expiry_year');
    
    // Formatage du numéro de carte avec des espaces tous les 4 chiffres
    cardNumberInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, ''); // Supprimer tout ce qui n'est pas un chiffre
        let formattedValue = '';
        
        for (let i = 0; i < value.length; i++) {
            if (i > 0 && i % 4 === 0) {
                formattedValue += ' ';
            }
            formattedValue += value[i];
        }
        
        // Limiter à 19 caractères (16 chiffres + 3 espaces)
        if (formattedValue.length > 19) {
            formattedValue = formattedValue.substring(0, 19);
        }
        
        e.target.value = formattedValue;
    });
    
    // Ne permettre que des chiffres pour le CVV
    cvvInput.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '').substring(0, 3);
    });
    
    // Validation du formulaire
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Réinitialiser les messages d'erreur
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        document.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
        
        // Valider le nom sur la carte
        const cardName = document.getElementById('card_name');
        if (!cardName.value.trim()) {
            showError(cardName, 'Veuillez entrer le nom du titulaire de la carte');
            isValid = false;
        }
        
        // Valider le numéro de carte
        if (!cardNumberInput.value.trim() || cardNumberInput.value.replace(/\s/g, '').length !== 16) {
            showError(cardNumberInput, 'Veuillez entrer un numéro de carte valide à 16 chiffres');
            isValid = false;
        }
        
        // Valider la date d'expiration
        if (!expiryMonthSelect.value) {
            showError(expiryMonthSelect, 'Veuillez sélectionner le mois d\'expiration');
            isValid = false;
        }
        
        if (!expiryYearSelect.value) {
            showError(expiryYearSelect, 'Veuillez sélectionner l\'année d\'expiration');
            isValid = false;
        }
        
        // Vérifier que la date d'expiration n'est pas dépassée
        if (expiryMonthSelect.value && expiryYearSelect.value) {
            const currentDate = new Date();
            const currentYear = currentDate.getFullYear();
            const currentMonth = currentDate.getMonth() + 1; // getMonth retourne 0-11
            
            const expYear = parseInt(expiryYearSelect.value);
            const expMonth = parseInt(expiryMonthSelect.value);
            
            if (expYear < currentYear || (expYear === currentYear && expMonth < currentMonth)) {
                showError(expiryYearSelect, 'La date d\'expiration est dépassée');
                isValid = false;
            }
        }
        
        // Valider le CVV
        if (!cvvInput.value.trim() || cvvInput.value.length !== 3) {
            showError(cvvInput, 'Veuillez entrer un code CVV valide à 3 chiffres');
            isValid = false;
        }
        
        // Valider les conditions générales
        const termsCheckbox = document.getElementById('accept_terms');
        if (!termsCheckbox.checked) {
            showError(termsCheckbox, 'Vous devez accepter les conditions générales de vente');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Fonction pour afficher les messages d'erreur
    function showError(element, message) {
        element.classList.add('error');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        
        // Insérer le message après l'élément ou après son parent (pour les cases à cocher)
        if (element.type === 'checkbox') {
            element.parentNode.insertAdjacentElement('afterend', errorDiv);
        } else {
            element.insertAdjacentElement('afterend', errorDiv);
        }
    }
}); 