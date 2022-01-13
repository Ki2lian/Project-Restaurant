import anime from 'animejs/lib/anime.es.js';
$(document).ready(function(){
    var products = [];
    var nbProducts, totalPrice;
    $('#listOfProducts').on('click', '.card', function(){
        const data = productsArray[$(this).parent().index()]
        const posProduct = getPosition(data.id);
        if(posProduct === -1){
            data.quantity = 1;
            products.push(data)
            $('#modalSeeCart .modal-body').append(`
                <div class="border mb-2 p-3 item" id="product-${data.id}">
                    <div class="d-flex justify-content-between">
                        <h4>${data.name}</h4>
                        <div class="remove-product"><i class="fas fa-minus-circle fa-2x"></i></div>
                    </div>
                    <p>${data.description}</p>
                    <div class="quantity d-flex justify-content-between align-items-center border px-2 py-1">
                        <div class="w-75">
                            <button class="qt-minus btn btn-lg">-</button>
                            <span class="qt btn btn-lg">1</span>
                            <button class="qt-plus btn btn-lg">+</button>
                        </div>
                        <div class="w-25 d-flex">
                            <span class="price me-auto">&dollar;${numberFormat(data.price)}</span>
                            <span class="sub-total-price">Sub-total: &dollar;<span>${numberFormat(data.price)}</span></span>
                        </div>
                    </div>
                </div>
            `)
            update()
            return;
        }

        changeQuantity('+', posProduct)
    });

    $('#modalSeeCart .modal-body').on('click', '.qt-minus, .qt-plus', function(){
        const id = $(this).parents('.item').attr('id').split('product-')[1];
        const position = getPosition(id)
        if(position === -1){
            alert('Oups, something went wrong, please reload your page');
            return;
        }

        const type = $(this).attr("class").split(/\s+/)[0] === "qt-plus" ? "+" : "-";
        changeQuantity(type, position);
    });

    $('#modalSeeCart .modal-body').on('click', '.remove-product', function(){
        const id = $(this).parents('.item').attr('id').split('product-')[1];
        const position = getPosition(id)
        removeProduct(position);
    });

    $('#checkout').on('click', function(){
        if(nbProducts <= 0) return;
        $(this).attr('disabled', 'disabled');
        $(this).html('<i class="fas fa-spinner fa-pulse"></i>');
        $.ajax({
            type: "POST",
            url: checkoutURL,
            data: `data=${JSON.stringify(products)}&id=${idRestaurant}`,
            dataType: "json",
            success: function(data){
                $('#modalSeeCart .modal-body').children().fadeOut(500, function(){
                    $('#modalSeeCart .modal-body').children().remove()
                    $('#modalSeeCart .modal-body').append(`
                        <div class="alert alert-success text-center" role="alert">
                            <span>You can find your order on your profile page</span>
                        </div>
                    `);
                    products = [];
                    update();
                });
            }
        })
        .always(() => {
            $(this).html('Checkout');
        })
        .fail(() => {
            $(this).removeAttr("disabled");
        })
    });

    function numberFormat(x){
        return x.toLocaleString('en-US', {minimumFractionDigits: 2})
    }

    function update(){
        nbProducts = 0;
        totalPrice = 0;
        products.forEach(product => {
            nbProducts += product.quantity;
            totalPrice += product.price * product.quantity
            $(`#modalSeeCart .modal-body #product-${product.id} .qt`).html(product.quantity)
            $(`#modalSeeCart .modal-body #product-${product.id} .sub-total-price span`).html( `${numberFormat(product.price * product.quantity)}`)
        });
        
        if(nbProducts === 0) $('#checkout').attr('disabled', 'disabled')
        else $('#checkout').removeAttr('disabled')
        
        $('#nbProducts').html(nbProducts)
        $('#totalPrice').html(numberFormat(totalPrice))
    }

    function changeQuantity(type, pos){
        switch (type) {
            case "+":
                products[pos].quantity++;
                break;
            case "-":
                products[pos].quantity--;
                if(products[pos].quantity === 0) removeProduct(pos);
                break;
            default:
                break;
        }
        update();
    }

    function getPosition(id){
        return products.findIndex((product) => { return product.id === parseInt(id)});
    }

    function removeProduct(pos){
        $(`#modalSeeCart .modal-body #product-${products[pos].id} .qt-minus`).attr('disabled', 'disabled')
        $(`#modalSeeCart .modal-body #product-${products[pos].id} .qt-plus`).attr('disabled', 'disabled')
        anime({
            targets: `#modalSeeCart .modal-body #product-${products[pos].id}`,
            duration: 500,
            translateX: [0, -100],
            opacity: [1, 0],
            complete: function(anim) {
                $(`#modalSeeCart .modal-body #product-${products[pos].id}`).remove();
                products.splice(pos, 1);
                update();
            }
        });
    }
});