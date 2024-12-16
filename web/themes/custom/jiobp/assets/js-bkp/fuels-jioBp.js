$(document).ready(function() {
    var url = $("#video").attr('src');
    $("#myModal").on('hide.bs.modal', function() {
        $("#video").attr('src', '');
    });
    $("#myModal").on('show.bs.modal', function() {
        $("#video").attr('src', url);
    });
});