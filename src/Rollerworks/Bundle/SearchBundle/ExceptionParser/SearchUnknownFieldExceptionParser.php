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
use Rollerworks\Component\Search\Exception\UnknownFieldException;

class SearchUnknownFieldExceptionParser implements ExceptionParserInterface
{
    public function accepts(\Exception $exception)
    {
        return $exception instanceof UnknownFieldException;
    }

    public function parseException(\Exception $exception)
    {
        /** @var UnknownFieldException $exception */

        return array(
            'message' => 'Field "{{ field }}" is not registered in the FieldSet or available as alias.',
            'field' => $exception->getFieldName(),
        );
    }
}
