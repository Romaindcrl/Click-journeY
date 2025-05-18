document.addEventListener('DOMContentLoaded', function() {
    const dayHeaders = document.querySelectorAll('.day-header');

    dayHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const dayContent = this.nextElementSibling;
            const toggleIcon = this.querySelector('.toggle-icon');

            if (dayContent && dayContent.classList.contains('day-content')) {
                dayContent.classList.toggle('collapsed');

                if (toggleIcon) {
                    if (dayContent.classList.contains('collapsed')) {
                        toggleIcon.textContent = '▼';
                    } else {
                        toggleIcon.textContent = '▲';
                    }
                }
            }
        });
    });
}); 