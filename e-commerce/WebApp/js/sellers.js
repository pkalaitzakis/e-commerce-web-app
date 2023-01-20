const productCard = document.querySelector("[data-product-card-template]");
const productGrid = document.querySelector("[data-product-grid]");
let products=[];
let sellerid;
let view = document.querySelector('.seller-view');
let currently_displayed=[];
const forwards = document.querySelector(".forwards-button");
const backwards = document.querySelector(".backwards-button");
backwards.style.display='none';

backwards.addEventListener("click", e => {
    let changed=0;
    let fits = calcColumns();
    let found=false;

    products.reverse().every(product =>{
        if(product.element.offsetLeft>0){
            product.element.style.display='none';
            currently_displayed.pop();
            found=true;
            return true;
        } else if(found && changed<fits){
            $(product.element).fadeIn(1000);
            currently_displayed.unshift(product.element);
            changed+=1;
            if(product.element.id===products.at(-1).element.id){
                backwards.style.display='none';
            }
            forwards.style.display='flex';
            return true;
        } else return !found;
    })
    products.reverse();
})

forwards.addEventListener("click", e => {
    // mark the number of 'next' products to be shown on screen
    let shown = 0;
    // calculate how many product cards fit on current view size
    let fits = calcColumns();
    // flag set when first displayed product was found
    let found=false;
    // start iterating through seller's product list
    products.every(product =>{
        // when first displayed element is encountered
        if(product.element.offsetLeft>0){
            // set display of old product to none
            product.element.style.display='none';
            // remove element from currently_displayed list
            currently_displayed.shift();
            // set found flag to true
            found=true;
            // keep going
            return true;
        } else if(found && shown<fits){
            $(product.element).fadeIn(1000);
            currently_displayed.push(product.element);
            const img = product.element.querySelector(".image_view").querySelector('img');
            img.src = product.IMG_NAME;
            shown+=1;
            if(product.element.id===products.at(-1).element.id){
                forwards.style.display='none';
            }
            backwards.style.display='flex';
            return true;
        } else
            return !found;
    })
})


addEventListener("resize", e=> {
    view = document.querySelector('.seller-view');
    let fits = calcColumns();
    console.log(fits, currently_displayed.length);
    if (fits<currently_displayed.length){
        var last_card = currently_displayed.pop();
        $(last_card).fadeOut(500);
    }
    if(currently_displayed.at(-1).id===products.at(-1).element.id){
        forwards.style.display='none';
    } else {
        forwards.style.display='flex';
    }
    if (currently_displayed.at(0).id===products.at(0).element.id){
        backwards.style.display='none';
    } else {
        backwards.style.display='flex';
    }
})

function calcColumns(){
    const view_width = document.querySelector('.seller-view').offsetWidth;
    const card_width = 300;
    return Math.floor(view_width/card_width);
}

function showForm(){
    $('.add_product_container img').hide();
    $('#new_product_card').show();
    $('#save_product').show();
    $('.image_view img').show();
}

function hideForm(){
    $('#new_product_card input').val('');
    $('#new_product_card').hide();
    $('.add_product_container img').show();
    const card = document.querySelector('#new_product_card');
    card.querySelector('#product_img').src='/assets/placeholder.png';
}

function loadProductImage(){
    const input_img = document.querySelector('#image_file');
    let freader = new FileReader();
    freader.readAsDataURL(input_img.files[0]);
    freader.onloadend = function(event){
        const current_img = document.querySelector('#product_img');
        current_img.src = event.target.result;
    }
}

async function addProduct() {
    const input_seller_name = username;
    const input_seller_id = sellerid;
    const input_product_name = document.querySelector('#input_product_name').value;
    const input_product_category = document.querySelector('#input_product_category').value;
    const input_product_code = document.querySelector('#input_product_code').value;
    const input_product_price = document.querySelector('#input_product_price').value;
    const input_image_src = document.querySelector('#image_file').files;

    if(!input_product_name){
        console.log(products);
        return;
    }
    if(!input_product_category){
        console.log('Product Category is mandatory');
        return;
    }
    if(!input_product_price){
        console.log('Product Price is mandatory');
        return;
    }
    if(!input_product_code){
        console.log('Product Code is mandatory');
        return;
    }

    // group provided input
    let post_data = new FormData();
    post_data.append("token", access_token);
    post_data.append('request_id', '210');
    post_data.append('id', input_seller_id);
    post_data.append('name', input_product_name);
    post_data.append('category', input_product_category);
    post_data.append('seller', input_seller_name);
    post_data.append('code', input_product_code);
    post_data.append('price', input_product_price);
    post_data.append('image', input_image_src[0])

    // post input and fetch db response (new selection query is executed
    // after update, to ensure fields were successfully updated on sever side)
    let db_id, db_name, db_category, db_seller, db_code, db_price, db_img;
    await fetch('http://localhost:81/mongo_request.php', {method: 'POST', body: post_data})
        .then((response) => response.json())
        .then((answer) => {
            db_id=answer.product_id;
            db_name=answer.product_name;
            db_category=answer.product_category;
            db_seller=answer.product_seller;
            db_code=answer.product_code;
            db_price=answer.product_price;
            db_img=answer.product_img;
        });
    // create a new product card in product view with the data retrieved from database insertion response
    const card = productCard.content.cloneNode(true).children[0];
    card.id = db_id;
    const name = card.querySelector('[data-product-name]')
    name.textContent= db_name;
    const product_category = card.querySelector('[data-product-category]');
    product_category.textContent = db_category;
    const price = card.querySelector('[data-product-price]');
    price.textContent = db_price;
    card.querySelector('[data-product-code]').textContent = db_code;
    card.querySelector('[data-product-merchant]').textContent = db_seller;
    // append the card into the grid
    productGrid.append(card);
    add_listeners(card, name, product_category, price);

    // and the total product array for js usage
    products.push({ IMG_NAME: db_img,
        CATEGORY: db_category,
        NAME: db_name,
        PRODUCTCODE: db_code,
        SELLERNAME: db_seller,
        PRICE: db_price,
        WITHDRAWAL_DATE: null,
        element: card});
    // check if the card fits in the current grid
    if(view.offsetWidth<card.offsetLeft+card.offsetWidth){
        card.style.display='none';
        forwards.style.display='flex';
    } else {
        //if so, make it visible
        card.querySelector('.image_view').querySelector('img').src=db_img;
        currently_displayed.push(card);
    }
    hideForm();
}


