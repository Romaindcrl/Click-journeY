/**
 * Validation des formulaires côté client
 */

document.addEventListener('DOMContentLoaded', function() {
    // Formulaire d'inscription
    const inscriptionForm = document.getElementById('inscription-form');
    if (inscriptionForm) {
        setupPasswordVisibility(inscriptionForm);
        setupCharCounter(inscriptionForm);
        
        inscriptionForm.addEventListener('submit', function(e) {
            if (!validateInscriptionForm(this)) {
                e.preventDefault();
            }
        });
    }
    
    // Formulaire de connexion
    const connexionForm = document.getElementById('connexion-form');
    if (connexionForm) {
        setupPasswordVisibility(connexionForm);
        
        connexionForm.addEventListener('submit', function(e) {
            if (!validateConnexionForm(this)) {
                e.preventDefault();
            }
        });
    }
    
    // Formulaire de profil
    const profilForm = document.getElementById('profil-form');
    if (profilForm) {
        setupEditableFields(profilForm);
    }
    
    // Formulaire de paiement
    const paiementForm = document.getElementById('paiement-form');
    if (paiementForm) {
        setupCardNumberFormatting(paiementForm);
        setupLivePaymentValidation(paiementForm);
        
        paiementForm.addEventListener('submit', function(e) {
            if (!validatePaiementForm(this)) {
                e.preventDefault();
            }
        });
    }
});

/**
 * Configure l'affichage/masquage du mot de passe
 */
function setupPasswordVisibility(form) {
    const passwordFields = form.querySelectorAll('input[type="password"]');
    
    passwordFields.forEach(field => {
        // Créer le conteneur pour le champ et l'icône
        const container = document.createElement('div');
        container.classList.add('password-field-container');
        field.parentNode.insertBefore(container, field);
        container.appendChild(field);
        
        // Créer le bouton de toggle
        const toggleButton = document.createElement('button');
        toggleButton.type = 'button';
        toggleButton.classList.add('password-toggle');
        toggleButton.innerHTML = '<i class="fas fa-eye"></i>';
        container.appendChild(toggleButton);
        
        // Ajouter l'événement de click
        toggleButton.addEventListener('click', function() {
            if (field.type === 'password') {
                field.type = 'text';
                toggleButton.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                field.type = 'password';
                toggleButton.innerHTML = '<i class="fas fa-eye"></i>';
            }
        });
    });
}

/**
 * Configure le compteur de caractères pour les champs limités
 */
function setupCharCounter(form) {
    const limitedFields = form.querySelectorAll('[data-max-length]');
    
    limitedFields.forEach(field => {
        const maxLength = parseInt(field.dataset.maxLength);
        
        // Créer le compteur
        const counter = document.createElement('div');
        counter.classList.add('char-counter');
        counter.textContent = `0/${maxLength}`;
        field.parentNode.appendChild(counter);
        
        // Mettre à jour le compteur lors de la saisie
        field.addEventListener('input', function() {
            const currentLength = this.value.length;
            counter.textContent = `${currentLength}/${maxLength}`;
            
            if (currentLength > maxLength) {
                counter.classList.add('exceeded');
                field.classList.add('error');
            } else {
                counter.classList.remove('exceeded');
                field.classList.remove('error');
            }
        });
        
        // Initialiser le compteur
        field.dispatchEvent(new Event('input'));
    });
}

/**
 * Configure les champs éditables dans le profil
 */
