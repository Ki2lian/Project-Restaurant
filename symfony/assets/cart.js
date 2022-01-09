$(document).ready(function(){
    var products = [];
    var nbProducts = 0;
    $('#listOfProducts .card').on('click', function(){
        const data = $(this).data();
        const posProduct = products.findIndex((product) => { return product.id === data.id});
        nbProducts++;
        if(posProduct === -1){
            data.quantity = 1;
            products.push(data)
            $('#modalSeeCart .modal-body').append(`
                <div class="border mb-2 p-3" id="product-${data.id}">
                    <h4>${data.name}</h4>
                    <p>${data.description}</p>
                    <div class="quantity d-flex justify-content-between align-items-center border px-2 py-1">
                        <div class="w-75">
                            <button class="qt-minus btn btn-lg">-</button>
                            <span class="qt btn btn-lg">1</span>
                            <button class="qt-plus btn btn-lg">+</button>
                        </div>
                        <div class="w-25 d-flex">
                            <span class="price me-auto">${numberFormat(data.price)}&euro;</span>
                            <span class="sub-total-price">Sous-total: <span>${numberFormat(data.price)}&euro;</span></span>
                        </div>
                    </div>
                </div>
            `)
            update()
            return;
        }

        products[posProduct].quantity += 1;
        $(`#modalSeeCart .modal-body #product-${data.id} .qt`).html(products[posProduct].quantity)
        $(`#modalSeeCart .modal-body #product-${data.id} .sub-total-price span`).html( `${numberFormat(products[posProduct].price * products[posProduct].quantity)}&euro;`)
        update()
        

    });

    $('#modalSeeCart .modal-body').on('click', '.qt-minus', function(){
        console.log("qt minus")
        // changePrice();
    })

    function numberFormat(x){
        return x.toLocaleString('fr-FR', {minimumFractionDigits: 2})
    }

    function update(){
        $('#nbProducts').html(nbProducts)
    }

});