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
use Rollerworks\Component\Search\Exception\ValuesOverflowException;

class SearchValuesOverflowExceptionParser implements ExceptionParserInterface
{
    public function accepts(\Exception $exception)
    {
        return $exception instanceof ValuesOverflowException;
    }

    public function parseException(\Exception $exception)
    {
        /** @var ValuesOverflowException $exception */

        return array(
            'message' => 'Field {{ field }} in group {{ group }} at nesting level {{ nesting }} exceeds the maximum number values per group, maximum: {{ max }}, total of values: {{ count }}.',
            'field' => $exception->getFieldName(),
            'group' => $exception->getGroupIdx(),
            'nesting' => $exception->getNestingLevel(),
            'max' => $exception->getMax(),
            'count' => $exception->getCount(),
        );
    }
}
