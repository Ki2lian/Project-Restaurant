$(document).ready(function(){
    const submitButton = $('#modalEditPassword .modal-body form .btn-success')
    $('#modalEditPassword .modal-body form').on('submit', function(e){
        e.preventDefault();
        if(submitButton.attr("disabled")) return;

        submitButton.attr("disabled", "disabled");
        submitButton.html('<i class="fas fa-spinner fa-pulse"></i>');
        var data = $(this).serialize();
        $.ajax({
            type: "PUT",
            url: editPasswordRoute,
            data,
            dataType: "json",
            success: function(data){
                $("#modalEditPassword .modal-body .card-body .alert").remove();
                if(data.errors){
                    var errors = "";
                    data.errors.forEach(error => {
                        errors += `<li>${error.message}</li>`;
                    });

                    var errorHTML = `
                        <div class="alert alert-danger" role="alert">
                            <ul>
                                ${errors}
                            </ul>
                        </div>
                    `;
                    $(errorHTML).insertAfter("#modalEditPassword .modal-body .card-body .info");
                }else{
                    $("#edit_password_form_old_password").val('');
                    $("#edit_password_form_new_password").val('');
                    $(`
                    <div class="alert alert-success" role="alert">
                        ${data.message}
                    </div>
                    `).insertAfter("#modalEditPassword .modal-body .card-body .info")
                }
            }
        })
        .always(function(){
            submitButton.removeAttr("disabled");
            submitButton.html('Save changes');
        })
    });
});