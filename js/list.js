/**
 * @file list.js
 * @description Gestisce l'interattività lato client per la barra laterale dei filtri del catalogo.
 * Utilizza un IIFE (Immediately Invoked Function Expression) per incapsulare le variabili.
 * Aspetta il caricamento del DOM e applica dei listener per eventi 'click' agli elementi 
 * trigger ("sort-by" e "genere-trigger"). Al click, alterna la visibilità (display: block/none)
 * dei rispettivi sotto-menu ("left-container" e "genere-subwrap") per creare un effetto a tendina.
 */
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
