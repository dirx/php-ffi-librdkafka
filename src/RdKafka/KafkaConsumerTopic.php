<?php

declare(strict_types=1);

namespace RdKafka;

use InvalidArgumentException;

class KafkaConsumerTopic extends Topic
{
    public function __construct(KafkaConsumer $consumer, string $name, TopicConf $conf = null)
    {
        parent::__construct($consumer, $name, $conf);
    }

    /**
     * @param int $partition
     * @param int $offset
     *
     * @return void
     * @throws Exception
     */
    public function offsetStore(int $partition, int $offset)
    {
        if ($partition != RD_KAFKA_PARTITION_UA && ($partition < 0 || $partition > 0x7FFFFFFF)) {
            throw new InvalidArgumentException(sprintf("Out of range value '%d' for partition", $partition));
        }

        $err = self::$ffi->rd_kafka_offset_store(
            $this->topic,
            $partition,
            $offset
        );

        if ($err != RD_KAFKA_RESP_ERR_NO_ERROR) {
            throw new Exception(self::err2str($err));
        }
    }
}
