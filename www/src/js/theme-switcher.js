/**
 * Script pour gérer le thème de l'application (clair/sombre)
 */
document.addEventListener('DOMContentLoaded', function() {
    const themeSwitch = document.getElementById('theme-switch');
    const themeLabel = document.querySelector('.theme-label');
    
    if (!themeSwitch) return;
    
    // Fonction pour appliquer le thème
    function applyTheme(isDark) {
        if (isDark) {
            document.body.classList.add('dark-mode');
            themeSwitch.checked = true;
            if (themeLabel) {
                themeLabel.textContent = 'Mode clair';
            }
        } else {
            document.body.classList.remove('dark-mode');
            themeSwitch.checked = false;
            if (themeLabel) {
                themeLabel.textContent = 'Mode sombre';
            }
        }
    }
    
    // Vérifier le thème stocké dans localStorage
    const storedTheme = localStorage.getItem('darkMode');
    
    // Appliquer le thème selon la valeur stockée
    applyTheme(storedTheme === 'true');
    
    // Gérer le changement de thème
    themeSwitch.addEventListener('change', function() {
        const isDark = this.checked;
        localStorage.setItem('darkMode', isDark);
        applyTheme(isDark);
    });
    
    // Gestion des flash messages
    const flashMessages = document.querySelectorAll('.flash-message');
    
    flashMessages.forEach(message => {
        // Faire disparaître le message après 3 secondes
        setTimeout(() => {
            message.style.opacity = '0';
            
            // Supprimer le message après la transition d'opacité
            setTimeout(() => {
                message.remove();
            }, 500); // Correspond au temps de transition CSS
        }, 3000);
        
        // Ajouter un bouton de fermeture
        const closeBtn = document.createElement('span');
        closeBtn.innerHTML = '&times;';
        closeBtn.className = 'flash-close';
        message.appendChild(closeBtn);
        
        // Gérer le clic sur le bouton de fermeture
        closeBtn.addEventListener('click', () => {
            message.style.opacity = '0';
            setTimeout(() => {
                message.remove();
            }, 500);
        });
    });
}); 