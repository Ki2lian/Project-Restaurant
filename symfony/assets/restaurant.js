$(document).ready(function(){
    const submitButton = $('#modalRestaurant .modal-body form .btn-success');
    var index = '';
    const options = {};

    $('.restaurants .add-restaurant').on('click', function(){
        $("#modalRestaurant .modal-body .card-body .alert").remove();
        options.valueSubmitButton = "Add"
        submitButton.html(options.valueSubmitButton)
        $('#modalRestaurant .modal-header h5').html('Add restaurant');
        $('#modalRestaurant .modal-body .info h4').html('Add restaurant');
        $('#modalRestaurant .modal-body .info p').html('After adding your restaurant, consider adding products to it');
        $('#restaurant_name').val('')
        $('#restaurant_address').val('')
        $('#restaurant_phone').val('')
        options.url = addRestaurantURL;
        options.type = "POST";
        options.addRestaurant = true;
    });

    $('.restaurants .edit-restaurant').on('click', function(){
        $("#modalRestaurant .modal-body .card-body .alert").remove();
        options.valueSubmitButton = "Edit"
        submitButton.html(options.valueSubmitButton)
        $('#modalRestaurant .modal-header h5').html('Modification');
        $('#modalRestaurant .modal-body .info h4').html('Modification');
        $('#modalRestaurant .modal-body .info p').html('Edit your restaurant');
        index = $(this).parents("tr").index();
        $('#restaurant_name').val(restaurantsArray[index].name)
        $('#restaurant_address').val(restaurantsArray[index].address)
        $('#restaurant_phone').val(restaurantsArray[index].phone)
        options.url = editRestaurantURL;
        options.type = "PUT";
        options.addRestaurant = false;
    });

    $('#modalRestaurant .modal-body form').on('submit', function(e){
        e.preventDefault();
        if(submitButton.attr("disabled")) return;
        $("#modalRestaurant .modal-body .card-body .alert").remove();

        submitButton.attr("disabled", "disabled");
        submitButton.html('<i class="fas fa-spinner fa-pulse"></i>');
        var data = $(this).serialize();
        if(!options.addRestaurant) data = [$(this).serialize(),$.param({"id": restaurantsArray[index].id})].join('&')
        
        $.ajax({
            type: options.type,
            url: options.url,
            data,
            dataType: "json",
            success: function(data){
                if(data.code != 200){
                    var errorHTML = `
                        <div class="alert alert-danger" role="alert">
                            <ul>
                                ${data.message}
                            </ul>
                        </div>
                    `;
                    $(errorHTML).insertAfter("#modalRestaurant .modal-body .card-body .info");
                    return;
                }
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
                    $(errorHTML).insertAfter("#modalRestaurant .modal-body .card-body .info");
                }else{
                    $(`
                    <div class="alert alert-success" role="alert">
                        ${data.message}
                    </div>
                    `).insertAfter("#modalRestaurant .modal-body .card-body .info");

                    if(options.addRestaurant){
                        $('#modalRestaurant .modal-body .card-body form input').val('');
                        setInterval(() => {
                            location.reload();
                        }, 3000)
                    }else{
                        var tds = $('.restaurants .card-body table tbody tr').eq(index).find('td');
                        tds.eq(0).html(data.info.name)
                        tds.eq(1).html(data.info.address)
                        tds.eq(2).html(data.info.phone)
                        tds.eq(4).html(data.info.updatedAt)
                        restaurantsArray[index].name = data.info.name;
                        restaurantsArray[index].address = data.info.address;
                        restaurantsArray[index].phone = data.info.phone;

                    }
                }
            }
        })
        .always(function(){
            submitButton.removeAttr("disabled");
            submitButton.html(options.valueSubmitButton);
        })
    });

    $('.restaurants .delete').on('click', function(){
        if($(this).attr("disabled")) return;
        index = $(this).parents("tr").index();
        if(window.confirm(`Are you sure you want to delete the restaurant (${restaurantsArray[index].name}) ?`)){
            $(this).attr("disabled", "disabled");
            $(this).html('<i class="fas fa-spinner fa-pulse"></i>');
            $(this).parents('td').find('edit-restaurant').attr("disabled", "disabled");
            $.ajax({
                type: 'DELETE',
                url: deleteRestaurantURL,
                data: `id=${restaurantsArray[index].id}`,
                dataType: "json",
                success: (data) => {
                    $(this).parents('tr').fadeOut(500, () => {
                        $(this).parents('tr').remove();
                        restaurantsArray.splice(index, 1)
                    });
                }
            })
        }

    });

});