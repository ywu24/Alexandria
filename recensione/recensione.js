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
    // Se la variabile puntiGuadagnati esiste ed è true, mostriamo l'alert
    if (typeof puntiGuadagnati !== 'undefined' && puntiGuadagnati) {
        // Creiamo un elemento visivo dinamico per i punti
        const pointsAlert = $(`
            <div style="position: fixed; top: 20px; right: 20px; background-color: #ffc107; color: #fff; padding: 15px 25px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); z-index: 9999; font-weight: bold; font-size: 1.1em; display: none;">
                <i class="fas fa-coins"></i> +5 Punti guadagnati!
            </div>
        `);
        
        $('body').append(pointsAlert);
        
        // Effetto di apparizione e scomparsa
        pointsAlert.fadeIn(500).delay(3000).fadeOut(500, function() {
            $(this).remove();
        });
    }
  }); 

