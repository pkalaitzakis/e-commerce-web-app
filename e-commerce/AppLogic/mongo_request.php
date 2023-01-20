<?php
if (array_key_exists('HTTP_ORIGIN', $_SERVER))
    if($_SERVER['HTTP_ORIGIN'] == "http://172.18.1.9" || $_SERVER['HTTP_ORIGIN'] == "http://localhost")
        header("Access-Control-Allow-Origin: ".$_SERVER['HTTP_ORIGIN']);

/* SEARCH PRODUCTS */
if (isset($_GET['request_id']) && $_GET['request_id'] === '302') {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_HTTPHEADER,array('X-Auth-Token: '.$_GET['token']));
    curl_setopt($curl, CURLOPT_HTTPGET, true);
    curl_setopt($curl, CURLOPT_URL,
        'http://data_storage_wilma:1028/products.php?'.http_build_query(array(
            'count' => isset($_GET['count'])?$_GET['count']:"",
            'by' => isset($_GET['by'])?$_GET['by']:"",
            'search' => isset($_GET['search'])?$_GET['search']:"",
            'start' => isset($_GET['start'])?$_GET['start']:"",
            'end' => isset($_GET['end'])?$_GET['end']:"",
            'limit' => isset($_GET['limit'])?$_GET['limit']:"",
            'offset' => isset($_GET['offset'])?$_GET['offset']:""
        )));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    echo $response;
    curl_close($curl);
}

/* ADD TO CART */
if(isset($_POST['request_id']) && $_POST['request_id'] === '250') {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded', 'X-Auth-Token: '.$_POST['token']));
    curl_setopt($curl, CURLOPT_URL, 'http://data_storage_wilma:1028/carts.php?');
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(['user_id' => $_POST["user_id"], 'product_id' => $_POST["product_id"]]));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    echo $response;
    curl_close($curl);
}

/* REMOVE FROM CART */
if(isset($_POST['request_id']) && $_POST['request_id'] === '260') {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded', 'X-Auth-Token: '.$_POST['token']));
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(array(
        'user_id' => $_POST['user_id'],
        'product_id' => $_POST['product_id']
    )));
    curl_setopt($curl, CURLOPT_URL, 'http://data_storage_wilma:1028/carts.php?');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    echo $response;
    curl_close($curl);
}

/* FETCH ALL PRODUCTS UNDER SELLER */
if(isset($_GET['request_id']) && $_GET['request_id'] === '303'){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded','X-Auth-Token: '.$_GET['token']));
    curl_setopt($curl, CURLOPT_HTTPGET, true);
    curl_setopt($curl, CURLOPT_URL,
        'http://data_storage_wilma:1028/carts.php?'.http_build_query(array(
            'user_id' => $_GET['user_id']
        )));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    echo $response;
    curl_close($curl);
}

/* FETCH ALL PRODUCTS UNDER SELLER */
if(isset($_GET['request_id']) && $_GET['request_id'] === '301'){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_HTTPGET, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded', 'X-Auth-Token: '.$_GET['token']));
    curl_setopt($curl, CURLOPT_URL,
        'http://data_storage_wilma:1028/products.php?'.http_build_query(array(
            'by' => "seller",
            'search' => $_GET['merchant']
        )));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    echo $response;
    curl_close($curl);
}

/* CREATE NEW PRODUCT */
if(isset($_POST['request_id']) && $_POST['request_id'] === '210'){
    if (isset($_FILES['image'])) {
        $file_name = $_FILES['image']['name'];
        $file_size = $_FILES['image']['size'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $extensions = array("jpeg", "jpg", "png");
        $file_path = "assets/products/" . $file_name;
        move_uploaded_file($file_tmp, $file_path);
    } else {
        $file_path = "assets/placeholder.png";
    }
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded', 'X-Auth-Token: '.$_POST['token']));
    curl_setopt($curl, CURLOPT_URL, 'http://data_storage_wilma:1028/products.php?');
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(array(
        'name' => $_POST['name'],
        'category' => $_POST['category'],
        'id' => $_POST['id'],
        'seller'  => $_POST['seller'],
        'code' => $_POST['code'],
        'price' => $_POST['price'],
        'src' => $file_path
    )));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    echo $response;
    curl_close($curl);
}

/* UPDATE EXISTING PRODUCT */
if(isset($_POST['request_id']) && $_POST['request_id'] === '220'){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded', 'X-Auth-Token: '.$_POST['token']));
    curl_setopt($curl, CURLOPT_URL, 'http://data_storage_wilma:1028/products.php?id='.$_POST['product_id']);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(array(
        'product_name' => $_POST['product_name'],
        'product_category' => $_POST['product_category'],
        'product_price' => $_POST['product_price'])));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    echo $response;
    curl_close($curl);
}

/* DELETE EXISTING PRODUCT */
if(isset($_POST['request_id']) && $_POST['request_id'] === '200'){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($curl, CURLOPT_HTTPHEADER,array('X-Auth-Token: '.$_POST['token']));
    curl_setopt($curl, CURLOPT_URL, 'http://data_storage_wilma:1028/products.php?product_id='.$_POST['product_id']);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    echo $response;
    curl_close($curl);
}

