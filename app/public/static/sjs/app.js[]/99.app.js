$('form').submit(function () {
    $('button, [type=submit]').each(function () {
        this.disabled = true;
    });
});

$('input, textarea').attr('autocomplete', 'off-' + (new Date()).getTime());

$(document).ready(function () {
    $('.close-popup').on('click', function () {
        $.get(LOCALED_ROOT_URL + 'ocultar-popup/');
        $(this).parents('.panel').first().hide();
    });
})
