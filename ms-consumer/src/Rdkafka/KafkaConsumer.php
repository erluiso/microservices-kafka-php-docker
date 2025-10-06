<?php

    declare(strict_types=1);

    /**
     * Kafka consumer to get messages from Kafka
     */
    class KafkaConsumer
    {
        private \RdKafka\KafkaConsumer $consumer;
        private $log = null;

        /**
         * Constructor
         * @param $topic
         */
        public function __construct(string $topic)
        {
            $this->log = new Log();
            $this->log->save("Init the consumer");
            
            $this->consumer = new RdKafka\KafkaConsumer(
                $this->initConfig()
            );

            // Subscribe to topic
            $this->consumer->subscribe([$topic]);
        }

        /**
         * Get the consumer
         */
        public function getConsumer()
        {
            return $this->consumer;
        }

        /**
         * Creates the init config to the consumer
         */
        private function initConfig()
        {
            $conf = new RdKafka\Conf();

            // Set a rebalance callback to log partition assignments (optional)
            $conf->setRebalanceCb(function (RdKafka\KafkaConsumer $kafka, $err, array $partitions = null) {
                switch ($err) 
                {
                    case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
                        $kafka->assign($partitions);
                        break;

                    case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                        $kafka->assign(NULL);
                        break;

                    default:
                        throw new \Exception($err);
                }
            });

            // Configure the group.id. All consumer with the same group.id will consume
            // different partitions.
            $conf->set('group.id', 'myConsumerGroup');

            // Initial list of Kafka brokers
            $conf->set('metadata.broker.list', 'kafka:9092');

            // Set where to start consuming messages when there is no initial offset in
            // offset store or the desired offset is out of range.
            // 'earliest': start from the beginning
            $conf->set('auto.offset.reset', 'earliest');

            // Emit EOF event when reaching the end of a partition
            $conf->set('enable.partition.eof', 'true');

            return $conf;
        }
    }
?>