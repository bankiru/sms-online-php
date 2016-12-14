<?php

namespace Bankiru\Sms\SmsOnline;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class ClientTest extends \PHPUnit_Framework_TestCase
{
    /** @var MockHandler */
    static private $queue;
    /** @var ClientInterface */
    static private $client;

    public function testClientSendsRequest()
    {

        $user    = 'user';
        $sender  = 'Sender';
        $message = 'Test message';
        $token   = 'token';
        $phone   = '71234567890';

        $client = new SmsOnline(self::getGuzzleClient(), $user, $token, 'http://localhost/');

        $sign = md5($user . $sender . $phone . $message . $token);
        self::getQueue()->append(
            function (Request $request) use ($sign, $user, $sender, $phone, $message) {
                parse_str(parse_url($request->getUri(), PHP_URL_QUERY), $query);
                self::assertArrayHasKey('sign', $query);
                self::assertSame($sign, $query['sign']);
                self::assertArrayHasKey('user', $query);
                self::assertSame($user, $query['user']);
                self::assertArrayHasKey('phone', $query);
                self::assertSame($phone, $query['phone']);
                self::assertArrayHasKey('from', $query);
                self::assertSame($sender, $query['from']);
                self::assertArrayHasKey('txt', $query);
                self::assertSame($message, $query['txt']);

                return new Response(200, [], '<code>0</code>');
            }
        );

        self::assertTrue($client->send($phone, $message, $sender, '_message_'));

        self::getQueue()->append(new Response(200, [], '<code>241</code>'));
        self::assertFalse($client->send($phone, $message, $sender, '_message_'));
    }

    /**
     * @expectedException \GuzzleHttp\Exception\ServerException
     */
    public function testClientThrowsExceptionOnInvalidCode()
    {
        $user    = 'user';
        $sender  = 'Sender';
        $message = 'Test message';
        $token   = 'token';
        $phone   = '71234567890';

        $client = new SmsOnline(self::getGuzzleClient(), $user, $token, 'http://localhost/');
        self::getQueue()->append(new Response(504));
        $client->send($phone, $message, $sender, '_message_');
    }

    public function tearDown()
    {
        self::assertSame(0, self::$queue->count());

        self::$queue  = null;
        self::$client = null;
    }

    private static function getQueue()
    {
        if (!static::$queue) {
            static::$queue = new MockHandler();
        }

        return static::$queue;
    }

    private static function getGuzzleClient()
    {
        if (!self::$client) {
            $stack        = HandlerStack::create(self::getQueue());
            self::$client = new Client(['handler' => $stack]);
        }

        return self::$client;
    }
}
