document.addEventListener('DOMContentLoaded', function() {
    var el = document.getElementById('personnalisation-data');
    if (el) {
        try {
            window.ACTIVITES_DATA = JSON.parse(el.dataset.activites);
        } catch (e) {
            window.ACTIVITES_DATA = [];
        }
        window.DUREE = parseInt(el.dataset.duree, 10) || 0;
        try {
            window.INITIAL_COUNTS = JSON.parse(el.dataset.initialCounts);
        } catch (e) {
            window.INITIAL_COUNTS = {};
        }
    }
}); 