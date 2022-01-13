$(document).ready(function(){
    const submitButton = $('#modalProduct .modal-body form .btn-success');
    var index = '';
    const options = {};

    $('.add-product-restaurant').on('click', function(){
        $("#modalProduct .modal-body .card-body .alert").remove();
        options.valueSubmitButton = "Add"
        submitButton.html(options.valueSubmitButton)
        $('#modalProduct .modal-header h5').html('Add product');
        $('#modalProduct .modal-body .info h4').html('Add product');
        $('#modalProduct .modal-body .info p').html('Add products for your restaurant');
        $('#product_name').val('')
        $('#product_price').val('')
        $('#product_description').val('')
        options.url = addProductURL;
        options.type = "POST";
        options.addProduct = true;
    });

    $('#listOfProducts').on('click', '.card .edit-product-restaurant', function(e){
        e.stopPropagation();
        $("#modalProduct .modal-body .card-body .alert").remove();
        options.valueSubmitButton = "Edit"
        submitButton.html(options.valueSubmitButton)
        $('#modalProduct .modal-header h5').html('Modification');
        $('#modalProduct .modal-body .info h4').html('Modification');
        $('#modalProduct .modal-body .info p').html('Edit your product');
        index = $(this).parents(".product").index();
        $('#product_name').val(productsArray[index].name)
        $('#product_price').val(productsArray[index].price)
        $('#product_description').val(productsArray[index].description)
        options.url = editProductURL;
        options.type = "PUT";
        options.addProduct = false;
    });

    $('#modalProduct .modal-body form').on('submit', function(e){
        e.preventDefault();
        if(submitButton.attr("disabled")) return;
        $("#modalProduct .modal-body .card-body .alert").remove();

        submitButton.attr("disabled", "disabled");
        submitButton.html('<i class="fas fa-spinner fa-pulse"></i>');
        var data = [$(this).serialize(),$.param({"idRestaurant": idRestaurant})].join('&');
        if(!options.addProduct) data = [$(this).serialize(),$.param({"idRestaurant": idRestaurant, "id": productsArray[index].id})].join('&')
        
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
                    $(errorHTML).insertAfter("#modalProduct .modal-body .card-body .info");
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
                    $(errorHTML).insertAfter("#modalProduct .modal-body .card-body .info");
                }else{
                    $(`
                    <div class="alert alert-success" role="alert">
                        ${data.message}
                    </div>
                    `).insertAfter("#modalProduct .modal-body .card-body .info");
                    
                    description = '';
                    if(data.info.description.length > 0) description = `<p>${data.info.description}</p>`;
                    if(options.addProduct){
                        $('#modalProduct .modal-body .card-body form input').val('');
                        $('#listOfProducts .alert').remove();
                        $('#listOfProducts').append(`
                            <div class="col-xl-4 col-lg-6 col-md-6 mb-4 product">
                                <div class="card" title="&dollar;${numberFormat(data.info.price)} ${data.info.name}">
                                    <div class="add-product"><i class="fas fa-plus-circle fa-2x"></i></div>
                                    <div class="card-body d-flex justify-content-center align-items-center text-center p-5 flex-column">
                                        <span class="text-dark">${data.info.name} <span class="text-muted h5">&dollar;${numberFormat(data.info.price)}</span>
                                            <button type="button" class="btn text-primary p-1 edit-product-restaurant" data-bs-toggle="modal" data-bs-target="#modalProduct"><i class="fas fa-edit fa-lg mb-2"></i></button>
                                            <button type="button" class="btn text-danger delete p-1"><i class="fas fa-trash fa-lg mb-2"></i></button>
                                        </span>
                                        ${description}
                                    </div>
                                </div>
                            </div>
                        `)
                        productsArray.push({id: data.info.id, price: data.info.price, name: data.info.name, description: data.info.description})
                        $('#product__token').val(data.info.csrfToken)
                    }else{
                        var cards = $('#listOfProducts .card').eq(index);
                        cards.attr('title', `&dollar;${numberFormat(data.info.price)} ${data.info.name}`)
                        cards.find('.card-body').children().remove()
                        cards.find('.card-body').append(`
                            <span class="text-dark">${data.info.name} <span class="text-muted h5">&dollar;${numberFormat(data.info.price)}</span>
                                <button type="button" class="btn text-primary p-1 edit-product-restaurant" data-bs-toggle="modal" data-bs-target="#modalProduct"><i class="fas fa-edit fa-lg mb-2"></i></button>
                                <button type="button" class="btn text-danger delete p-1"><i class="fas fa-trash fa-lg mb-2"></i></button>
                            </span>
                            ${description}
                        `);
                        productsArray[index].name = data.info.name;
                        productsArray[index].price = data.info.price;
                        productsArray[index].description = data.info.description;
                        $('.see-cart').fadeOut(500, function(){
                            $('.see-cart').remove();
                            alert('The cart is hidden because there have been modifications on one of the products');
                        });
                    }
                }
            }
        })
        .always(function(){
            submitButton.removeAttr("disabled");
            submitButton.html(options.valueSubmitButton);
        })
    });

    $('#listOfProducts').on('click', '.card .delete', function(e){
        e.stopPropagation();
        if($(this).attr("disabled")) return;
        index = $(this).parents(".product").index();
        if(window.confirm(`Are you sure you want to delete the product (${productsArray[index].name}) ?`)){
            $(this).attr("disabled", "disabled");
            $(this).html('<i class="fas fa-spinner fa-pulse"></i>');
            $(this).parents('.product').find('.edit-product-restaurant').attr("disabled", "disabled");
            $.ajax({
                type: 'DELETE',
                url: deleteProductURL,
                data: `idRestaurant=${idRestaurant}&id=${productsArray[index].id}`,
                dataType: "json",
                success: (data) => {
                    $(this).parents('.product').fadeOut(500, () => {
                        $(this).parents('.product').remove();
                        productsArray.splice(index, 1);

                        if(productsArray.length == 0){
                            $('#listOfProducts').append(`
                                <div class="alert alert-info text-center" role="alert">
                                    <span>There is no product available for this restaurant, check back later, <a class="fw-bold link" href="${homeURL}">choose another restaurant</a></span>
                                </div>
                            `);
                        }
                    });
                    $('.see-cart').fadeOut(500, function(){
                        $('.see-cart').remove();
                        alert('The cart is hidden because there have been modifications on one of the products');
                    });
                }
            })
        }

    });

    function numberFormat(x){
        return x.toLocaleString('en-US', {minimumFractionDigits: 2})
    }

});