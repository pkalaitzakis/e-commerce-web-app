<?php
#Definitions
$client_id = "b906a991-8854-4281-99c2-6a7108ba25a8";
$client_secret = "a67618a8-ebca-4b09-967d-288aab9fefa1";

if (array_key_exists('HTTP_ORIGIN', $_SERVER))
    if($_SERVER['HTTP_ORIGIN'] == "http://172.18.1.9" || $_SERVER['HTTP_ORIGIN'] == "http://localhost")
        header("Access-Control-Allow-Origin: ".$_SERVER['HTTP_ORIGIN']);

/* Function that utilizes curl library to make a request to keyrock idm
   and retrieve an administration token (x-auth-token), using the default
   provider's credentials (keyrock default admin)*/
function x_Auth_token() {
    // request x-auth-token using the providers credentials
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, 'http://172.18.1.5:3005/v1/auth/tokens');
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS,
        json_encode(array(
                "name" => "pkalaitzakis@test.com",
                "password" => "1234")
        )
    );
    curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($curl);
    $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    curl_close($curl);
    $header = substr($response, 0, $header_size);
    $data=explode("\n", $header);
    $data=explode(" ", $data[2]);
    return trim($data[1]);
}

/* Assign a specific role ($role) to a user ($user_id),
   in our keyrock registered application ($app_id), after of course
   requesting for an x-token, by utilizing the endpoint 'Assign a Role to User'
   in Keyrock's REST api */
function authorize($app_id, $role, $user_id) {
    $x_token = x_Auth_token();
    if ($role == "Admin") {
        $role_id = "2a412ab8-bd73-4251-9bde-d9894a19f5b8";
    } elseif ($role == "Merchant") {
        $role_id = "51c752d8-e3a9-49af-bddc-60d8742fa19c";
    } else{
        $role_id = "db202f2c-a4f0-422b-a72f-c8b152dd6878";
    }

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', "X-Auth-token: ".$x_token));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_URL,
        'http://172.18.1.5:3005/v1/applications/'.$app_id.'/users/'.$user_id.'/roles/'.$role_id);
    curl_exec($curl);
}

/* Likewise this function deletes an assigned role, by sending a 'DELETE' request rather than a 'POST'
   to the correct endpoint in keyrock's REST api*/
