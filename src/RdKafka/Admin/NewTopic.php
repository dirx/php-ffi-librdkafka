<?php
declare(strict_types=1);

namespace RdKafka\Admin;

use Assert\Assert;
use FFI\CData;
use RdKafka\Api;
use RdKafka\Exception;

class NewTopic extends Api
{
    private ?CData $topic;

    public function __construct(string $name, int $num_partitions, int $replication_factor)
    {
        parent::__construct();

        $errstr = \FFI::new("char[512]");
        $this->topic = self::$ffi->rd_kafka_NewTopic_new(
            $name,
            $num_partitions,
            $replication_factor,
            $errstr,
            \FFI::sizeOf($errstr)
        );

        if ($this->topic === null) {
            throw new Exception(\FFI::string($errstr));
        }
    }

    public function __destruct()
    {
        if ($this->topic === null) {
            return;
        }

        self::$ffi->rd_kafka_NewTopic_destroy($this->topic);
    }

    public function getCData(): CData
    {
        return $this->topic;
    }

    public function setReplicaAssignment(int $partition_id, array $broker_ids)
    {
        Assert::that($broker_ids)->notEmpty()->all()->integer();

        $brokerIdsCount = count($broker_ids);
        $brokerIds = \FFI::new('int*[' . $brokerIdsCount . ']');
        foreach (array_values($broker_ids) as $i => $broker_id) {
            $int = \FFI::new("int");
            $int->cdata = $broker_id;
            $brokerIds[$i] = \FFI::addr($int);
        }

        $errstr = \FFI::new("char[512]");
        $err = (int)self::$ffi->rd_kafka_NewTopic_set_replica_assignment(
            $this->topic,
            $partition_id,
            $brokerIds[0],
            $brokerIdsCount,
            $errstr,
            \FFI::sizeOf($errstr)
        );

        if ($err != RD_KAFKA_RESP_ERR_NO_ERROR) {
            throw new Exception(\FFI::string($errstr));
        }
    }

    public function setConfig(string $name, string $value)
    {
        $err = self::$ffi->rd_kafka_NewTopic_set_config($this->topic, $name, $value);

        if ($err != RD_KAFKA_RESP_ERR_NO_ERROR) {
            throw new Exception(self::err2str($err));
        }
    }
}
