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
use Rollerworks\Component\Search\Exception\UnsupportedValueTypeException;

class SearchUnsupportedValueTypeExceptionParser implements ExceptionParserInterface
{
    public function accepts(\Exception $exception)
    {
        return $exception instanceof UnsupportedValueTypeException;
    }

    public function parseException(\Exception $exception)
    {
        /** @var UnsupportedValueTypeException $exception */

        return array(
            'message' => 'Field "{{ field }}" does not accept '.$exception->getValueType().' values.',
            'field' => $exception->getFieldName(),
        );
    }
}