function deauthorize($app_id, $role, $user_id) {
    $x_token = x_Auth_token();
    if ($role == "Admin") {
        $role_id = "2a412ab8-bd73-4251-9bde-d9894a19f5b8";
    } elseif ($role == "Merchant") {
        $role_id = "51c752d8-e3a9-49af-bddc-60d8742fa19c";
    } else{
        $role_id = "db202f2c-a4f0-422b-a72f-c8b152dd6878";
    }
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', "X-Auth-token: ".$x_token));
    curl_setopt($curl, CURLOPT_URL,
        'http://172.18.1.5:3005/v1/applications/'.$app_id.'/users/'.$user_id.'/roles/'.$role_id);
    curl_exec($curl);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch($_POST['action']) {
        /***************************************** REQUEST USER LOGIN *****************************************/
        case "login":
            // initialize a new curl session
            $curl = curl_init();
            $base64_auth = base64_encode($client_id.":".$client_secret);
            // set request's target url to keyrock's oauth2/token endpoint
            curl_setopt($curl, CURLOPT_URL, "http://172.18.1.5:3005/oauth2/token");
            // needed info to be included in header (see keyrock api documentation for further details)
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic '.$base64_auth,
                    'Content-Type: application/x-www-form-urlencoded'
                )
            );
            // prepare a POST request
            curl_setopt($curl, CURLOPT_POST, true);
            // request's body
            curl_setopt($curl, CURLOPT_POSTFIELDS,
                http_build_query([
                    'grant_type' => "password",
                    'username' => $_POST['email'],
                    'password' => $_POST['password'],
                    'scope' => "permanent"
                ])
            );
            // accept and receive response
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            // execute request and receive response from keyrock api
            $response = curl_exec($curl);
            // decode response (json format)
            $response = json_decode($response);
            // terminate curl session
            curl_close($curl);
            // retrieve tokens that are stored in the decoded json
            $access_token = $response->access_token;
            // prepare a secondary POST request to a different keyrock api
            // endpoint, in order to retrieve information pertaining to the
            // user that just logged in. that user also needs to include the
            // valid oauth2 token that he received from the previous request
            $curl = curl_init();
            $url = "http://172.18.1.5:3005/user"."?".http_build_query(['access_token' => $access_token]);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPGET, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curl);
            $response = json_decode($response);
            curl_close($curl);
            $role= isset($response->roles[0]) ? $response->roles[0]->name : null;
            // expose certain user info back to WebApp $_SESSION global variable
            echo json_encode(array(
                'user_id' => $response->id,
                'username' => $response->username,
                'email' => $response->email,
                'role' => $role,
                'access_token' => $access_token
            ));
            break;
        /***************************************** REQUEST USER REGISTRATION *****************************************/
        case "register":
            $x_token = x_Auth_token();
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, 'http://172.18.1.5:3005/v1/users');
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'X-Auth-token: '.$x_token));
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS,
                json_encode(array(
                        "user" => array(
                            "username" => $_POST["username"],
                            "email" => $_POST["email"],
                            "password" => $_POST["password"],
                            "description" => $_POST["name"].", ".$_POST["surname"].", ".$_POST["role"].", 0")
                        )
                )
            );
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curl);
            $response = json_decode($response);
            curl_close($curl);
            header('Location: http://172.18.1.9/index.php');
            break;
        case "delete_user":
            $x_token = x_Auth_token();
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, 'http://172.18.1.5:3005/v1/users/'.$_POST['user_id']);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-Auth-token: '.$x_token));
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, true);
            $response = curl_exec($curl);
            $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            curl_close($curl);
            $header = substr($response, 0, $header_size);
            $data=explode("\n", $header);
            $data=explode(" ", $data[0]);
            $data=trim($data[1]);

            if($data==='204') {
                echo json_encode(["res" => 0]);
            } else {
                echo json_encode(["res" => 1]);
            }
            break;
        case "update_user":
            $x_token = x_Auth_token();
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, 'http://172.18.1.5:3005/v1/users/'.$_POST['user_id']);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-Auth-token: '.$x_token));
            curl_setopt($curl, CURLOPT_HTTPGET, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curl);
            $response = json_decode($response);

            if(isset($response->user)) {
                $current_id = $response->user->id;
                $current_username = $response->user->username;
                $fields = explode(", ", $response->user->description);
                $current_fname = $fields[0];
                $current_lname = $fields[1];
                $current_role = $fields[2];
                $current_status = $fields[3];

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, 'http://172.18.1.5:3005/v1/users/'.$_POST['user_id']);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'X-Auth-token:'.$x_token));
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                if(!empty($_POST["input_username"]) && $current_username != $_POST["input_username"]) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array(
                        "user" => array("username" => $_POST["input_username"])
                    )));
                    $response = curl_exec($curl);
                    $response = json_decode($response);

                    if(isset($response->values_updated->username)) {
                        $current_username = $response->values_updated->username;
                    }
                }

                if(!empty($_POST["input_fname"]) && $current_fname != $_POST["input_fname"]) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array(
                        "user" => array("description" => $_POST["input_fname"].", ".$current_lname.", ".$current_role.", ".$current_status)
                    )));
                    $response = curl_exec($curl);
                    $response = json_decode($response);
                    if(isset($response->values_updated->description)) {
                        $fields = explode(", ", $response->values_updated->description);
                        $current_fname = $fields[0];
                    }
                }

                if(!empty($_POST["input_lname"]) && $current_lname != $_POST["input_lname"]) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array(
                        "user" => array("description" => $current_fname.", ".$_POST["input_lname"].", ".$current_role.", ".$current_status)
                    )));
                    $response = curl_exec($curl);
                    $response = json_decode($response);
                    if(isset($response->values_updated->description)) {
                        $fields = explode(", ", $response->values_updated->description);
                        $current_lname = $fields[1];
                    }

                }

                if(isset($_POST["input_confirmed"]) && $_POST["input_confirmed"] !== $current_status) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array(
                        "user" => array("description" => $current_fname.", ".$current_lname.", ".$current_role.", ".$_POST["input_confirmed"])
                    )));
                    $response = curl_exec($curl);
                    $response = json_decode($response);
                    if(isset($response->values_updated->description)) {
                        $fields = explode(", ", $response->values_updated->description);
                        $current_status = $fields[3];
                        if($current_status==1){
                            authorize($client_id, $current_role, $_POST['user_id']);
                        } else {
                            deauthorize($client_id, $current_role, $_POST['user_id']);
                        }
                    }
                }

                if(!empty($_POST["input_role"]) && $current_role !== $_POST["input_role"]) {
                    if ($current_status==1){
                        deauthorize($client_id, $current_role, $_POST['user_id']);
                    }

                    // update user's description field
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array(
                        "user" => array("description" => $current_fname.", ".$current_lname.", ".$_POST["input_role"].", ".$current_status)
                    )));
                    // execute request
                    $response = curl_exec($curl);
                    $response = json_decode($response);
                    // if response was completed successfully,
                    if(isset($response->values_updated->description)) {
                        $fields = explode(", ", $response->values_updated->description);
                        $current_role = $fields[2];
                        if ($current_status==1){
                            authorize($client_id, $current_role, $_POST['user_id']);
                        }
                    }
                }

                echo json_encode(array(
                    'usrname' => $current_username,
                    'fname' => $current_fname,
                    'lname' => $current_lname,
                    'role' => $current_role,
                    'confirmed' => $current_status
                ));
            }
            break;
    }
}

