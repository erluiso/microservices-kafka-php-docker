<?php
    define("PROJECT_ROOT_PATH", __DIR__);

    require PROJECT_ROOT_PATH . '/src/Model/Log.php';
    require PROJECT_ROOT_PATH . '/src/RdKafka/KafkaConsumer.php';

    $log  = new Log();
    $KafkaConsumer = new KafkaConsumer("newUser");
    $consumer = $KafkaConsumer->getConsumer();

    while (true) {
        $message = $consumer->consume(5*1000);
        switch ($message->err) {
            case RD_KAFKA_RESP_ERR_NO_ERROR:
                $log->save("A message has been received!");

                $message = json_decode(json_encode($message), true);
                $data = json_decode($message["payload"], true);

                $log->save("Calling to register email microservice with the email: ".$data["email"]);
                callToEmailMs(["email" => $data["email"]]);
                
                break;
            case RD_KAFKA_RESP_ERR__TIMED_OUT:
                $log->save("Timed out");
                break;
            case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                break;
            default:
                $log->save("Error: ".$message->err." Description: ".$message->errstr());
                break;
        }
    }

    /**
     * Call to register email microservice to send the email
     */
    function callToEmailMs($data)
    {
        $fields_string = http_build_query($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "email-service:80/emails/sendRegisterEmail");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        if(curl_exec($ch) === false)
        {
            $log->save("Curl error: " . curl_error($ch));
        }

        curl_close($ch);
    }