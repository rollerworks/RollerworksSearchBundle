<?php

declare(strict_types=1);

/*
 * This file is part of the RollerworksSearch package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Bundle\SearchBundle\Tests\Functional;

use Doctrine\ORM\Tools\SchemaTool;
use Rollerworks\Component\Search\ApiPlatform\Doctrine\Orm\Extension\SearchExtension;
use Rollerworks\Component\Search\SearchCondition;

/**
 * @internal
 */
final class ApiPlatformTest extends FunctionalTestCase
{
    public static function setUpBeforeClass(): void
    {
        if (! class_exists(SearchExtension::class)) {
            self::markTestSkipped('rollerworks/search-api-platform is not installed.');
        }

        $client = self::newClient(['config' => 'api_platform.yml']);
        $client->getKernel()->boot();

        $em = $client->getContainer()->get('doctrine')->getManager('default');
        $metadatas = $em->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool($em);
        $schemaTool->updateSchema($metadatas, true);

        self::ensureKernelShutdown();
    }

    /**
     * @test
     */
    public function empty_search_code_is_valid(): void
    {
        $client = self::newClient(['config' => 'api_platform.yml']);

        $client->request('GET', '/books.json');

        self::assertEquals('[]', $client->getResponse()->getContent());
    }

    /**
     * @test
     */
    public function with_valid_condition(): void
    {
        $client = self::newClient(['config' => 'api_platform.yml']);
        $client->request(
            'GET',
            '/books.json',
            ['search' => 'title: Symfony;']
        );

        self::assertFalse($client->getResponse()->isRedirection());
        self::assertInstanceOf(SearchCondition::class, $client->getRequest()->attributes->get('_api_search_condition'));
        self::assertEquals('[]', $client->getResponse()->getContent());
    }

    /**
     * @test
     */
    public function with_valid_condition_json(): void
    {
        $client = self::newClient(['config' => 'api_platform.yml']);
        $client->request(
            'GET',
            '/books.json',
            ['search' => '{"fields":{"title":{"single-values":["Symfony;"]}}}']
        );

        self::assertFalse($client->getResponse()->isRedirection());
        self::assertInstanceOf(SearchCondition::class, $client->getRequest()->attributes->get('_api_search_condition'));
        self::assertEquals('[]', $client->getResponse()->getContent());
    }

    /**
     * @test
     */
    public function invalid_condition_has_errors_in_json_format(): void
    {
        $client = self::newClient(['config' => 'api_platform.yml']);
        $client->request(
            'GET',
            '/books.json',
            ['search' => 'id: He;']
        );

        self::assertFalse($client->getResponse()->isRedirection());
        self::assertEquals('/books.json?search=id%3A+He%3B', $client->getRequest()->getRequestUri());
        self::assertNull($client->getRequest()->attributes->get('_api_search_condition'));
        self::assertJsonStringEqualsJsonString(
            '{
                "@context": "/contexts/SearchViolation",
                "@id": "/search_violations",
                "@type": "SearchViolation",
                "description": "[id][0]: This value is not valid.",
                "detail": "[id][0]: This value is not valid.",
                "status": 422,
                "title": "The search condition is invalid",
                "type": "https://tools.ietf.org/html/rfc2616#section-10",
                "violations": [
                    {
                        "message": "This value is not valid.",
                        "propertyPath": "[id][0]"
                    }
                ]
            }',
            $client->getResponse()->getContent(),
        );
    }

    /**
     * @test
     */
    public function invalid_condition_has_errors_in_jsonld_format(): void
    {
        $client = self::newClient(['config' => 'api_platform.yml']);
        $client->request(
            'GET',
            '/books.jsonld',
            ['search' => 'id: He;']
        );

        self::assertFalse($client->getResponse()->isRedirection());
        self::assertEquals('/books.jsonld?search=id%3A+He%3B', $client->getRequest()->getRequestUri());
        self::assertNull($client->getRequest()->attributes->get('_api_search_condition'));
        self::assertJsonStringEqualsJsonString(
            '{
                "@context": "/contexts/SearchViolation",
                "@id": "/search_violations",
                "@type": "SearchViolation",
                "description": "[id][0]: This value is not valid.",
                "detail": "[id][0]: This value is not valid.",
                "status": 422,
                "title": "The search condition is invalid",
                "type": "https://tools.ietf.org/html/rfc2616#section-10",
                "violations": [
                    {
                        "message": "This value is not valid.",
                        "propertyPath": "[id][0]"
                    }
                ]
            }',
            $client->getResponse()->getContent()
        );
    }
}
