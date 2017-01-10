<?php

namespace Bankiru\Sms\SmsOnline;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ScayTrase\SmsDeliveryBundle\Exception\DeliveryFailedException;
use ScayTrase\SmsDeliveryBundle\Service\ShortMessageInterface;
use ScayTrase\SmsDeliveryBundle\Transport\TransportInterface;

final class SmsOnlineTransport implements TransportInterface
{
    /** @var  SmsOnline */
    private $client;
    /** @var  LoggerInterface */
    private $logger;
    /** @var  string */
    private $sender;
    /** @var string */
    private $transactionPrefix;

    /**
     * SmsOnlineTransport constructor.
     *
     * @param SmsOnline $client
     * @param string $sender
     * @param LoggerInterface $logger
     * @param string $transactionPrefix
     */
    public function __construct(SmsOnline $client, $sender, LoggerInterface $logger = null, $transactionPrefix = '')
    {
        $this->client = $client;
        $this->sender = (string)$sender;
        $this->logger = $logger ?: new NullLogger();
        $this->transactionPrefix = $transactionPrefix;
    }

    /** {@inheritdoc} */
    public function send(ShortMessageInterface $message)
    {
        try {
            return (bool)$this->client->send(
                $message->getRecipient(),
                $message->getBody(),
                $this->sender,
                $this->getTransactionPrefix($message)
            );
        } catch (GuzzleException $e) {
            throw new DeliveryFailedException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param ShortMessageInterface $message
     * @return string
     */
    private function getTransactionPrefix(ShortMessageInterface $message)
    {
        $prefix = $this->transactionPrefix;

        if ($message instanceof PrefixedSmsInterface) {
            $prefix = $message->getTransactionPrefix() ?: $prefix;
        }

        return $prefix . sha1(random_bytes(20));
    }
}
