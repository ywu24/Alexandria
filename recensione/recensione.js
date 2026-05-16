document.getElementById('titolo').addEventListener('input', function () {
    var counter = document.getElementById('titolo-counter');
    counter.innerText = 'Caratteri rimanenti: ' + (50 - this.value.length);
});

document.getElementById('messaggio').addEventListener('input', function () {
    var counter = document.getElementById('messaggio-counter');
    counter.innerText = 'Caratteri rimanenti: ' + (500 - this.value.length);
});

document.addEventListener('DOMContentLoaded', function () {
    if (typeof puntiGuadagnati !== 'undefined' && puntiGuadagnati) {
        var pointsAlert = document.createElement('div');
        pointsAlert.className = 'alert alert-warning position-fixed top-0 end-0 m-3 shadow-md fw-bold fs-md';
        pointsAlert.setAttribute('role', 'alert');
        pointsAlert.style.cssText = 'z-index: 9999; opacity: 0; transition: opacity 0.5s;';
        pointsAlert.innerHTML = '<i class="fas fa-coins me-2"></i> +5 Punti guadagnati!';

        document.body.appendChild(pointsAlert);

        requestAnimationFrame(function () {
            pointsAlert.style.opacity = '1';
        });

        setTimeout(function () {
            pointsAlert.style.opacity = '0';
            setTimeout(function () {
                pointsAlert.remove();
            }, 500);
        }, 3500);
    }
});
