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
use Rollerworks\Component\Search\Exception\FieldRequiredException;

class SearchFieldRequiredExceptionParser implements ExceptionParserInterface
{
    public function accepts(\Exception $exception)
    {
        return $exception instanceof FieldRequiredException;
    }

    public function parseException(\Exception $exception)
    {
        /** @var FieldRequiredException $exception */

        return array(
            'message' => 'Field "{{ field }}" is required but is missing in group {{ group }} at nesting level {{ nesting }}.',
            'field' => $exception->getFieldName(),
            'group' => $exception->getGroupIdx(),
            'nesting' => $exception->getNestingLevel(),
        );
    }
}
