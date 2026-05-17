(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var input = document.querySelector('.custom-file-input');
        if (!input) return;
        input.addEventListener('change', function () {
            var fileName = this.value.split('\\').pop();
            this.nextElementSibling.classList.add('selected');
            this.nextElementSibling.textContent = fileName;
        });
    });
})();
