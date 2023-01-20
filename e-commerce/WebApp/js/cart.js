const cart_table = document.querySelector("#cart_items");
let e=false;
let empty_cart=cart_table.querySelector("#empty_cart");
let sum;
const total= cart_table.querySelector("#sum");
let row, img_cell, name_cell, date_cell, price_cell;

fetch('http://localhost:81/mongo_request.php?request_id=303&user_id='+user_id+'&token='+access_token)
    .then((response) => (response.json()))
    .then(data =>{
        data.forEach(item => {
            empty_cart.remove();
            row = cart_table.querySelector('tbody').insertRow(0);
            row.id = item.PRODUCTID;
            img_cell = row.insertCell(-1);
            let img_element = document.createElement("img");
            img_element.src = item.IMG_NAME;
            img_element.style.width='100px';
            img_cell.appendChild(img_element);
            name_cell = row.insertCell(-1);
            name_cell.innerHTML = item.NAME;
            name_cell.style.maxWidth = '300px';
            date_cell = row.insertCell(-1);
            date_cell.innerHTML = item.INSERTION_DATE.substring(0, 16);
            price_cell = row.insertCell(-1);
            price_cell.id='price';
            price_cell.innerHTML = item.PRICE;
            sum=parseFloat(total.innerHTML);
            sum+=item.PRICE;
            console.log(sum);
            total.innerHTML=sum.toFixed(2);
            img_cell = row.insertCell(-1);
            img_element = document.createElement("img");
            img_element.id = 'delete_row_'+row.id;
            img_element.src = '/assets/delete.png';
            img_element.style.width='20px';
            img_element.style.cursor='pointer';
            img_cell.appendChild(img_element);
            img_element.addEventListener('click', async e=>{
                    let post_data = new FormData();
                    post_data.append('token', access_token);
                    post_data.append('request_id', '260');
                    post_data.append('user_id', item.USERID);
                    post_data.append('product_id', item.PRODUCTID);
                    await fetch('http://localhost:81/mongo_request.php', {method:'POST', body: post_data})
                        .then((response) => (response.json()))
                        .then((data) => {if(data.res===0) {
                            const sel = e.target.id.replace('delete_row_', '#');
                            sum=parseFloat(total.innerHTML);
                            const price = parseFloat($(sel)[0].querySelector('#price').innerHTML);
                            sum-=price;
                            total.innerHTML=sum.toFixed(2);
                            $(sel).remove();
                            if(cart_table.querySelector('tbody').children.length===1){
                                cart_table.querySelector('tbody').insertBefore(empty_cart, cart_table.querySelector('#total_sum'));
                            }
                        }});
            })
        })
    });

