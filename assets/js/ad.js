$('#add-image').click(function () {
    const index = +$('#widgets-counter').val();
    const tmpl = $('#annonce_images').data('prototype').replace(/_name_/g, index);

    $('#widgets-counter').val(index + 1);


    $('#annonce_images').append(tmpl);

    handleDeleteButtons();

});
function handleDeleteButtons() {
    $('button[data-action="delete"]').click(function() {
        const target = this.dataset.target;
        $(target).remove();
    });

}
