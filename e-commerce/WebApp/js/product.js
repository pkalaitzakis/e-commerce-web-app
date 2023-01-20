const productCard = document.querySelector("[data-product-card-template]");
const productGrid = document.querySelector("[data-product-grid]");
let products=[];
const search_by = document.querySelector('#search-by');
const search_button = document.querySelector("#do_search");
const search_tag = document.querySelector('[data-search]');
const date_start = document.querySelector('#start_date');
const date_end = document.querySelector('#end_date');
let max_price,min_price;
let value_tag='';
let value_start='', value_end = '';
let by='name';

let users = [];
let user_offset = 0;
let page_size = 25;

// on domcontentloaded event, initialize pagination div (no search parameters)
document.addEventListener('DOMContentLoaded', (event) =>{initialize_pages()}, false);


search_tag.addEventListener("input", e=>{
    value_tag=e.target.value;
})

search_by.addEventListener("change", e=>{
    if(e.target.value.toString()==='date'){
        $('#search-tag').hide();
        $('.range-date').show();
        $('.range-price').hide();
    } else if (e.target.value.toString()==='price'){
        $('#search-tag').hide();
        $('.range-date').hide();
        $('.range-price').show();
        value_start=min_price;
        value_end=max_price;
    } else{
        $('#search-tag').show();
        $('.range-date').hide();
        $('.range-price').hide();
    }
    by=e.target.value.toString();
})

date_start.addEventListener("change", e=>{
    value_start=e.target.value.toString();
    date_end.min=value_start;
})

date_end.addEventListener("change", e=>{
    value_end=e.target.value.toString();
    date_start.max=value_end;
})

// everytime input changes
search_button.addEventListener("click", e => {
    initialize_pages();
})

$(async function() {
    await fetch('http://localhost:81/mongo_request.php?request_id=302&by=&count=1&token='+access_token)
        .then(data => data.json()).then(rows=> {
            min_price=Math.floor(rows.min);
            max_price=Math.ceil(rows.max);
        });
    $("#price-range").slider({
        step: 1,
        range: true,
        min: min_price,
        max: max_price,
        values: [min_price, max_price],
        slide: function(event, ui)
        {$("#priceRange").val(ui.values[0] + " - " + ui.values[1] + " €");
            value_start=ui.values[0];
            value_end=ui.values[1];
        }
    });
    $("#priceRange").val($("#price-range").slider("values", 0) + " - " + $("#price-range").slider("values", 1)+ " €");
});

async function initialize_pages () {
    let entries;
    // ask db how many records match the request
    const url = 'http://localhost:81/mongo_request.php?request_id=302&token='+access_token+'&by='+by+'&count=1&start='+value_start+'&end='+value_end+'&search='+value_tag
    await fetch(url)
        .then(data => data.json()).then(rows=> {
            entries=rows.count
        });

    // clean previously loaded content
    if(productGrid.children.length>0)
        empty_container();

    if (!entries){
        document.querySelector('.product_grid').textContent='No result';
        $('#pagination').hide();
        return;
    } else{
        $('#pagination').show();
        document.querySelector('.product_grid').textContent='';
    }

    // initialize pagination div by remaking its children according to number of entries reported back
    Pagination.Init(document.getElementById('pagination'), {
        size: Math.ceil(entries/page_size), // number of pages to be generated
        page: 1,  // selected page
        step: 3   // pages before and after current
    });

    // fetch first batch of products (page one results)
    fetch_batch_products(page_size, 0);

    const page_list = document.querySelector('#pagination').children[1];
    const observer = new MutationObserver((mutationList) => {
        const current_page =  mutationList[0].target.innerHTML;
        user_offset = ((current_page-1) * page_size);
        if(productGrid.children.length>0)
            empty_container();
        fetch_batch_products(page_size, user_offset);
    });
    observer.observe(page_list, {subtree:true, attributes: true});
}

function empty_container(){
    const iterations = productGrid.children.length;
    for(let i=0; i<iterations; i++){
        productGrid.children[0].remove();
    }
}

function fetch_batch_products(limit, offset){
    const url ="http://localhost:81/mongo_request.php?"+'token='+access_token+
        "&request_id=302&by="+by+'&start='+value_start+'&end='+value_end+"&limit="+limit+"&offset="+offset+"&search="+value_tag;
    fetch(url)
        .then(res => res.json())
        .then(data =>{
            products = data.map(product => {
                const card = productCard.content.cloneNode(true).children[0];
                card.id = product.PRODUCT_ID;

                const product_category = card.querySelector('[data-product-category]');
                product_category.textContent = product.CATEGORY;

                const name = card.querySelector("[data-product-name]");
                name.textContent = product.NAME;

                const code = card.querySelector("[data-product-code]");
                code.textContent = product.PRODUCTCODE;

                const merchant = card.querySelector("[data-product-merchant]");
                merchant.textContent = product.SELLERNAME;

                const price = card.querySelector("[data-product-price]");
                price.textContent = product.PRICE;

                if (product.WITHDRAWAL_DATE) {
                    card.querySelector("[data-product-withdrawal]").textContent = product.WITHDRAWAL_DATE;
                    const cart_image = card.querySelector(".add_to_cart");
                    cart_image.style.display = 'none';
                    price.style.display = 'none';
                    card.querySelector('#withdrawn').style.display = 'block';
                    card.querySelector('#euro').style.display = 'none';
                    card.querySelector('.purchase_field').style.background = 'gray';
                }

                card.querySelector('.add_to_cart').id=card.id;

                card.querySelector('.add_to_cart').addEventListener("click", async e => {
                    let post_data = new FormData();
                    post_data.append("token", access_token);
                    post_data.append("request_id", '250');
                    post_data.append("user_id", user_id);
                    post_data.append("product_id", e.target.id);
                    await fetch('http://localhost:81/mongo_request.php', {method:'POST', body:post_data})
                        .then((response) => (response.json()))
                        .then((data) => {display_msg(data.res, e.target.id)});
                })

                productGrid.append(card);

                const img = card.querySelector(".image_view").querySelector('img');
                img.src = product.IMG_NAME;

                return {
                    IMG_NAME: product.IMG_NAME,
                    CATEGORY: product.CATEGORY,
                    NAME: product.NAME,
                    PRODUCTCODE: product.PRODUCTCODE,
                    SELLERNAME: product.SELLERNAME,
                    PRICE: product.PRICE,
                    WITHDRAWAL_DATE: product.WITHDRAWAL_DATE,
                    element: card
                }
            })
        })
}

function display_msg(msg_type, target_id){
    let msg_element = document.createElement('div');
    msg_element.className = 'cart_display_msg';
    msg_element.id = 'display_msg_'+target_id;
    const view = ($('#'+target_id+'.product_card')[0]).querySelector('.image_view');
    view.appendChild(msg_element);
    if(msg_type===0){
        msg_element.innerHTML='<p>Item already placed in cart!</p>';
    } else {
        msg_element.innerHTML='<p>Item successfully added to cart!</p>';
    }
    $('#'+target_id+'.add_to_cart').hide();
    $('#display_msg_'+target_id).fadeOut(2500, function(){
        $('#'+target_id+'.add_to_cart').show();
        $('#display_msg_'+target_id).remove();
    });
}