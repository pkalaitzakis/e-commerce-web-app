<?php
$manager = new MongoDB\Driver\Manager('mongodb://root:secret@mongo_db:27017/');
switch($_SERVER['REQUEST_METHOD']) {
    case 'GET': // request product
        if(isset($_GET['limit']) &&  isset($_GET['offset'])){
            $options = ["limit" => intval($_GET['limit']), "skip" => intval($_GET['offset'])];
        }
        switch($_GET["by"]) {
            case "name":
                $filter = ["name" => new \MongoDB\BSON\Regex($_GET['search'])];
                break;
            case "date":
                $start = new DateTime($_GET['start']);
                $end = new DateTime($_GET['end']);
                $filter = [ "withdrawn" => [
                    '$gte' => new MongoDB\BSON\UTCDateTime($start->getTimestamp()*1000),
                    '$lte' => new MongoDB\BSON\UTCDateTime($end->getTimestamp()*1000)]];
                break;
            case "price":
                $filter = ["price" => ['$gte' => intval($_GET["start"]), '$lte' => intval($_GET["end"])]];
                break;
            case "seller":
                $filter = ["seller.name" => new \MongoDB\BSON\Regex($_GET['search'])];
                break;
            case "category":
                $filter = ["category" => new \MongoDB\BSON\Regex($_GET['search'])];
                break;
            default:
                $filter = [];
                $max=new MongoDB\Driver\Query($filter, ['sort' => ['price' => -1], 'limit' => 1]);
                $max = $manager->executeQuery('estore.Products', $max);
                $max = json_encode($max->toArray()[0]->price);
                $min = new MongoDB\Driver\Query($filter, ['sort' => ['price' => 1], 'limit' => 1]);
                $min = $manager->executeQuery('estore.Products', $min);
                $min = json_encode($min->toArray()[0]->price);
                break;
        }
        if(empty($_GET["count"])) {
            if(isset($options)){
                $find = new MongoDB\Driver\Query($filter, $options);
            } else{
                $find = new MongoDB\Driver\Query($filter);
            }
            $result = $manager->executeQuery("estore.Products", $find);
            $products = $result->toArray();
            $data_out = array();
            foreach ($products as $product) {
                $data_out[]=array('PRODUCT_ID' => (string)$product->_id,
                    'NAME' => $product->name,
                    'PRODUCTCODE' => $product->code,
                    'SELLERNAME' => $product->seller->name,
                    'PRICE' => $product->price,
                    'CATEGORY' => $product->category,
                    'WITHDRAWAL_DATE' => $product->withdrawn==='NULL'?null:$product->withdrawn->toDateTime()->format('Y-m-d h:m:s'),
                    'IMG_NAME' => $product->img);
            }
            echo json_encode($data_out);
        } else {
            $find = new MongoDB\Driver\Query($filter);
            $result = $manager->executeQuery("estore.Products", $find);
            $result=$result->toArray();
            $result = count($result);
            if (isset($max)&&isset($min))
                echo json_encode(array("count" =>$result, "min" =>$min, "max" => $max));
            else
                echo json_encode(array("count" =>$result));
        }
        break;
    case 'POST': //insert into Products collection
        $filter = ['name' => $_POST["name"],
                   'seller' => ['id' => $_POST['id'],
                   'name' => $_POST['seller']],
                   'category' => $_POST['category'],
                   'code' => $_POST['code'],
                   'price' => floatval($_POST['price']),
                   'withdrawn' => 'NULL',
                   'img' => $_POST['src']];

        $bulk = new MongoDB\Driver\BulkWrite();
        $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
        $bulk->insert($filter);
        $result = $manager->executeBulkWrite('estore.Products', $bulk,$writeConcern);
        if($result->getInsertedCount()) {
            $find = new MongoDB\Driver\Query($filter);
            $result = $manager->executeQuery("estore.Products", $find);
            $result = $result->toArray()[0];
            echo json_encode(array('product_id' => (string)$result->_id,
                'product_name' => $result->name,
                'product_code' => $result->code,
                'product_seller' => $result->seller->name,
                'product_price' => $result->price,
                'product_category' => $result->category,
                'product_img' => $result->img));
        }
        break;
    case 'DELETE': // delete from Products collection using id
        $filter = ['_id' => new MongoDB\BSON\ObjectId($_REQUEST['product_id'])];
        $bulk = new MongoDB\Driver\BulkWrite();
        $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
        $bulk->delete($filter);
        $result = $manager->executeBulkWrite('estore.Products', $bulk, $writeConcern);
        $failed = $result->getDeletedCount()==1?0:1;
        echo json_encode(array("res" => $failed));
        break;
    case 'PATCH': // update document from Product collection using id
        $filter = ['_id' => new MongoDB\BSON\ObjectId($_GET['id'])];
        # parse request's body
        $input =file_get_contents('php://input');
        parse_str($input, $_PATCH);
        $find = new MongoDB\Driver\Query($filter);
        $result = $manager->executeQuery("estore.Products", $find);
        $result = $result->toArray()[0];
        $bulk = new MongoDB\Driver\BulkWrite();

        if(!empty($_PATCH["product_name"]) && $result->name != $_PATCH["product_name"]) {
            $update = ['$set' => ['name' => $_PATCH['product_name']] ];
            $bulk->update($filter, $update);
        }

        if(!empty($_PATCH["product_category"]) && $result->category != $_PATCH["product_category"]) {
            $update = ['$set' => ['category' => $_PATCH['product_category']] ];
            $bulk->update($filter, $update);
        }

        if(!empty($_PATCH["product_price"]) && $result->price != $_PATCH["product_price"]) {
            $update = ['$set' => ['price' => $_PATCH['product_price']] ];
            $bulk->update($filter, $update);
        }

        if ($bulk->count()){
            $result = $manager->executeBulkWrite('estore.Products', $bulk);
            if ($result->getModifiedCount()>0) {
                $result = $manager->executeQuery("estore.Products", $find);
                $result = $result->toArray();
                echo json_encode($result);
            }
        }
        break;
}