document.getElementById('oggetto').addEventListener('input', function () {
    document.getElementById('oggetto-counter').innerText = 'Caratteri rimanenti: ' + (50 - this.value.length);
});

document.getElementById('messaggio').addEventListener('input', function () {
    document.getElementById('messaggio-counter').innerText = 'Caratteri rimanenti: ' + (250 - this.value.length);
});

function previewImage(event) {
    var input = event.target;
    var preview = document.getElementById('image-preview');
    var trash = document.getElementById('trash-btn');
    var label = $(input).siblings(".custom-file-label");

    if (input.files && input.files[0]) {
        var reader = new FileReader();
        var fileName = input.files[0].name;

        label.addClass("selected").html(fileName);

        reader.onload = function (e) {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
            preview.classList.add('d-inline-block');
            trash.classList.remove('d-none');
            trash.classList.add('d-block');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function deleteImage() {
    var preview = document.getElementById('image-preview');
    var input = document.getElementById('file');
    var trash = document.getElementById('trash-btn');
    var label = $(".custom-file-label");

    preview.src = '#';
    preview.classList.remove('d-inline-block');
    preview.classList.add('d-none');
    trash.classList.remove('d-block');
    trash.classList.add('d-none');
    input.value = '';
    label.removeClass("selected").html("Scegli file...");
}
