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

use Rollerworks\Component\Search\FieldSet;
use Rollerworks\Component\Search\SearchConditionInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * AbstractCacheSearchProcessor provides the basic logic for all processors.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
abstract class AbstractSearchProcessor implements SearchProcessorInterface
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var SearchConditionInterface
     */
    protected $searchCondition;

    /**
     * @var string
     */
    protected $filterCode = '';

    /**
     * @var string
     */
    protected $uriPrefix;

    /**
     * @var FieldSet
     */
    protected $fieldSet;

    /**
     * @var array
     */
    protected $errors = array();

    /**
     * {@inheritdoc}
     */
    public function getFilterCode()
    {
        return $this->filterCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCondition()
    {
        return $this->searchCondition;
    }

    /**
     * Returns whether the processed result is valid.
     *
     * @return bool
     */
    public function isValid()
    {
        if (count($this->errors) > 0) {
            return false;
        }

        return !($this->searchCondition && $this->searchCondition->getValuesGroup()->hasErrors());
    }

    /**
     * {@inheritdoc}
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Gets a Request object value from the request.
     *
     * @param string          $name      Name of the parameter
     * @param string          $default   Default value to use when value is missing or invalid
     * @param bool            $query     Get from the instead of the POST
     * @param callable|string $validator Callback to validate the value, default is string
     *
     * @return mixed|string
     */
    protected function getRequestParam($name, $default = '', $query = true, $validator = 'is_string')
    {
        $params = $query ? $this->request->query : $this->request->request;
        if ($this->uriPrefix) {
            $value = $params->get($this->uriPrefix.'['.$name.']', $default, true);
        } else {
            $value = $params->get($name, $default);
        }

        // Use default when invalid
        if ($validator && !call_user_func($validator, $value)) {
            $value = $default;
        }

        return $value;
    }

    /**
     * Checks if the processing format supported.
     *
     * @param string $format
     *
     * @throws \InvalidArgumentException when format is invalid unsupported
     */
    protected static function assertValidSearchFormat($format)
    {
        if (!is_string($format) || !in_array($format, array('json', 'xml', 'filter_query'), true)) {
            throw new \InvalidArgumentException('Unsupported format, only accepts: json, xml or filter_query.');
        }
    }
}
