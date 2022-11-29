<?php

namespace AppBundle\Monolog\Handler;

use AppBundle\Entity\EventLog;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DatabaseHandler extends AbstractProcessingHandler
{
    /**
     * @var EntityManagerInterface
     */
    protected
        $em;

    /**
     * MonologDBHandler constructor.
     * @param EntityManagerInterface $em
     * @param bool|int $level
     * @param bool $bubble
     */
    public function __construct(EntityManagerInterface $em, $level = Logger::INFO, $bubble = true)
    {
        $this->em = $em;
        parent::__construct($level,$bubble);

    }

    /**
     * Called when writing to our database
     * @param array $record
     */
    protected function write(array $record)
    {
        $logEntry = new EventLog();
        $logEntry->setMessage($record['message']);
        $logEntry->setLevel($record['level']);
        $logEntry->setLevelName($record['level_name']);
        $logEntry->setExtra($record['extra']);
        $logEntry->setChannel($record['channel']);
        $logEntry->setContext($record['context']);

        $this->em->persist($logEntry);
        $this->em->flush();

    }
}