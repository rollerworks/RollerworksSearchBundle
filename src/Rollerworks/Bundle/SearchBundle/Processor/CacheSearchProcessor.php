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

use Doctrine\Common\Cache\Cache;
use Rollerworks\Component\Search\FieldSet;
use Rollerworks\Component\Search\SearchConditionSerializer;
use Symfony\Component\HttpFoundation\Request;

/**
 * CacheSearchProcessor caches processed request data
 * for better performance.
 *
 * Cached data only contains only input-to-searchCondition (after formatting)
 * and exported formats.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class CacheSearchProcessor extends AbstractSearchProcessor
{
    /**
     * @var Cache
     */
    private $cacheDriver;

    /**
     * @var SearchProcessorInterface
     */
    private $processor;

    /**
     * @var array
     */
    private $errors = array();

    /**
     * Constructor.
     *
     * @param SearchProcessorInterface $processor
     * @param Cache                    $cacheDriver
     * @param FieldSet                 $fieldSet
     * @param string                   $uriPrefix
     */
    public function __construct(
        SearchProcessorInterface $processor,
        Cache $cacheDriver,
        FieldSet $fieldSet,
        $uriPrefix = ''
    ) {
        $this->processor = $processor;
        $this->cacheDriver = $cacheDriver;
        $this->fieldSet = $fieldSet;
        $this->uriPrefix = $uriPrefix;
    }

    /**
     * {@inheritdoc}
     */
    public function processRequest(Request $request)
    {
        $this->request = $request;
        $this->filterCode = $this->getRequestParam('filter', '');
        $this->searchCondition = null;
        $this->errors = array();

        $isPost = 'POST' === $request->getMethod();
        if (!$isPost && '' === $this->filterCode) {
            return $this;
        }

        if ($isPost && $this->filterCode) {
            $this->clearCache();
        }

        if ($isPost || !$this->loadFromCache()) {
            if ($this->processor->processRequest($request)) {
                $this->errors = $this->processor->getErrors();
            } else {
                $this->searchCondition = $this->processor->getSearchCondition();
                $this->filterCode = $this->processor->getFilterCode();

                $this->storeCache();
            }
        }

        return $this;
    }

    public function storeCache()
    {
        if (!$this->searchCondition || $this->searchCondition->getValuesGroup()->hasErrors()) {
            return;
        }

        $this->cacheDriver->save(
            'search_condition.'.$this->fieldSet->getSetName().'.'.$this->filterCode,
            SearchConditionSerializer::serialize($this->searchCondition)
        );
    }

    /**
     * Clears the cache for the current condition.
     *
     * @return bool
     */
    public function clearCache()
    {
        if (!$this->filterCode) {
            return false;
        }

        $fieldSetName = $this->fieldSet->getSetName();
        $this->cacheDriver->delete('search_condition.'.$fieldSetName.'.'.$this->filterCode);

        foreach (array('json', 'xml', 'filter_query') as $format) {
            $this->cacheDriver->delete('search_export.'.$fieldSetName.'.'.$this->filterCode.'.'.$format);
        }

        return true;
    }

    /**
     * Returns the exported format of the SearchCondition.
     *
     * @param string $format
     *
     * @return string|array Exported format
     *
     * @throws \RuntimeException When there is no SearchCondition or its invalid
     */
    public function exportSearchCondition($format)
    {
        static::assertValidSearchFormat($format);

        if (null === $this->searchCondition || $this->searchCondition->getValuesGroup()->hasErrors()) {
            throw new \RuntimeException('Unable to export empty/invalid SearchCondition');
        }

        $cacheKey = 'search_export.'.$this->filterCode.'.'.$format;
        if ($this->cacheDriver->contains($cacheKey)) {
            return $this->cacheDriver->fetch($cacheKey);
        }

        $exported = $this->processor->exportSearchCondition($format);
        $this->cacheDriver->save($cacheKey, $exported);

        return $exported;
    }

    /**
     * Loads SearchCondition from the cache.
     *
     * @return bool
     */
    protected function loadFromCache()
    {
        $cacheKey = 'search_condition.'.$this->fieldSet->getSetName().'.'.$this->filterCode;
        if ($this->cacheDriver->contains($cacheKey)) {
            try {
                $this->searchCondition = SearchConditionSerializer::unserialize(
                    $this->fieldSet,
                    $this->cacheDriver->fetch($cacheKey)
                );

                return true;
            } catch (\Exception $e) {
                $this->clearCache();
            }
        }

        return false;
    }
}
