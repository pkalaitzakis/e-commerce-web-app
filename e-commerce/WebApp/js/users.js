const userCardTemplate = document.querySelector("[data-user-template]");
const userCardContainer = document.querySelector("[data-user-cards-container]");
const searchInput = document.querySelector("[data-search]");
let username;

let users = [];
let user_offset = 0;
let page_size = 25;

// on domcontentloaded event, initialize pagination div (no search parameters)
document.addEventListener('DOMContentLoaded', (event) =>{initialize_pages('')}, false);

// everytime input changes
searchInput.addEventListener("change", e => {
    initialize_pages(e.target.value);
})

async function initialize_pages (param) {
    let entries;
    // ask db how many records match the request
    await fetch('http://localhost:81/keyrock_idm.php?count=1&search='+encodeURI(param))
        .then(data => data.json()).then(rows=> {entries=rows});

    if (!entries)
        return;
    // initialize pagination div by remaking its children according to number of entries reported back
    Pagination.Init(document.getElementById('pagination'), {
        size: Math.ceil(entries/page_size), // number of pages to be generated
        page: 1,  // selected page
        step: 3   // pages before and after current
    });

    // clean previously loaded content
    if(userCardContainer.children.length>0)
        empty_container();

    // fetch first batch of users (page one results)
    fetch_batch_users(page_size, 0, param);


    const page_list = document.querySelector('#pagination').children[1];
    const observer = new MutationObserver((mutationList) => {
        const current_page =  mutationList[0].target.innerHTML;
        user_offset = ((current_page-1) * page_size);
        if(userCardContainer.children.length>0)
            empty_container();
        fetch_batch_users(page_size, user_offset, param);
    });
    observer.observe(page_list, {subtree:true, attributes: true});
}

function empty_container(){
    const iterations = userCardContainer.children.length;
    for(let i=0; i<iterations; i++){
        userCardContainer.children[0].remove();
    }
}

function fetch_batch_users(limit, offset, search_param){
    fetch("http://localhost:81/keyrock_idm.php?limit="+encodeURI(limit.toString())
        +"&offset="+encodeURI(offset.toString())
        +"&search="+encodeURI(search_param))
        .then(res => res.json())
        .then(data =>{
            users = data.map(user => {
                const card = userCardTemplate.content.cloneNode(true).children[0];

                const userid = card.querySelector("[data-id]");
                userid.textContent = user.USER_ID;
                card.id = user.USER_ID;

                const fname = card.querySelector("[data-fname]");
                fname.textContent = user.FNAME;

                const lname = card.querySelector("[data-lname]");
                lname.textContent = user.LNAME;

                const username = card.querySelector("[data-username]");
                username.textContent = user.USERNAME;

                const email = card.querySelector("[data-email]");
                email.textContent = user.EMAIL;

                const role = card.querySelector("[data-role]");
                role.textContent = user.ROLE_T;

                const confirmed = card.querySelector("[data-confirmed]");

                if (user.CONFIRMED === "1"){
                    confirmed.textContent = "Confirmed";
                    card.children[1].querySelector(".confirmed_edit").children[0].checked='checked';
                } else{
                    confirmed.textContent = "Confirmation Pending";
                    card.children[1].querySelector(".confirmed_edit").children[2].checked='checked';
                }

                card.children[1].id = "form_"+user.USER_ID;
                card.querySelector('.button_type').children[2].id = "save_"+user.USER_ID;
                card.querySelector('.button_type').children[1].id = "edit_"+user.USER_ID;
                card.querySelector('.button_type').children[0].id = "delete_"+user.USER_ID;

                userCardContainer.append(card);
                return {FNAME: user.FNAME,
                    EMAIL: user.EMAIL,
                    LNAME: user.LNAME,
                    USERNAME: user.USERNAME,
                    EMAIL: user.EMAIL,
                    CONFIRMED: user.CONFIRMED,
                    ROLE_T: user.ROLE_T,
                    element: card}
            })

        })
}

async function deleteForm(){
    //fetch card by modifying event target id tag
    const userid = this.event.target.id.replace('delete_', '')
    const card = document.getElementById(userid);

    let post_data = new FormData();
    post_data.append('action', 'delete_user');
    post_data.append('user_id', userid);

    let failed;
    await fetch('http://localhost:81/keyrock_idm.php', {method: 'POST', body:post_data})
        .then((response) => response.json())
        .then((answer) => failed=answer.res);

    if(!failed) {
        card.style.display='none';
    } else {
        console.log('Deletion failed');
    }
}

