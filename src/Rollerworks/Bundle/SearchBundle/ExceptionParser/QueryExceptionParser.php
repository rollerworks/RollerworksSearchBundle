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
use Rollerworks\Component\Search\Input\FilterQuery\QueryException;

class QueryExceptionParser implements ExceptionParserInterface
{
    public function accepts(\Exception $exception)
    {
        return $exception instanceof QueryException;
    }

    public function parseException(\Exception $exception)
    {
        if (preg_match('/^\[Syntax Error\] line (?P<line>\d+), col (?P<column>\d+): Error: Expected \'(?P<expected>[^\']+)\', got (?P<got>\'[^\']+\'|end of string\.)$/i', $exception->getMessage(), $matches)) {
            return array(
                'message' => 'rollerworks_search.syntax_error_got',
                'line' => $matches['line'],
                'column' => $matches['column'],
                'expected' => $matches['expected'],
                'got' => $matches['got'],
                'unexpected_end' => 'rollerworks_search.end_of_string' === $matches['got'],
            );
        }

        if (preg_match('/^\[Syntax Error\] line (?P<line>\d+), col (?P<column>\d+): Error: Unexpected (?P<unexpected>\'[^\']+\'|end of string\.)$/i', $exception->getMessage(), $matches)) {
            return array(
                'message' => 'rollerworks_search.syntax_error',
                'line' => $matches['line'],
                'column' => $matches['column'],
                'unexpected' => $matches['unexpected'],
                'unexpected_end' => 'rollerworks_search.end_of_string' === $matches['unexpected'],
            );
        }

        return array(
            'message' => $exception->getMessage(),
            'line' => 0,
            'column' => 0,
            'unexpected_end' => false,
        );
    }
}
