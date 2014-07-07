<?php

/**
 * This file is part of the RollerworksSearchBundle package.
 *
 * (c) 2014 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\SearchBundle\Processor;

use Rollerworks\Bundle\SearchBundle\DependencyInjection\Factory\FieldSet;

interface SearchProcessorFactoryInterface
{
    /**
     * Creates a new SearchProcessorInterface object instance.
     *
     * @param string|FieldSet        $fieldSet  FieldSet object or FieldSet name
     * @param ConfigurationInterface $config    Search Configuration object
     * @param string                 $uriPrefix URL prefix to allow multiple processors per page
     *
     * @return SearchProcessorInterface
     */
    public function createProcessor($fieldSet, ConfigurationInterface $config, $uriPrefix = '');
}
