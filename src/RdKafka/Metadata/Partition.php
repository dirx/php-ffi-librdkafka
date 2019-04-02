<?php
declare(strict_types=1);

namespace RdKafka\Metadata;

class Partition
{
    private int $id;
    private int $err;
    private int $leader;
    private array $replicas;
    private array $isrs;

    public function __construct(int $id, int $err, int $leader, array $replicas, array $isrs)
    {
        $this->id = $id;
        $this->err = $err;
        $this->leader = $leader;
        $this->replicas = $replicas;
        $this->isrs = $isrs;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getErr(): int
    {
        return $this->err;
    }

    public function getLeader(): int
    {
        return $this->leader;
    }

    public function getReplicas(): array
    {
        return $this->replicas;
    }

    public function getIsrs(): array
    {
        return $this->isrs;
    }
}
