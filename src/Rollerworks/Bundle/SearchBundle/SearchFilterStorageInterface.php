<?php

/**
 * This file is part of the RollerworksSearchBundle package.
 *
 * (c) 2014 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\SearchBundle;

/**
 * SearchFilterStorageInterface for storing search-filters
 * for persistent usage.
 *
 * A storage can be anything from a cookie, PHP session or
 * entry in a database.
 *
 * @package Rollerworks\Bundle\SearchBundle
 */
interface SearchFilterStorageInterface
{
    /**
     * Returns whether the storage contains the filter.
     *
     * @param string $filterName Name of the filter (eg. FieldSet name)
     *
     * @return bool
     */
    public function containsFilter($filterName);

    /**
     * Returns the filter-code from the storage.
     *
     * @param string $filterName Name of the filter (eg. FieldSet name)
     *
     * @return string
     */
    public function getFilterCode($filterName);

    /**
     * Sets the filter-code by filter-name.
     *
     * @param string $filterName Name of the filter (eg. FieldSet name)
     * @param string $filterCode Filtering code (encoded structure)
     */
    public function setFilterCode($filterName, $filterCode);

    /**
     * Removes the filter-code from the storage.
     *
     * @param string $filterName Name of the filter (eg. FieldSet name)
     */
    public function removeFilter($filterName);
}
