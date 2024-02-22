<?php

require "../db_connection.php";

$req = $_POST['request'];

$req = filter_var($req, FILTER_SANITIZE_STRING);

function getColumnValues($columnName, $tableName) {
    global $connessione;
    $sql = "SELECT " . $columnName . " FROM " . $tableName;
    $result = $connessione->query($sql);

    $valori = array();

    if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $valori[] = $row[$columnName];
        }
    }

    return $valori;
}

switch($req){
    # ! allUsers
    case 'allUsers':
        echo json_encode(getColumnValues("username", "users"));
        break;

    # ! objectPosition
    case 'objectPosition':
        global $connessione;
        $productName = $_POST['product'];
        $sql = "SELECT lane FROM products WHERE name = '$productName'";

        if($result = $connessione->query($sql)) {
            if($result->num_rows == 1) {
                echo json_encode($result->fetch_assoc()); # se vuoi solo il valore: $result->fetch_assoc()["lane"]
            }
        } else {
            echo json_encode(false);
        }
        break;

    # ! addToCart
    case 'addToCart':
        global $connessione;
        $user = $_POST["user"];
        $productName = $_POST["product"];

        $sql = "SELECT cart FROM users WHERE username = '$user'";
        if($result = $connessione->query($sql)) {
            if($result->num_rows == 1) {
                $result = $result->fetch_assoc()['cart'];
                $result = json_decode($result, true);
                
                if(array_key_exists($productName, $result)){
                    $result[$productName] += 1;
                } else {
                    $result[$productName] = 1;
                }

                $result = json_encode($result, true);

                $sql = "UPDATE users SET cart = '$result' WHERE username = '$user'";
                if($connessione->query($sql)){
                    echo json_encode(true);
                } else {
                    echo json_encode(false);
                }
            }
        } else {
            echo json_encode(false);
        }
        break;

    # ! checkout
    case 'checkout':
        global $connessione;
        $user = $_POST["user"];
        $total = 0.00;
    
        $sql = "SELECT cart FROM users WHERE username = '$user'";
        if($result = $connessione->query($sql)) {
            if($result->num_rows == 1) {
                $result = $result->fetch_assoc();

                $cart = json_decode($result['cart'], true);
                $elements = array_keys($cart);
                // calcolo del totale
                foreach ($elements as $name_element) {
                    $price = $connessione->query("SELECT price FROM products WHERE name  = '$name_element'");
                    $price = floatval(implode($price->fetch_assoc()));
                    
                    $total += $price * $cart[$name_element];
                }

                if($chronology = $connessione->query("SELECT chronology FROM users WHERE username = '$user'") and json_encode($cart) != '[]'){
                    $chronology = $chronology->fetch_assoc()['chronology'];
                    $chronology = json_decode($chronology, true);
                    array_push($chronology, $cart);
                    $chronology = json_encode($chronology);

                    if($connessione->query("UPDATE users SET chronology = '$chronology' WHERE username = '$user'")){
                        $connessione->query("UPDATE users SET cart = '{}' WHERE username = '$user'");
                    }
                }


            }
        }

        echo json_encode(array(
            'total' => $total
        ));


    default:
        break;
}

