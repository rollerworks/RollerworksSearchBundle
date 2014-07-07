<?php

/**
 * This file is part of the RollerworksSearchBundle package.
 *
 * (c) 2014 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\SearchBundle\ExceptionParser;

use Rollerworks\Component\ExceptionParser\ExceptionParserInterface;
use Rollerworks\Component\Search\Exception\GroupsOverflowException;

class SearchGroupsOverflowExceptionParser implements ExceptionParserInterface
{
    public function accepts(\Exception $exception)
    {
        return $exception instanceof GroupsOverflowException;
    }

    public function parseException(\Exception $exception)
    {
        /** @var GroupsOverflowException $exception */

        return array(
            'message' => 'Group {{ group }} at nesting level %d exceeds maximum number of groups, maximum: %d, total of groups: %d.',
            'group' => $exception->getGroupIdx(),
            'nesting' => $exception->getNestingLevel(),
            'max' => $exception->getMax(),
            'count' => $exception->getCount(),
        );
    }
}
