<?php

declare(strict_types=1);

namespace Rollerworks\Bundle\SearchBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * If FOS Elastica Bundle is detected, use its client as our client.
 *
 * Class ElasticaBundlePass.
 */
class ElasticaBundlePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('fos_elastica.client.default')) {
            return;
        }

        $container->addAliases([
            'rollerworks_search.elasticsearch.client' => 'fos_elastica.client.default',
        ]);
    }
}
