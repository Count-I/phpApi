<?php

    require_once 'DbConnect.php';

    $response = array();
    $target_dir = "uploads/";

    if(isset($_GET['apicall'])){
        switch($_GET['apicall']){

            case 'upload':

                $message = "Params ";
                $is_error = false;

                if(!isset($_POST['desc'])){
                    $is_error = true;
                    $message .= " desc, ";

                }

                if(!isset($_FILES['image']['name'])){
                    $is_error = true;
                    $message .= "image is required";
                }

                if($is_error){
                    $response['error'] = true;
                    $response['message'] = $message;
                }else{

                    $target_file = $target_dir .  uniqid()  . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                    
                    if(move_uploaded_file($_FILES['image']['tmp_name'], $target_file)){

                        $stmt = $conn->prepare("INSERT INTO uploads (`path`, `description`) VALUES(?,?);");
                        $stmt->bind_param("ss", $target_file, $_POST['desc']);

                        if($stmt->execute()){
                            $response['error'] = false;
                            $response['message'] = "Image uploaded successfully";
                            $response['image'] = getBaseURL() . $target_file;
                        }else{
                            $response['error'] = true;
                            $response['message'] = "Something gone wrong... 1";
                        }
                    }else{
                        $response['error'] = true;
                        $response['message'] = "Something gone wrong... 2"; 
                    }   
                }
            break;

            case 'images': 

                $stmt = $conn->prepare("SELECT id, path, description FROM uploads;");
                $stmt->execute();
                $stmt->bind_result($id,$path,$desc);

                while($stmt->fetch()){
                    $image = array();
                    $image['id'] = $id;
                    $image['path'] = getBaseURL() . $path;
                    $image['desc'] = $desc;
                    array_push($response, $image);
                }
            break;
        }
    }

    function getBaseURL(){
        $url = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
        $url .= $_SERVER['SERVER_NAME'];
        $url .= $_SERVER['REQUEST_URI'];
        return dirname($url) . '/'; 
    }

    header('Content-Type:application/json');
    echo json_encode($response);

?>