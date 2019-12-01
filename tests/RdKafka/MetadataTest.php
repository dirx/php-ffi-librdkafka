<?php

declare(strict_types=1);

namespace RdKafka;

use PHPUnit\Framework\TestCase;
use RdKafka\Metadata\Broker;
use RdKafka\Metadata\Collection;
use RdKafka\Metadata\Partition;
use RdKafka\Metadata\Topic;

/**
 * @covers \RdKafka\Metadata
 * @covers \RdKafka\Metadata\Broker
 * @covers \RdKafka\Metadata\Partition
 * @covers \RdKafka\Metadata\Topic
 */
class MetadataTest extends TestCase
{
    private $metadata;

    protected function setUp(): void
    {
        $conf = new Conf();
        $conf->set('metadata.broker.list', KAFKA_BROKERS);
        $producer = new Producer($conf);

        $this->metadata = $producer->getMetadata(true, null, (int)KAFKA_TEST_TIMEOUT_MS);
    }

    public function testGetBrokers()
    {
        $brokers = $this->metadata->getBrokers();

        $this->assertInstanceOf(Collection::class, $brokers);
        $this->assertCount(1, $brokers);

        /** @var Broker $broker */
        $broker = $brokers->current();

        $this->assertGreaterThan(0, $broker->getId());
        $this->assertEquals('kafka', $broker->getHost());
        $this->assertEquals(9092, $broker->getPort());
    }

    public function testGetTopics()
    {
        $topics = $this->metadata->getTopics();

        $this->assertInstanceOf(Collection::class, $topics);
        $this->assertGreaterThan(0, $topics->count());

        /** @var Topic $topic */
        $topic = $topics->current();

        $this->assertGreaterThan('__consumer_offsets', $topic->getTopic());
        $this->assertEquals(RD_KAFKA_RESP_ERR_NO_ERROR, $topic->getErr());

        $partitions = $topic->getPartitions();
        $this->assertInstanceOf(Collection::class, $partitions);

        /** @var Partition $partition */
        $partition = $partitions->current();

        $this->assertEquals(0, $partition->getId());
        $this->assertEquals((int)KAFKA_BROKER_ID, $partition->getIsrs()->current());
        $this->assertEquals((int)KAFKA_BROKER_ID, $partition->getLeader());
        $this->assertEquals((int)KAFKA_BROKER_ID, $partition->getReplicas()->current());
    }

    public function testGetOrigBrokerId()
    {
        $this->assertEquals((int)KAFKA_BROKER_ID, $this->metadata->getOrigBrokerId());
    }

    public function testGetOrigBrokerName()
    {
        $this->assertEquals(KAFKA_BROKERS . '/' . KAFKA_BROKER_ID, $this->metadata->getOrigBrokerName());
    }
}
