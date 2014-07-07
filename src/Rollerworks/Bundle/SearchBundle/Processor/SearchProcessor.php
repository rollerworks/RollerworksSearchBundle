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

use Rollerworks\Bundle\SearchBundle\ExceptionParser;
use Rollerworks\Component\ExceptionParser\ExceptionParserManager;
use Rollerworks\Component\Search\Extension\Symfony\DependencyInjection\ExporterFactory;
use Rollerworks\Component\Search\Extension\Symfony\DependencyInjection\InputFactory;
use Rollerworks\Component\Search\FieldSet;
use Rollerworks\Component\Search\FormatterInterface;
use Rollerworks\Component\Search\Input\AbstractInput;
use Rollerworks\Component\UriEncoder\UriEncoderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * SearchProcessorInterface processes search-data.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
class SearchProcessor extends AbstractSearchProcessor
{
    /**
     * @var ConfigurationInterface
     */
    protected $config;

    /**
     * @var InputFactory
     */
    protected $inputFactory;

    /**
     * @var ExporterFactory
     */
    protected $exportFactory;

    /**
     * @var FormatterInterface
     */
    protected $formatter;

    /**
     * @var UriEncoderInterface
     */
    protected $uriEncoder;

    /**
     * Constructor.
     *
     * @param InputFactory           $inputFactory
     * @param ExporterFactory        $exportFactory
     * @param FormatterInterface     $formatter
     * @param UriEncoderInterface    $uirEncoder
     * @param ConfigurationInterface $config        ControllerResourceConfig
     * @param FieldSet               $fieldSet      FieldSet instance for filtering
     * @param string                 $uriPrefix     URI-prefix, used when there are multiple filters on a page
     */
    public function __construct(
        InputFactory $inputFactory,
        ExporterFactory $exportFactory,
        FormatterInterface $formatter,
        UriEncoderInterface $uirEncoder,
        ConfigurationInterface $config,
        FieldSet $fieldSet,
        $uriPrefix = ''
    ) {
        $this->inputFactory = $inputFactory;
        $this->exportFactory = $exportFactory;
        $this->formatter = $formatter;
        $this->uriEncoder = $uirEncoder;

        $this->config = $config;
        $this->fieldSet = $fieldSet;
        $this->uriPrefix = (string) $uriPrefix;
    }

    /**
     * {@inheritdoc}
     */
    public function processRequest(Request $request)
    {
        $this->request = $request;
        $isPost = $request->isMethod('POST');

        $input = $this->getRequestParam('filter', '', !$isPost);
        $format = $this->getRequestParam('format', 'filter_query', !$isPost);

        if ('' === $input) {
            $this->searchCondition = null;
            $this->filterCode = '';
            $this->errors = array();

            return $this;
        }

        if (!$isPost) {
            $input = $this->uriEncoder->decodeUri($input);
        }

        static::assertValidSearchFormat($format);
        $this->processInput($input, $format);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function exportSearchCondition($format)
    {
        static::assertValidSearchFormat($format);

        if (null === $this->searchCondition || $this->searchCondition->getValuesGroup()->hasErrors()) {
            throw new \RuntimeException('Unable to export empty/invalid SearchCondition');
        }

        $export = $this->exportFactory->create($format);

        return $export->exportCondition($this->searchCondition, 'filter_query' === $format);
    }

    /**
     * Processes the input to a SearchCondition.
     *
     * @param string $input
     * @param string $format
     */
    protected function processInput($input, $format)
    {
        if ('' === $input) {
            return;
        }

        $this->filterCode = '';

        try {
            $this->searchCondition = $this->getInputProcessor($this->fieldSet, $format)->process($input);
        } catch (\Exception $e) {
            $this->errors = array($this->getExceptionMessage($e));
            $this->searchCondition = null;

            return;
        }

        $this->formatter->format($this->searchCondition);

        if (!$this->searchCondition->getValuesGroup()->hasErrors()) {
            $this->filterCode = $this->uriEncoder->encodeUri($this->exportSearchCondition('filter_query'));
        }
    }

    /**
     * Transforms the Exception to a Message object.
     *
     * @param \Exception $exception
     *
     * @return array
     *
     * @throws \Exception When exception can not be parsed
     */
    protected function getExceptionMessage(\Exception $exception)
    {
        $exceptionParser = new ExceptionParserManager('{{ {var} }}');
        $exceptionParser->addExceptionParser(new ExceptionParser\QueryExceptionParser());
        $exceptionParser->addExceptionParser(new ExceptionParser\SearchFieldRequiredExceptionParser());
        $exceptionParser->addExceptionParser(new ExceptionParser\SearchGroupsOverflowExceptionParser());
        $exceptionParser->addExceptionParser(new ExceptionParser\SearchValuesOverflowExceptionParser());
        $exceptionParser->addExceptionParser(new ExceptionParser\SearchGroupsNestingExceptionParser());
        $exceptionParser->addExceptionParser(new ExceptionParser\SearchUnsupportedValueTypeExceptionParser());
        $exceptionParser->addExceptionParser(new ExceptionParser\SearchUnknownFieldExceptionParser());

        $params = $exceptionParser->processException($exception);

        // No compatible parser re-throw for external caching
        if (array() === $params) {
            throw $exception;
        }

        return $params;
    }

    /**
     * Gets a new InputProcessor for the given format.
     *
     * @param FieldSet $fieldset
     * @param string   $inputFormat
     *
     * @return AbstractInput
     */
    protected function getInputProcessor(FieldSet $fieldset, $inputFormat)
    {
        /** @var AbstractInput $inputProcessor */
        $inputProcessor = $this->inputFactory->create($inputFormat);
        $inputProcessor->setFieldSet($fieldset);
        $inputProcessor->setMaxValues($this->config->getSearchMaxValues());
        $inputProcessor->setMaxGroups($this->config->getSearchMaxGroups());
        $inputProcessor->setMaxNestingLevel($this->config->getSearchMaxNesting());

        return $inputProcessor;
    }
}
