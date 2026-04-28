// Conteggio dei caratteri rimanenti
    document.getElementById('titolo').addEventListener('input', function () {
      var counter = document.getElementById('titolo-counter');
      counter.innerText = 'Caratteri rimanenti: ' + (50 - this.value.length);
    });

    document.getElementById('messaggio').addEventListener('input', function () {
      var counter = document.getElementById('messaggio-counter');
      counter.innerText = 'Caratteri rimanenti: ' + (500 - this.value.length);
    }); 



