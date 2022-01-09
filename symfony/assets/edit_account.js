$(document).ready(function(){
    const submitButton = $('#modalEditAccount .modal-body form .btn-success')
    $('#modalEditAccount .modal-body form').on('submit', function(e){
        e.preventDefault();
        if(submitButton.attr("disabled")) return;

        submitButton.attr("disabled", "disabled");
        submitButton.html('<i class="fas fa-spinner fa-pulse"></i>');
        var data = $(this).serialize();
        $.ajax({
            type: "POST",
            url: editAccountRoute,
            data,
            dataType: "json",
            success: function(data){
                $("#modalEditAccount .modal-body .card-body .alert").remove();
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
                    $(errorHTML).insertAfter("#modalEditAccount .modal-body .card-body .info");
                }else{
                    $("#edit_account_form_plainPassword").val('');
                    $(`
                    <div class="alert alert-success" role="alert">
                        ${data.message}
                    </div>
                    `).insertAfter("#modalEditAccount .modal-body .card-body .info")
                    $('#firstname').html(data.info.firstname)
                    $('#lastname').html(data.info.lastname)
                } 
            }
        })
        .always(function(){
            submitButton.removeAttr("disabled");
            submitButton.html('Save changes');
        })
    });
});