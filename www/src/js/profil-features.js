function toggleOptions(id) {
    var content = document.getElementById('options-' + id);
    if (content) {
        content.classList.toggle('collapsed');
    }
} 