function setupEditableFields(form) {
    const editableContainers = form.querySelectorAll('.editable-field');
    
    editableContainers.forEach(container => {
        const field = container.querySelector('input, textarea, select');
        const editButton = container.querySelector('.edit-btn');
        const saveButton = container.querySelector('.save-btn');
        const cancelButton = container.querySelector('.cancel-btn');
        const submitButton = form.querySelector('button[type="submit"]');
        
        if (!field || !editButton) return;
        
        // Mémoriser la valeur initiale
        let initialValue = field.value;
        
        // Désactiver le champ initialement
        field.disabled = true;
        if (saveButton) saveButton.style.display = 'none';
        if (cancelButton) cancelButton.style.display = 'none';
        if (submitButton) submitButton.style.display = 'none';
        
        // Activer l'édition au clic sur le bouton
        editButton.addEventListener('click', function() {
            field.disabled = false;
            field.focus();
            editButton.style.display = 'none';
            if (saveButton) saveButton.style.display = 'inline-block';
            if (cancelButton) cancelButton.style.display = 'inline-block';
        });
        
        // Enregistrer les modifications
        if (saveButton) {
            saveButton.addEventListener('click', function() {
                // Valider l'email si c'est un champ email
                if (field.type === 'email') {
                    if (!isValidEmail(field.value)) {
                        // Supprimer les anciens messages d'erreur
                        const existingError = container.querySelector('.field-error');
                        if (existingError) existingError.remove();
                        
                        // Créer un nouveau message d'erreur
                        const errorMessage = document.createElement('div');
                        errorMessage.className = 'field-error';
                        errorMessage.innerText = 'Veuillez entrer une adresse email valide';
                        errorMessage.style.color = 'red';
                        errorMessage.style.fontSize = '0.875rem';
                        errorMessage.style.marginTop = '0.25rem';
                        container.appendChild(errorMessage);
                        return;
                    } else {
                        // Enlever le message d'erreur s'il existe
                        const existingError = container.querySelector('.field-error');
                        if (existingError) existingError.remove();
                    }
                }
                
                // Ne pas désactiver le champ afin qu'il soit inclus dans la soumission
                editButton.style.display = 'inline-block';
                saveButton.style.display = 'none';
                cancelButton.style.display = 'none';
                
                // Si au moins un champ a été modifié, afficher le bouton de soumission
                if (initialValue !== field.value) {
                    initialValue = field.value;
                    container.classList.add('modified');
                    if (submitButton) submitButton.style.display = 'block';
                    // Soumettre automatiquement le formulaire pour enregistrer la modification
                    form.submit();
                }
            });
        }
        
        // Annuler les modifications
        if (cancelButton) {
            cancelButton.addEventListener('click', function() {
                field.value = initialValue;
                field.disabled = true;
                editButton.style.display = 'inline-block';
                saveButton.style.display = 'none';
                cancelButton.style.display = 'none';
                // Supprimer les messages d'erreur
                const existingError = container.querySelector('.field-error');
                if (existingError) existingError.remove();
            });
        }
    });
}

/**
 * Configure la validation en temps réel du formulaire de paiement
 */
