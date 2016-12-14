<?php

namespace Bankiru\Sms\SmsOnline\DependencyInjection;

use Bankiru\Sms\SmsOnline\SmsOnline;
use Bankiru\Sms\SmsOnline\SmsOnlineTransport;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

class SmsOnlineExtension extends Extension
{
    /** {@inheritdoc} */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $client = $container->register('sms_online.client', SmsOnline::class);
        $client->setArguments(
            [
                new Definition(Client::class),
                $config['login'],
                $config['password'],
                $config['url'],
            ]
        );

        $transport = $container->register('sms_online.transport', SmsOnlineTransport::class);
        $transport->setArguments(
            [
                $client,
                $config['sender'],
                new Reference('logger'),
                $config['transaction_prefix']
            ]
        );
    }
}