function editForm() {
    //fetch card by modifying event target id tag
    const card = document.getElementById(this.event.target.id.replace('edit_', ''));

    // fetch full name field html element
    const fullname_field = card.querySelector('.fullname');

    // fetch username field html element
    const username_field = card.querySelector('.username');

    // fetch current_role html element
    const role_field = card.querySelector('.role');

    // fetch current_role html element
    const confirmed_field = card.querySelector('.confirmed');

    // fetch edit_button html element
    const edit_button = document.getElementById(this.event.target.id);

    // fetch save button html element
    const save_button = document.getElementById(this.event.target.id.replace('edit_', 'save_'));

    // fetch invisible form html element
    const form = document.getElementById(this.event.target.id.replace('edit_', 'form_'));

    form.querySelector('.fullname_edit').querySelector('.fname_edit').querySelector('input').value=fullname_field.querySelector('.fname').textContent;
    form.querySelector('.fullname_edit').querySelector('.lname_edit').querySelector('input').value=fullname_field.querySelector('.lname').textContent;
    form.querySelector('.username_edit').querySelector('input').value=username_field.textContent;

    // initialize form's role option to current role
    if (role_field.textContent === 'Admin')
        form.querySelector('#admin').selected='selected';
    else if (role_field.textContent === 'Merchant')
        form.querySelector('#merch').selected='selected';
    else // user case
        form.querySelector('#plain').selected='selected';

    // handle form and edit/save buttons visibility
    if(form.style.display === 'none') {
        edit_button.innerHTML = 'Cancel';
        save_button.style.display = 'inline';
        fullname_field.style.display = 'none';
        username_field.style.display = 'none';
        role_field.style.display = 'none';
        confirmed_field.style.display = 'none';
        form.style.display = 'block';
    } else if (form.style.display === 'block'){
        edit_button.innerHTML = 'Edit';
        save_button.style.display= 'none';
        form.style.display = 'none';
        fullname_field.style.display = 'block';
        username_field.style.display = 'block';
        role_field.style.display = 'block';
        confirmed_field.style.display = 'block';
    }
}

async function save_form(){
    const id = this.event.target.id.replace('save_', '');
    const card = document.getElementById(id);

    // fetch form html element
    const form = document.getElementById(this.event.target.id.replace('save_', 'form_'));

    // fetch save and edit buttons
    const save_button = document.getElementById(this.event.target.id);
    const edit_button = document.getElementById(this.event.target.id.replace('save_', 'edit_'));

    // get form's fields input values
    const input_fname = card.querySelector('.fname_edit').querySelector('input').value;
    const input_lname = card.querySelector('.lname_edit').querySelector('input').value;
    const input_username = card.querySelector('.username_edit').querySelector('input').value;
    const input_role = card.querySelector('.role_edit').querySelector('select').value;
    const input_confirmed_field = card.querySelector('.confirmed_edit');

    let input_confirmed;
    if(input_confirmed_field.children[0].checked)
        input_confirmed=1;
    else
        input_confirmed=0;


    // fetch fullname field html element
    const fullname_field = card.querySelector('.fullname');
    // fetch username field html element
    const username_field = card.querySelector('.username');
    // fetch current_role html element
    const role_field = card.querySelector('.role');
    // fetch current_role html element
    const confirmed_field = card.querySelector('.confirmed');

    // handle css styling of save buttom, edit button, form, and updated info fields

    save_button.style.display='none';
    edit_button.textContent='Edit';
    form.style.display='none';
    fullname_field.style.display='block';
    username_field.style.display='block';
    role_field.style.display='block';
    confirmed_field.style.display='block';
    // group provided input

    let post_data = new FormData();
    post_data.append('action', 'update_user');
    post_data.append('user_id', id);
    post_data.append('input_fname', input_fname);
    post_data.append('input_lname', input_lname);
    post_data.append('input_username', input_username);
    post_data.append('input_role', input_role);
    post_data.append('input_confirmed', input_confirmed);
    // post input and fetch db response (new selection query is executed
    // after update, to ensure fields were successfully update on sever side)
    let db_fname, db_lname, db_username, db_role, db_status;
    await fetch('http://localhost:81/keyrock_idm.php', {method: 'POST', body: post_data})
        .then((response) => response.json())
        .then((answer) => {db_fname=answer.fname;
            db_lname=answer.lname;
            db_username=answer.usrname;
            db_role=answer.role;
            db_status=answer.confirmed
        });

    // update card fields according to db selection reply (after update)
    fullname_field.querySelector('.fname').textContent=db_fname;
    fullname_field.querySelector('.lname').textContent=db_lname;
    username_field.textContent=db_username;
    role_field.textContent=db_role;

    console.log(db_lname,db_username,db_role, db_status);
    if (db_status==="1")
        confirmed_field.textContent='Confirmed';
    else
        confirmed_field.textContent='Confirmation Pending';
}