if($_SERVER['REQUEST_METHOD']==='GET'){
    $mysqli = require __DIR__ . "/database.php";

    if(empty($_GET["search"])){
        if(empty($_GET["count"])) {
            $stmt = $mysqli->prepare("SELECT id, username, email, description FROM user LIMIT ? offset ?");
            $stmt->bind_param("dd", $_GET["limit"], $_GET["offset"]);
        } else{
            $result = $mysqli->query("SELECT COUNT(*) FROM user WHERE 1");
            echo $result->fetch_assoc()["COUNT(*)"];
            return;
        }
    } else{
        $search_value = $_GET["search"];
        $param = "%$search_value%";
        if(empty($_GET["count"])) {
            $stmt = $mysqli->prepare("SELECT id, username, email, description FROM user WHERE 
                                                      username LIKE ? || description LIKE ? ||email LIKE ? 
                                                      LIMIT ? offset ?");
            $stmt->bind_param("sssdd", $param, $param, $param, $_GET["limit"], $_GET["offset"]);
        } else {
            $stmt = $mysqli->prepare("SELECT COUNT(*) FROM user WHERE 
                                                      username LIKE ? || description LIKE ? ||email LIKE ?");
            $stmt->bind_param("sss", $param, $param, $param);
            $stmt->execute();
            $result=$stmt->get_result()->fetch_assoc()["COUNT(*)"];
            echo $result;
            return;
        }
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $first = true;
    $index = 0;
    header("Content-Type: application/json");

    while ($user_obj = $result->fetch_assoc()) {
        if ($first) {
            echo "[";
            $first = false;
        }
        $user_details = explode(", ", $user_obj['description']);
        echo json_encode(array(
            "USER_ID" => $user_obj['id'],
            "USERNAME" => $user_obj['username'],
            "EMAIL" => $user_obj['email'],
            "FNAME" => $user_details[0],
            "LNAME" => $user_details[1],
            "ROLE_T" => $user_details[2],
            "CONFIRMED" => $user_details[3]
        ));
        $index += 1;
        if ($index === $result->num_rows) {
            echo "]";
        } else
            echo ",";
    }
}