function toggleDay(dayIndex) {
    const content = document.getElementById('day-content-' + dayIndex);
    if (content) { // Vérifier si content existe
        const header = content.previousElementSibling;
        if (header) { // Vérifier si header existe
            const icon = header.querySelector('.toggle-icon');
            if (icon) { // Vérifier si icon existe
                if (content.classList.contains('collapsed')) {
                    content.classList.remove('collapsed');
                    icon.textContent = '▼';
                } else {
                    content.classList.add('collapsed');
                    icon.textContent = '►';
                }
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const contents = document.querySelectorAll('.day-content');
    contents.forEach(content => {
        const header = content.previousElementSibling;
        if (header) { // Vérifier si header existe
            const icon = header.querySelector('.toggle-icon');
            if (icon) { // Vérifier si icon existe
                if (content.classList.contains('collapsed')) {
                    icon.textContent = '►';
                } else {
                    icon.textContent = '▼';
                }
            }
        }
    });
}); 