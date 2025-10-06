<?php
    header("Access-Control-Allow-Origin: *"); // Allow CORS (if required)
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    require PROJECT_ROOT_PATH . '/config/db.php';
    require PROJECT_ROOT_PATH . '/src/Model/Email.php';
    require PROJECT_ROOT_PATH . '/src/Model/Log.php';

    $method = $_SERVER['REQUEST_METHOD'];

    $log = new Log();

    // Basic routing
    if ($uri[1] === 'emails') 
    {
        // We create UserModel instance
        $emailModel = new Email();

        $log->save("A $method request has been received: ".$url);

        switch ($method) 
        {
            case 'GET':

                if(isset($uri[2]) && trim($uri[2]) != "") 
                {
                    //Create the emails table
                    if($uri[2] === "createTable")
                    {  
                        try
                        {
                            $emailModel->createTable();
                            echo json_encode(
                                [
                                    "error" => ["code" => 0,"message" => ""],
                                    "message" => "Email table created successfully!"
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
                else
                {
                    //Get all emails sended
                    try
                    {
                        $emails = $emailModel->getEmails();
                        echo json_encode(
                            [
                                "error" => ["code" => 0,"message" => ""],
                                "emails" => $emails
                            ]
                        );
                    }
                    catch(Exception $e)
                    {
                        echo json_encode(
                            [
                                "error" => ["code" => 1,"message" => $e->getMessage()],
                                "emails" => []
                            ]
                        );
                    }
                }

                break;

            case 'POST':

                if($uri[2] === "sendRegisterEmail")
                {  
                    try
                    {
                        $emailModel->save($_POST["email"]);

                        //Here we will write the email sender
                        $log->save("The register email has been sended to => ".$_POST["email"]);

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