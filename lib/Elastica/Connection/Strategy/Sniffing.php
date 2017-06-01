<?php
/**
 * Created by PhpStorm.
 * User: slebrequier
 * Date: 01/06/17
 * Time: 11:17
 */

namespace Elastica\Connection\Strategy;

use Elastica\Connection;

class Sniffing implements StrategyInterface
{
    /** @var int  */
    private $sniffingInterval = 300;

    /** @var  int */
    private $nextSniff = -1;

    /**
     * @param array|Connection[] $connections
     *
     * @return Connection
     * @throws \Exception
     */
    public function getConnection($connections)
    {
        $this->sniff($connections);

        shuffle($connections);

        foreach ($connections as $connection) {
            /** @var \Elastica\Connection $connection */
            if ($connection->isAlive() && $connection->ping()) {
                return $connection;
            }
        }

        throw new \Exception("No alive nodes found in your cluster");
    }

    public function scheduleCheck()
    {
        $this->nextSniff = -1;
    }

    /**
     * @param $connections
     */
    private function sniff($connections)
    {
        if ($this->nextSniff >= time()) {
            return;
        }

        foreach ($connections as $connection) {
            if (!$connection->isAlive()) {
                $connection->ping();
            }
        }

        $this->nextSniff = time() + $this->sniffingInterval;
    }
}

