function Check_next() {
    var wanted = document.getElementsByName("slider");
    for (var i = 0; i < wanted.length; ++i) {
        if (wanted[i].checked == true) {
            if (i == wanted.length - 1) {
                wanted[0].checked = true;
            } else {
                wanted[i + 1].checked = true;
            }
            break;
        }
    }
}

setInterval(function () {
    Check_next()
}, 5000)