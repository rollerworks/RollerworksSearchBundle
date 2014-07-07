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

interface ConfigurationInterface
{
    /**
     * @return int
     */
    public function getSearchMaxValues();

    /**
     * @return int
     */
    public function getSearchMaxGroups();

    /**
     * @return int
     */
    public function getSearchMaxNesting();
}
