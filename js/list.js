(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var sortBy = document.getElementById('sort-by');
        var leftContainer = document.getElementById('left-container');
        if (sortBy && leftContainer) {
            sortBy.addEventListener('click', function () {
                leftContainer.style.display = leftContainer.style.display === 'none' ? 'block' : 'none';
            });
        }

        var genereTrigger = document.getElementById('genere-trigger');
        var genereSubwrap = document.getElementById('genere-subwrap');
        if (genereTrigger && genereSubwrap) {
            genereTrigger.addEventListener('click', function () {
                genereSubwrap.style.display = genereSubwrap.style.display === 'none' ? 'block' : 'none';
            });
        }
    });
})();
