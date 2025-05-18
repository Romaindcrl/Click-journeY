
        // Gestion du thème
        const themeToggle = document.getElementById('theme-toggle');
        const htmlElement = document.documentElement;

        // Vérifier le thème stocké ou utiliser le thème clair par défaut
        const currentTheme = localStorage.getItem('theme') || 'light';
        htmlElement.setAttribute('data-theme', currentTheme);
        themeToggle.checked = currentTheme === 'dark';

        // Changer le thème lorsque l'utilisateur clique sur le toggle
        themeToggle.addEventListener('change', function() {
            const theme = this.checked ? 'dark' : 'light';
            htmlElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
        });
