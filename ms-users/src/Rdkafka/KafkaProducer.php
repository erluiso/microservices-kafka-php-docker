<?php

    declare(strict_types=1);

    /**
     * Kafka producer to send messages to a Kafka topic
     */
    class KafkaProducer
    {
        private $producer = null;
        private $log = null;

        private string $topic;

        /**
         * Constructor
         */
        public function __construct(string $topic)
        {
            try
            {
                $this->topic = $topic;
                $this->producer = new \RdKafka\Producer();
                $this->producer->setLogLevel(LOG_DEBUG);

                if($this->producer->addBrokers("kafka:9092") < 1)
                {
                    throw new Exception("Error adding brokers");
                }
            }
            catch(Exception $e)
            {
                throw $e;
            }
        }

        /**
         * Producer to send messages to a Kafka topic
         */
        public function produce(string $message): void
        {
            try
            {
                $topic = $this->producer->newTopic($this->topic);

                if (!$this->producer->getMetadata(false, $topic, 2000)) 
                {
                    throw new Exception("Failed to get metadata, is broker down?");
                }

                $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);

                $response = $this->producer->flush(1000); // Wait for delivery reports
                
                if($response != RD_KAFKA_RESP_ERR_NO_ERROR)
                {
                    throw new Exception("RDkafka producer: error sedding message => ".RD_KAFKA_RESP_ERR_NO_ERROR);
                }
            }
            catch(Exception $e)
            {
                throw $e;
            }
        }
    }
?>