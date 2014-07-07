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
use Rollerworks\Component\Search\Extension\Symfony\DependencyInjection\ExporterFactory;
use Rollerworks\Component\Search\Extension\Symfony\DependencyInjection\FieldSetRegistry;
use Rollerworks\Component\Search\Extension\Symfony\DependencyInjection\InputFactory;
use Rollerworks\Component\Search\FieldSet;
use Rollerworks\Component\Search\FormatterInterface;
use Rollerworks\Component\UriEncoder\UriEncoderInterface;

/**
 * SearchProcessorFactory creates a new SearchProcessor
 * or CacheSearchProcessor for usage.
 */
class SearchProcessorFactory implements SearchProcessorFactoryInterface
{
    /**
     * @var InputFactory
     */
    private $inputFactory;

    /**
     * @var ExporterFactory
     */
    private $exporterFactory;

    /**
     * @var FieldSetRegistry
     */
    private $fieldSetRegistry;

    /**
     * @var UriEncoderInterface
     */
    private $uriEncoder;

    /**
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * @var Cache
     */
    private $cacheAdapter;

    /**
     * Constructor.
     *
     * @param InputFactory        $inputFactory
     * @param ExporterFactory     $exporterFactory
     * @param FormatterInterface  $formatter
     * @param FieldSetRegistry    $fieldSetRegistry
     * @param UriEncoderInterface $uriEncoder
     * @param Cache               $cacheAdapter
     */
    public function __construct(
        InputFactory $inputFactory,
        ExporterFactory $exporterFactory,
        FormatterInterface $formatter,
        FieldSetRegistry $fieldSetRegistry,
        UriEncoderInterface $uriEncoder,
        Cache $cacheAdapter
    ) {
        $this->inputFactory = $inputFactory;
        $this->exporterFactory = $exporterFactory;
        $this->fieldSetRegistry = $fieldSetRegistry;
        $this->uriEncoder = $uriEncoder;
        $this->formatter = $formatter;
        $this->cacheAdapter = $cacheAdapter;
    }

    /**
     * Creates a new SearchProcessor instance.
     *
     * @param string|FieldSet        $fieldSet  FieldSet object or FieldSet name
     * @param ConfigurationInterface $config    Search Configuration object
     * @param string                 $uriPrefix URL prefix to allow multiple processors per page
     * @param bool                   $cached    Use cached processor (recommended paged results)
     *
     * @return SearchProcessor|CacheSearchProcessor
     */
    public function createProcessor($fieldSet, ConfigurationInterface $config, $uriPrefix = '', $cached = true)
    {
        if (!$fieldSet instanceof FieldSet) {
            $fieldSet = $this->fieldSetRegistry->getFieldSet($fieldSet);
        }

        $processor = new SearchProcessor(
            $this->inputFactory,
            $this->exporterFactory,
            $this->formatter,
            $this->uriEncoder,
            $config,
            $fieldSet,
            $uriPrefix
        );

        if ($cached) {
            $processor = new CacheSearchProcessor($processor, $this->cacheAdapter, $fieldSet, $uriPrefix);
        }

        return $processor;
    }
}
