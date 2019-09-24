<?php

namespace Tests\Feature;

use Tests\TestCase;
use L5Swagger\Generator;

/**
 * Class DocumentationTest
 *
 * @package Tests\Feature
 */
class DocumentationTest extends TestCase
{
    /**
     * Test that OpenAPI documentation generated successfully.
     *
     * @doesNotPerformAssertions
     */
    public function testOpenAPIDocumentationGeneratedSuccessfully()
    {
        Generator::generateDocs();
    }
}
