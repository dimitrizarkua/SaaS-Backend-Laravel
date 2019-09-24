<?php

namespace Tests\API;

use HSkrasek\OpenAPI\Converter;
use Illuminate\Foundation\Testing\TestResponse as BaseResponse;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationError;
use Opis\JsonSchema\Validator;
use PHPUnit\Framework\Assert as PHPUnit;
use Tests\OpenApiLoader;

/**
 * Class TestResponse
 *
 * @package Tests\API
 */
class TestResponse extends BaseResponse
{
    /**
     * Assert that the given string is contained within the response.
     *
     * @param  string $value
     *
     * @return $this
     */
    public function assertSee($value)
    {
        PHPUnit::assertContains((string)$value, $this->getContent());

        return $this;
    }

    /**
     * Assert that the response JSON has the expected count of items at the data object of response.
     *
     * @param  int $count
     *
     * @return $this
     */
    public function assertJsonDataCount(int $count)
    {
        $key = 'data';

        return $this->assertJsonCount($count, $key);
    }

    /**
     * Assert that the JSON response has the expected 'data' as a key.
     *
     * @return \Tests\API\TestResponse
     */
    public function assertSeeData()
    {
        return $this->assertSee('data');
    }

    /**
     * Asserts that the JSON response has the expected 'data' as a key.
     *
     * @return \Tests\API\TestResponse
     */
    public function assertSeeAdditional()
    {
        $this->assertSee('additional');

        return $this;
    }

    /**
     * @return $this
     */
    public function assertSeePagination()
    {
        PHPUnit::assertContains((string)'pagination', $this->getContent());

        return $this;
    }

    /**
     * @param null|string $keyName
     *
     * @return mixed
     */
    public function getData(?string $keyName = null)
    {
        $this->assertSeeData();

        $data = json_decode($this->getContent(), true)['data'];
        if (null !== $keyName) {
            PHPUnit::assertArrayHasKey($keyName, $data);

            return $data[$keyName];
        }

        return $data;
    }

    /**
     * Returns additional data.
     *
     * @param null|string $keyName
     *
     * @return mixed
     */
    public function getAdditionalData(?string $keyName = null)
    {
        $this->assertSeeAdditional();

        $data = json_decode($this->getContent(), true)['additional'];
        if (null !== $keyName) {
            PHPUnit::assertArrayHasKey($keyName, $data);

            return $data[$keyName];
        }

        return $data;
    }

    /**
     * Assert that response is NotAllowed.
     *
     * @param string|null $expectedMessage Optional expected message.
     */
    public function assertNotAllowed(string $expectedMessage = null)
    {
        $this->assertStatus(405);

        if (null !== $expectedMessage) {
            $response = json_decode($this->getContent(), true);
            PHPUnit::assertArrayHasKey('error_message', $response);
            $responseMessage = $response['error_message'];
            PHPUnit::assertEquals($expectedMessage, $responseMessage);
        }
    }

    /**
     * Assert that response is FailedDependency.
     *
     * @param string|null $expectedMessage Optional expected message.
     */
    public function assertFailedDependency(string $expectedMessage = null)
    {
        $this->assertStatus(424);

        if (null !== $expectedMessage) {
            $response = json_decode($this->getContent(), true);
            PHPUnit::assertArrayHasKey('error_message', $response);
            $responseMessage = $response['error_message'];
            PHPUnit::assertEquals($expectedMessage, $responseMessage);
        }
    }

    /**
     * Assert that response has valid format in compare with given resource.
     *
     * @param string $className       Name of class that contains AO\Schema that should be checked.
     * @param bool   $isResponseClass Show whether the given class name is a some response class.
     *                                By default we expecting a some resource class.
     *
     * @return $this
     */
    public function assertValidSchema(string $className, bool $isResponseClass = false)
    {
        $docs = OpenApiLoader::load($className);

        //Convert OpenApi to JsonSchema
        $converter = new Converter();
        $docs      = $converter->convert(json_decode(json_encode($docs)));

        $data = json_decode($this->getContent(), true);

        if (false === $isResponseClass) {
            $data = $data['data'];
        }
        $data = json_decode(json_encode($data));

        $schemaData            = json_decode(json_encode($docs), true);
        $schemaData['$schema'] = "http://json-schema.org/draft-07/schema#";

        $schema = Schema::fromJsonString(json_encode($schemaData));

        $validator = new Validator();
        $result    = $validator->schemaValidation($data, $schema);

        if (false === $result->isValid()) {
            $message = '';
            foreach ($result->getErrors() as $error) {
                $message .= $this->formatValidationError($error);
            }

            PHPUnit::fail($message);
        }

        return $this;
    }

    /**
     * Formatting validation error message.
     *
     * @param \Opis\JsonSchema\ValidationError $error
     *
     * @return string
     */
    private function formatValidationError(ValidationError $error): string
    {
        if (!empty($error->subErrors())) {
            $error = $error->subErrors()[0];

            return $this->formatValidationError($error);
        }

        $message = 'Response format is invalid' . PHP_EOL;
        $message .= sprintf('Error: %s', $error->keyword()) . PHP_EOL;
        $message .= sprintf('Field: %s', implode('.', $error->dataPointer())) . PHP_EOL;
        $message .= 'Expected schema: ' . PHP_EOL;
        $message .= json_encode($error->keywordArgs(), JSON_PRETTY_PRINT) . PHP_EOL;
        $message .= 'Actual data: ' . json_encode($error->data(), JSON_PRETTY_PRINT) . PHP_EOL;

        return $message;
    }
}
