<?php
$manager = new MongoDB\Driver\Manager('mongodb://root:secret@mongo_db:27017/');
switch($_SERVER['REQUEST_METHOD']) {
    case 'GET': // request product
        $filter = ['user_id' => (string) $_GET['user_id']];
        $find = new MongoDB\Driver\Query($filter);
        $result = $manager->executeQuery("estore.Carts", $find);
        $carts = $result->toArray();
        $data_out = array();
        foreach ($carts as $cart) {
            $data_out[] = array('USERID' => $cart->user_id,
                'PRODUCTID' => (String) $cart->product->id,
                'NAME' => $cart->product->name,
                'PRICE' => $cart->product->price,
                'IMG_NAME' => $cart->product->img,
                'INSERTION_DATE' => $cart->date->toDateTime()->format('Y-m-d h:m:s'));
        }
        echo json_encode($data_out);
        break;
    case 'POST': //insert into Products collection
        $filter = ['product.id' => $_POST['product_id']];
        $find = new MongoDB\Driver\Query($filter);
        $result = $manager->executeQuery("estore.Carts", $find);
        if(empty($result->toArray()[0])) {
            $filter = ['_id' => new MongoDB\BSON\ObjectId($_POST['product_id'])];
            $find = new MongoDB\Driver\Query($filter);
            $result = $manager->executeQuery("estore.Products", $find);
            $product = $result->toArray()[0];
            $filter = ['user_id' => $_POST["user_id"],
                'product' => ['id' => $_POST['product_id'],
                    'name' => $product->name,
                    'price' => $product->price,
                    'img' => $product->img],
                'date' => new \MongoDB\BSON\UTCDateTime()];

            $bulk = new MongoDB\Driver\BulkWrite();
            $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
            $bulk->insert($filter);
            $result = $manager->executeBulkWrite('estore.Carts', $bulk,$writeConcern);
            echo json_encode(array('res' => 1));
            return;
        }
        echo json_encode(array('res' => 0)); //failed
        break;
    case 'DELETE': // delete from Products collection using id
        $input =file_get_contents('php://input');
        parse_str($input, $_DELETE);
        $filter = ['user_id' => $_DELETE['user_id'], 'product.id' => $_DELETE['product_id']];
        $bulk = new MongoDB\Driver\BulkWrite();
        $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
        $bulk->delete($filter);
        $result = $manager->executeBulkWrite('estore.Carts', $bulk, $writeConcern);
        $failed = $result->getDeletedCount()==1?0:1;
        echo json_encode(array("res" => $failed));
        break;
}