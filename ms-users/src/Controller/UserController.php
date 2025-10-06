<?php
    require PROJECT_ROOT_PATH . '/config/db.php';
    require PROJECT_ROOT_PATH . '/src/Model/User.php';
    require PROJECT_ROOT_PATH . '/src/Model/Log.php';
    require PROJECT_ROOT_PATH . '/src/RdKafka/KafkaProducer.php';

    $method = $_SERVER['REQUEST_METHOD'];

    $log = new Log();

    // Basic routing
    if ($uri[1] === 'users') 
    {
        // We create UserModel instance
        $userModel = new User();

        $log->save("A $method request has been received: ".$url);

        switch ($method) 
        {
            case 'GET':

                if(isset($uri[2]) && trim($uri[2]) != "") 
                {
                    //Load table
                    if($uri[2] === "createTable")
                    {  
                        try
                        {
                            $userModel->createTable();
                            echo json_encode(
                                [
                                    "error" => ["code" => 0,"message" => ""],
                                    "message" => "Table and users created successfully!"
                                ]
                            );
                        }
                        catch(Exception $e)
                        {
                            echo json_encode(
                                [
                                    "error" => ["code" => 1,"message" => $e->getMessage()],
                                    "message" => ""
                                ]
                            );
                        }
                    }
                }
                break;

            case 'POST':

                if($uri[2] === "createNewUser")
                {
                    try
                    {
                        $userModel->saveUser($_POST["name"],$_POST["email"]);

                        $producer = new KafkaProducer("newUser");

                        $producer->produce(
                            json_encode([
                                "user"  => $_POST["name"],
                                "email" => $_POST["email"]
                            ])
                        );

                        //Response
                        echo json_encode(
                            [
                                "error" => ["code" => 0,"message" => ""],
                                "data" => [
                                    "message" => "User created successfully",
                                    "name"    => $_POST["name"],
                                    "email"   => $_POST["email"]
                                ]
                            ]
                        );
                    }
                    catch(Exception $e)
                    {
                        echo json_encode(
                            [
                                "error" => ["code" => 1,"message" => $e->getMessage()],
                                "user" => []
                            ]
                        );
                    }
                }
                break;
        }
    } 
    else if($uri[1] === 'resetLogs')
    {
        switch ($method) 
        {
            case 'GET':

                $log->reset();

            break;
        }
    }
    else
    {
        http_response_code(404);
        $log->save("Route not found");
        echo ("Route not found");
    }
?>