function setupLivePaymentValidation(form) {
    const cardNameInput = document.getElementById('card-name');
    const cardNumberInput = document.getElementById('card-number');
    const expiryMonthSelect = document.getElementById('expiry-month');
    const expiryYearSelect = document.getElementById('expiry-year');
    const cvvInput = document.getElementById('cvv');
    const termsCheckbox = document.getElementById('terms');
    const submitButton = document.getElementById('submit-payment');
    const formError = document.getElementById('form-error');

    // Patterns de validation
    const cardNamePattern = /^[A-Za-zÀ-ÖØ-öø-ÿ\s'-]{2,50}$/;
    const cardNumberPattern = /^[0-9]{16}$/;
    const cvvPattern = /^[0-9]{3,4}$/;

    // État de validation
    const validationState = {
        cardName: false,
        cardNumber: false,
        expiryMonth: false,
        expiryYear: false,
        cvv: false,
        terms: false
    };

    // Initialisation
    populateExpiryMonths();
    populateExpiryYears();

    // Event listeners
    if (cardNameInput) cardNameInput.addEventListener('input', validateCardName);
    if (cardNumberInput) cardNumberInput.addEventListener('input', validateCardNumber);
    if (expiryMonthSelect) expiryMonthSelect.addEventListener('change', validateExpiryMonth);
    if (expiryYearSelect) expiryYearSelect.addEventListener('change', validateExpiryYear);
    if (cvvInput) cvvInput.addEventListener('input', validateCVV);
    if (termsCheckbox) termsCheckbox.addEventListener('change', validateTerms);
    if (form) form.addEventListener('submit', handleSubmit);

    // Fonctions de validation
    function validateCardName() {
        const value = cardNameInput.value.trim();
        const isValid = cardNamePattern.test(value);
        updateValidationUI(cardNameInput, isValid);
        validationState.cardName = isValid;
        return isValid;
    }

    function validateCardNumber() {
        const value = cardNumberInput.value.replace(/\s/g, '');
        const isValid = cardNumberPattern.test(value);
        updateValidationUI(cardNumberInput, isValid);
        validationState.cardNumber = isValid;
        
        // Formatage du numéro de carte (groupes de 4 chiffres)
        if (value.length > 0) {
            const formatted = value.match(/.{1,4}/g)?.join(' ') || value;
            if (formatted !== cardNumberInput.value) {
                cardNumberInput.value = formatted;
            }
        }
        
        return isValid;
    }

    function validateExpiryMonth() {
        const value = expiryMonthSelect.value;
        const isValid = value !== '';
        updateValidationUI(expiryMonthSelect, isValid);
        validationState.expiryMonth = isValid;
        
        // Valider la date d'expiration complète si le mois et l'année sont sélectionnés
        if (isValid && validationState.expiryYear) {
            validateExpiryDate();
        }
        
        return isValid;
    }

    function validateExpiryYear() {
        const value = expiryYearSelect.value;
        const isValid = value !== '';
        updateValidationUI(expiryYearSelect, isValid);
        validationState.expiryYear = isValid;
        
        // Valider la date d'expiration complète si le mois et l'année sont sélectionnés
        if (isValid && validationState.expiryMonth) {
            validateExpiryDate();
        }
        
        return isValid;
    }

    function validateExpiryDate() {
        const currentDate = new Date();
        const currentMonth = currentDate.getMonth() + 1; // getMonth() est basé sur 0
        const currentYear = currentDate.getFullYear();
        
        const selectedMonth = parseInt(expiryMonthSelect.value);
        const selectedYear = parseInt(expiryYearSelect.value);
        
        const isValid = (selectedYear > currentYear) || 
                        (selectedYear === currentYear && selectedMonth >= currentMonth);
        
        if (!isValid) {
            expiryMonthSelect.classList.add('is-invalid');
            expiryYearSelect.classList.add('is-invalid');
            document.getElementById('expiry-error').textContent = 'La date d\'expiration est dépassée';
            document.getElementById('expiry-error').style.display = 'block';
        } else {
            document.getElementById('expiry-error').style.display = 'none';
        }
        
        validationState.expiryMonth = isValid;
        validationState.expiryYear = isValid;
        
        return isValid;
    }

    function validateCVV() {
        const value = cvvInput.value.trim();
        const isValid = cvvPattern.test(value);
        updateValidationUI(cvvInput, isValid);
        validationState.cvv = isValid;
        return isValid;
    }

    function validateTerms() {
        const isValid = termsCheckbox.checked;
        updateValidationUI(termsCheckbox, isValid);
        validationState.terms = isValid;
        return isValid;
    }

    // Fonctions utilitaires
    function updateValidationUI(element, isValid) {
        if (isValid) {
            element.classList.remove('is-invalid');
            element.classList.add('is-valid');
            
            // Masquer le message d'erreur correspondant
            const errorId = element.id + '-error';
            const errorElement = document.getElementById(errorId);
            if (errorElement) {
                errorElement.style.display = 'none';
            }
        } else {
            element.classList.remove('is-valid');
            element.classList.add('is-invalid');
            
            // Afficher le message d'erreur correspondant
            const errorId = element.id + '-error';
            const errorElement = document.getElementById(errorId);
            if (errorElement) {
                errorElement.style.display = 'block';
            }
        }
    }

    function resetValidationState() {
        Object.keys(validationState).forEach(key => {
            validationState[key] = false;
        });
        
        const formElements = form.querySelectorAll('input, select');
        formElements.forEach(element => {
            element.classList.remove('is-valid', 'is-invalid');
        });
        
        if (formError) {
            formError.style.display = 'none';
        }
    }

    function populateExpiryMonths() {
        if (!expiryMonthSelect) return;
        
        for (let i = 1; i <= 12; i++) {
            const month = i < 10 ? '0' + i : i.toString();
            const option = document.createElement('option');
            option.value = month;
            option.textContent = month;
            expiryMonthSelect.appendChild(option);
        }
    }

    function populateExpiryYears() {
        if (!expiryYearSelect) return;
        
        const currentYear = new Date().getFullYear();
        for (let i = 0; i < 10; i++) {
            const year = (currentYear + i).toString();
            const option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            expiryYearSelect.appendChild(option);
        }
    }

    function validateAll() {
        const isCardNameValid = validateCardName();
        const isCardNumberValid = validateCardNumber();
        const isExpiryMonthValid = validateExpiryMonth();
        const isExpiryYearValid = validateExpiryYear();
        const isCvvValid = validateCVV();
        const isTermsValid = validateTerms();
        
        return isCardNameValid && isCardNumberValid && 
               isExpiryMonthValid && isExpiryYearValid && 
               isCvvValid && isTermsValid;
    }

    function handleSubmit(event) {
        event.preventDefault();
        
        const isValid = validateAll();
        
        if (isValid) {
            // Soumettre le formulaire
            form.submit();
        } else {
            // Afficher message d'erreur général
            if (formError) {
                formError.textContent = 'Veuillez corriger les erreurs dans le formulaire';
                formError.style.display = 'block';
            }
            
            // Faire défiler jusqu'au premier champ invalide
            const firstInvalidElement = form.querySelector('.is-invalid');
            if (firstInvalidElement) {
                firstInvalidElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalidElement.focus();
            }
        }
    }
}

/**
 * Configure le formatage du numéro de carte
 */
function setupCardNumberFormatting(form) {
    const cardNumberField = form.querySelector('input[name="card_number"]');
    
    if (cardNumberField) {
        cardNumberField.addEventListener('input', function(e) {
            // Supprimer tous les caractères non numériques
            let value = this.value.replace(/\D/g, '');
            
            // Limiter à 16 chiffres
            value = value.substring(0, 16);
            
            // Formater avec des espaces tous les 4 chiffres
            value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
            
            // Mettre à jour la valeur
            this.value = value;
        });
    }
}

/**
 * Valide le formulaire d'inscription
 */
function validateInscriptionForm(form) {
    const login = form.querySelector('input[name="login"]');
    const password = form.querySelector('input[name="password"]');
    const confirmPassword = form.querySelector('input[name="confirm_password"]');
    const email = form.querySelector('input[name="email"]');
    const nom = form.querySelector('input[name="nom"]');
    const prenom = form.querySelector('input[name="prenom"]');
    
    let isValid = true;
    
    // Réinitialiser les erreurs
    form.querySelectorAll('.error-message').forEach(error => error.remove());
    form.querySelectorAll('.error').forEach(field => field.classList.remove('error'));
    
    // Validation du login
    if (!login.value.trim()) {
        showError(login, 'Le login est requis');
        isValid = false;
    } else if (login.value.length < 3) {
        showError(login, 'Le login doit contenir au moins 3 caractères');
        isValid = false;
    }
    
    // Validation du mot de passe
    if (!password.value) {
        showError(password, 'Le mot de passe est requis');
        isValid = false;
    } else if (password.value.length < 8) {
        showError(password, 'Le mot de passe doit contenir au moins 8 caractères');
        isValid = false;
    } else if (!/[A-Z]/.test(password.value)) {
        showError(password, 'Le mot de passe doit contenir au moins une majuscule');
        isValid = false;
    } else if (!/[0-9]/.test(password.value)) {
        showError(password, 'Le mot de passe doit contenir au moins un chiffre');
        isValid = false;
    }
    
    // Validation de la confirmation du mot de passe
    if (password.value !== confirmPassword.value) {
        showError(confirmPassword, 'Les mots de passe ne correspondent pas');
        isValid = false;
    }
    
    // Validation de l'email
    if (!email.value.trim()) {
        showError(email, 'L\'email est requis');
        isValid = false;
    } else if (!isValidEmail(email.value)) {
        showError(email, 'L\'email n\'est pas valide');
        isValid = false;
    }
    
    // Validation du nom
    if (!nom.value.trim()) {
        showError(nom, 'Le nom est requis');
        isValid = false;
    }
    
    // Validation du prénom
    if (!prenom.value.trim()) {
        showError(prenom, 'Le prénom est requis');
        isValid = false;
    }
    
    return isValid;
}

/**
 * Valide le formulaire de connexion
 */
function validateConnexionForm(form) {
    const login = form.querySelector('input[name="login"]');
    const password = form.querySelector('input[name="password"]');
    
    let isValid = true;
    
    // Réinitialiser les erreurs
    form.querySelectorAll('.error-message').forEach(error => error.remove());
    form.querySelectorAll('.error').forEach(field => field.classList.remove('error'));
    
    // Validation du login
    if (!login.value.trim()) {
        showError(login, 'Le login est requis');
        isValid = false;
    }
    
    // Validation du mot de passe
    if (!password.value) {
        showError(password, 'Le mot de passe est requis');
        isValid = false;
    }
    
    return isValid;
}

/**
 * Valide le formulaire de paiement
 */
function validatePaiementForm(form) {
    const cardNumber = form.querySelector('input[name="card_number"]');
    const cardName = form.querySelector('input[name="card_name"]');
    const expiryMonth = form.querySelector('select[name="expiry_month"]');
    const expiryYear = form.querySelector('select[name="expiry_year"]');
    const cvv = form.querySelector('input[name="cvv"]');
    
    let isValid = true;
    
    // Réinitialiser les erreurs
    form.querySelectorAll('.error-message').forEach(error => error.remove());
    form.querySelectorAll('.error').forEach(field => field.classList.remove('error'));
    
    // Validation du numéro de carte
    if (!cardNumber.value.trim()) {
        showError(cardNumber, 'Le numéro de carte est requis');
        isValid = false;
    } else if (cardNumber.value.replace(/\s/g, '').length !== 16) {
        showError(cardNumber, 'Le numéro de carte doit contenir 16 chiffres');
        isValid = false;
    }
    
    // Validation du nom sur la carte
    if (!cardName.value.trim()) {
        showError(cardName, 'Le nom sur la carte est requis');
        isValid = false;
    }
    
    // Validation de la date d'expiration
    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().getMonth() + 1;
    
    if (!expiryMonth.value) {
        showError(expiryMonth, 'Le mois d\'expiration est requis');
        isValid = false;
    }
    
    if (!expiryYear.value) {
        showError(expiryYear, 'L\'année d\'expiration est requise');
        isValid = false;
    }
    
    if (expiryYear.value && expiryMonth.value) {
        if (parseInt(expiryYear.value) < currentYear || 
            (parseInt(expiryYear.value) === currentYear && parseInt(expiryMonth.value) < currentMonth)) {
            showError(expiryYear, 'La date d\'expiration est dépassée');
            isValid = false;
        }
    }
    
    // Validation du CVV
    if (!cvv.value.trim()) {
        showError(cvv, 'Le code de sécurité est requis');
        isValid = false;
    } else if (!/^\d{3}$/.test(cvv.value)) {
        showError(cvv, 'Le code de sécurité doit contenir 3 chiffres');
        isValid = false;
    }
    
    return isValid;
}

/**
 * Affiche un message d'erreur sous un champ
 */
function showError(field, message) {
    field.classList.add('error');
    
    const errorMessage = document.createElement('div');
    errorMessage.classList.add('error-message');
    errorMessage.textContent = message;
    
    field.parentNode.appendChild(errorMessage);
}

/**
 * Vérifie si un email est valide
 */
function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Processus de paiement via API
 */
function processPaymentAPI() {
    const paymentForm = document.getElementById('paymentForm');
    const submitButton = paymentForm.querySelector('button[type="submit"]');
    const errorElement = document.getElementById('paymentError');
    
    paymentForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Réinitialiser l'erreur
        errorElement.style.display = 'none';
        
        // Vérifier la validité du formulaire
        if (!paymentForm.checkValidity()) {
            e.stopPropagation();
            paymentForm.classList.add('was-validated');
            return;
        }
        
        // Désactiver le bouton pendant le traitement
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Traitement en cours...';
        
        // Préparer les données pour l'API
        const paymentData = {
            cardName: document.getElementById('cardName').value,
            cardNumber: document.getElementById('cardNumber').value.replace(/\s/g, ''),
            expiryMonth: document.getElementById('expiryMonth').value,
            expiryYear: document.getElementById('expiryYear').value,
            cvv: document.getElementById('cvv').value
        };
        
        // Envoyer la requête à l'API
        fetch('/api/payment_api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(paymentData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Rediriger vers la page de confirmation
                window.location.href = data.redirect;
            } else {
                // Afficher l'erreur
                errorElement.textContent = data.message;
                errorElement.style.display = 'block';
                
                // Réactiver le bouton
                submitButton.disabled = false;
                submitButton.textContent = 'Finaliser le paiement';
                
                // Faire défiler jusqu'à l'erreur
                errorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            errorElement.textContent = 'Une erreur est survenue lors du traitement de votre paiement. Veuillez réessayer.';
            errorElement.style.display = 'block';
            
            // Réactiver le bouton
            submitButton.disabled = false;
            submitButton.textContent = 'Finaliser le paiement';
        });
    });
}

// Initialiser les fonctions lorsque le document est chargé
document.addEventListener('DOMContentLoaded', function() {
    setupPasswordVisibility();
    setupCharCounter();
    setupLiveValidation();
    setupLivePaymentValidation();
    processPaymentAPI();
}); 