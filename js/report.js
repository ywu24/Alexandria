/**
 * Alexandria Library Management System
 *
 * @file Report submission page — character counters, image preview, file deletion
 */

(function () {
    document.addEventListener('DOMContentLoaded', function () {
        const oggetto = document.getElementById('oggetto');
        const oggettoCounter = document.getElementById('oggetto-counter');
        if (oggetto && oggettoCounter) {
            oggetto.addEventListener('input', function () {
                oggettoCounter.innerText = 'Caratteri rimanenti: ' + (50 - this.value.length);
            });
        }

        const messaggio = document.getElementById('messaggio');
        const messaggioCounter = document.getElementById('messaggio-counter');
        if (messaggio && messaggioCounter) {
            messaggio.addEventListener('input', function () {
                messaggioCounter.innerText = 'Caratteri rimanenti: ' + (250 - this.value.length);
            });
        }
    });

    window.previewImage = function (event) {
        var input = event.target;
        var preview = document.getElementById('image-preview');
        var trash = document.getElementById('trash-btn');
        var label = input.nextElementSibling;

        if (input.files && input.files[0]) {
            var fileName = input.files[0].name;

            if (label && label.classList.contains('custom-file-label')) {
                label.classList.add('selected');
                label.textContent = fileName;
            }

            var reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
                preview.classList.add('d-inline-block');
                trash.classList.remove('d-none');
                trash.classList.add('d-block');
            };
            reader.readAsDataURL(input.files[0]);
        }
    };

    window.deleteImage = function () {
        var preview = document.getElementById('image-preview');
        var input = document.getElementById('file');
        var trash = document.getElementById('trash-btn');
        var label = document.querySelector('.custom-file-label');

        preview.src = '#';
        preview.classList.remove('d-inline-block');
        preview.classList.add('d-none');
        trash.classList.remove('d-block');
        trash.classList.add('d-none');
        input.value = '';
        if (label) {
            label.classList.remove('selected');
            label.textContent = 'Scegli file...';
        }
    };
})();
