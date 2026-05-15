// Conteggio dei caratteri rimanenti
document.getElementById('titolo').addEventListener('input', function () {
    var counter = document.getElementById('titolo-counter');
    counter.innerText = 'Caratteri rimanenti: ' + (50 - this.value.length);
});

document.getElementById('messaggio').addEventListener('input', function () {
    var counter = document.getElementById('messaggio-counter');
    counter.innerText = 'Caratteri rimanenti: ' + (500 - this.value.length);
});

$(document).ready(function() {
    if (typeof puntiGuadagnati !== 'undefined' && puntiGuadagnati) {
        const pointsAlert = $(`
            <div class="alert alert-warning position-fixed top-0 end-0 m-3 shadow-md fw-bold fs-md" role="alert" style="z-index: 9999;">
                <i class="fas fa-coins me-2"></i> +5 Punti guadagnati!
            </div>
        `);

        $('body').append(pointsAlert);

        pointsAlert.fadeIn(500).delay(3000).fadeOut(500, function() {
            $(this).remove();
        });
    }
});
