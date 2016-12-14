<?php

namespace Bankiru\Sms\SmsOnline;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use ScayTrase\SmsDeliveryBundle\Exception\DeliveryFailedException;

final class SmsOnline
{
    /** @var ClientInterface */
    private $client;
    /** @var  string */
    private $url;
    /** @var string */
    private $user;
    /** @var string */
    private $token;

    /**
     * @param ClientInterface $client
     * @param string          $user
     * @param string          $token
     * @param string          $url
     */
    public function __construct(ClientInterface $client, $user, $token, $url)
    {
        $this->client = $client;
        $this->url    = $url;
        $this->user   = $user;
        $this->token  = $token;
    }

    /**
     * @param int    $phone
     * @param string $message
     * @param string $from
     * @param string $messageId
     *
     * @return bool
     *
     * @throws GuzzleException
     * @throws DeliveryFailedException
     */
    public function send($phone, $message, $from, $messageId)
    {
        $get = [
            'user'             => $this->user,
            'from'             => $from,
            'phone'            => $phone,
            'txt'              => $message,
            'sign'             => $this->getSign($from, $phone, $message),
            'dlr'              => 1,
            'p_transaction_id' => $messageId,
        ];

        $result = $this->client->request('GET', $this->getUrl($get));

        if ($result->getStatusCode() !== 200) {
            throw new DeliveryFailedException($result->getReasonPhrase());
        }

        return strpos((string)$result->getBody(), '<code>0</code>') !== false;
    }

    /**
     * @param string $from
     * @param int    $phone
     * @param string $message
     *
     * @return string
     */
    private function getSign($from, $phone, $message)
    {
        return md5($this->user . $from . $phone . $message . $this->token);
    }

    /**
     * @param array $get
     *
     * @return string
     */
    private function getUrl(array $get)
    {
        return $this->url . (false === strpos($this->url, '?') ? '?' : '&') . http_build_query($get);
    }
}
