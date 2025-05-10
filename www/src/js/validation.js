document.addEventListener('DOMContentLoaded', function() {
    // Ajouter un bouton show/hide pour tous les champs password
    var passwordFields = document.querySelectorAll('input[type="password"]');
    passwordFields.forEach(function(field) {
        // Créer un wrapper positionné pour l'icon toggle
        var wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        field.parentNode.insertBefore(wrapper, field);
        wrapper.appendChild(field);
        // Créer le bouton toggle
        var toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'password-toggle';
        toggle.innerHTML = '<i class="fas fa-eye"></i>';
        toggle.style.position = 'absolute';
        toggle.style.top = '50%';
        toggle.style.right = '10px';
        toggle.style.transform = 'translateY(-50%)';
        toggle.style.background = 'none';
        toggle.style.border = 'none';
        toggle.style.padding = '0';
        toggle.style.cursor = 'pointer';
        wrapper.appendChild(toggle);
        // Gérer le clic pour basculer le type
        toggle.addEventListener('click', function() {
            if (field.type === 'password') {
                field.type = 'text';
                toggle.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                field.type = 'password';
                toggle.innerHTML = '<i class="fas fa-eye"></i>';
            }
        });
    });
    var forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(event) {
            var valid = true;
            // Remove existing error messages
            form.querySelectorAll('.error-message').forEach(function(msg) {
                msg.parentNode.removeChild(msg);
            });
            // Validate each input, textarea, select
            var elements = form.querySelectorAll('input, textarea, select');
            elements.forEach(function(input) {
                input.classList.remove('input-error');
                var value = input.value.trim();
                var type = input.type.toLowerCase();
                if (input.hasAttribute('required') && !value) {
                    valid = false;
                    showError(input, 'Ce champ est requis');
                } else if (type === 'email' && value) {
                    var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!re.test(value)) {
                        valid = false;
                        showError(input, 'Adresse email invalide');
                    }
                } else if (type === 'number' && value) {
                    if (isNaN(value)) {
                        valid = false;
                        showError(input, 'Veuillez saisir un nombre valide');
                    }
                } else if (type === 'password' && input.name === 'password' && value.length < 6) {
                    valid = false;
                    showError(input, 'Le mot de passe doit contenir au moins 6 caractères');
                } else if (input.id === 'confirm_password' && value) {
                    var pwd = form.querySelector('input[name="password"]');
                    if (pwd && value !== pwd.value) {
                        valid = false;
                        showError(input, 'Les mots de passe ne correspondent pas');
                    }
                }
            });
            if (!valid) {
                event.preventDefault();
            }
        });
    });
    function showError(input, message) {
        input.classList.add('input-error');
        var error = document.createElement('div');
        error.className = 'error-message';
        error.innerText = message;
        input.parentNode.insertBefore(error, input.nextSibling);
    }
}); 