fetch("http://localhost:81/mongo_request.php?request_id=301&merchant="+username+"&token="+access_token)
    .then(res => res.json())
    .then(data =>{
        products = data.map(product => {
            const card = productCard.content.cloneNode(true).children[0];
            card.id = product.PRODUCT_ID;

            const name = card.querySelector("[data-product-name]");
            name.textContent = product.NAME;

            const product_category = card.querySelector("[data-product-category]");
            product_category.textContent = product.CATEGORY;

            const code = card.querySelector("[data-product-code]");
            code.textContent = product.PRODUCTCODE;

            const merchant = card.querySelector("[data-product-merchant]");
            merchant.textContent = product.SELLERNAME;

            const price = card.querySelector("[data-product-price]");
            price.textContent = product.PRICE;

            if (product.WITHDRAWAL_DATE) {
                card.querySelector("[data-product-withdrawal]").textContent = "Sold out: \n" + product.WITHDRAWAL_DATE;
                const cart_image = card.querySelector(".add_to_cart_image");
                cart_image.style.display = 'none';
                price.style.display = 'none';
                card.querySelector('#euro').style.display='none';
                card.querySelector('.purchase_field').style.background = 'gray';
            }

            add_listeners(card, name, product_category, price);
            productGrid.append(card);

            if(view.offsetWidth<card.offsetLeft+card.offsetWidth){
                card.style.display='none';
            } else {
                const img = card.querySelector(".image_view").querySelector('img');
                img.src = product.IMG_NAME;
                currently_displayed.push(card);
            }
            return {
                IMG_NAME: product.IMG_NAME,
                CATEGORY: product.CATEGORY,
                NAME: product.NAME,
                PRODUCTCODE: product.PRODUCTCODE,
                SELLERNAME: product.SELLERNAME,
                PRICE: product.PRICE,
                WITHDRAWAL_DATE: product.WITHDRAWAL_DATE,
                element: card
            }})
        if(currently_displayed.length===products.length)
            forwards.style.display='none';
    })


async function add_listeners(card, name, product_category, price) {
    const info=card.querySelector('.info');
    const save=card.querySelector('.save_product');
    const edit=card.querySelector('.edit_product');
    const dlt=card.querySelector('.delete_product');
    const edit_form = card.querySelector('.product_edit_form');
    save.id = card.id;
    edit.id = card.id;
    dlt.id = card.id;
    edit_form.id= card.id;

    save.addEventListener("click", async e=> {
        const input_name = edit_form.querySelector('#edit_name');
        const input_category = edit_form.querySelector('#edit_category');
        const input_price = edit_form.querySelector('#edit_price');

        let post_data = new FormData();
        post_data.append("token", access_token);
        post_data.append('request_id', '220');
        post_data.append('product_id', e.target.id);
        post_data.append('product_name', input_name.value);
        post_data.append('product_category', input_category.value);
        post_data.append('product_price', input_price.value);

        let db_product_name, db_category, db_price;
        await fetch('http://localhost:81/mongo_request.php', {method:'POST', body: post_data})
            .then((response) => response.json())
            .then((answer) => {
                db_product_name=answer[0].name;
                db_category=answer[0].category;
                db_price=answer[0].price
            });

        edit_form.style.display='none';
        info.style.display='block';
        save.style.display='none';
        dlt.style.display='block';

        name.textContent = db_product_name;
        product_category.textContent = db_category;
        price.textContent = db_price;
    })

    edit.addEventListener("click", e=> {
        edit_form.querySelector('#edit_name').value=name.textContent;
        edit_form.querySelector('#edit_category').value=product_category.textContent;
        edit_form.querySelector('#edit_price').value=price.textContent;
        if(edit_form.style.display==='none'){
            dlt.style.display='none';
            save.style.display='block';
            info.style.display='none';
            edit_form.style.display='block';
        } else{
            edit_form.style.display='none';
            info.style.display='block';
            save.style.display='none';
            dlt.style.display='block';
        }
    })

    dlt.addEventListener("click", async e => {
        let post_data = new FormData();
        post_data.append("token", access_token);
        post_data.append('request_id', '200');
        post_data.append('product_id', e.target.id);
        let failed;
        await fetch('http://localhost:81/mongo_request.php', {method: 'POST', body: post_data})
            .then(response => response.json()).then(data => failed=data.res);

        if (!failed) {
            card.style.display = 'none';
        } else {
            console.log('Deletion failed');
        }
    })
}