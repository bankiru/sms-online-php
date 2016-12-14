<?php

namespace Bankiru\Sms\SmsOnline\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /** {@inheritdoc} */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $root    = $builder->root('sms_online');

        $root->children()
             ->scalarNode('login')
             ->defaultNull()
             ->isRequired();
        $root->children()
             ->scalarNode('password')
             ->isRequired();
        $root->children()
             ->scalarNode('url')
             ->defaultValue('https://bulk.sms-online.com/')
             ->info('SMS Online API Url')
             ->isRequired();
        $root->children()->scalarNode('sender')->defaultNull()->isRequired();
        $root->children()->scalarNode('transaction_prefix')->defaultNull();

        return $builder;
    }
}
