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

use Rollerworks\Component\Search\SearchConditionInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * SearchProcessorInterface.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
interface SearchProcessorInterface
{
    /**
     * Processes the search data from the request.
     *
     * Returns false when there are violations, errors, or when there is no new filtering.
     * You should call isValid() to determine of the result is valid.
     *
     * @param Request $request
     *
     * @return self
     */
    public function processRequest(Request $request);

    /**
     * Gets the unique filtering-code.
     *
     * @return string
     */
    public function getFilterCode();

    /**
     * Gets the SearchCondition.
     *
     * @return SearchConditionInterface|null
     */
    public function getSearchCondition();

    /**
     * Gets the exported format of the SearchCondition.
     *
     * @param string $format
     *
     * @return string|array Exported format
     *
     * @throws \RuntimeException When there is no SearchCondition or its invalid
     */
    public function exportSearchCondition($format);

    /**
     * Gets processing error.
     *
     * @return array[]|\Traversable
     */
    public function getErrors();

    /**
     * Returns whether the processed result is valid.
     *
     * @return bool
     */
    public function isValid();
}
