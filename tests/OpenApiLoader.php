<?php

namespace Tests;

use OpenApi\Analysis;
use OpenApi\StaticAnalyser;

/**
 * Class OpenApiLoader
 *
 * @package Tests
 */
class OpenApiLoader
{
    /**
     * @var array
     */
    private $docs = [];

    private $excludeNamespaces = ['App\Rules'];

    private static $inlineSchemas = [];

    /**
     * Loads docs by class.
     *
     * @param string $className
     *
     * @return array
     */
    public static function load(string $className): array
    {
        $instance = new static();
        $instance->process($className);

        return $instance->toArray();
    }

    /**
     * @param string $className
     *
     * @throws \ReflectionException
     */
    public function process(string $className)
    {
        $docs       = $this->getDocs($className);
        $this->docs = $this->resolveDependencies($docs);
    }

    /**
     * @param array $docs
     *
     * @return array
     */
    private function resolveDependencies(array $docs): array
    {
        foreach ($docs as $key => $value) {
            if (is_array($value)) {
                $docs[$key] = $this->resolveDependencies($value);
            }
            if ($key === '$ref') {
                $dependency = preg_replace('/#\/components\/schemas\//', '', $value);
                $class      = $this->findClass($dependency);
                if (null === $class) {
                    if (array_key_exists($dependency, static::$inlineSchemas)) {
                        $docs = static::$inlineSchemas[$dependency];
                    }

                    continue;
                }
                $docs = static::load($class);
            }
        }

        return $docs;
    }

    /**
     * @param string $className
     *
     * @return string|null
     */
    private function findClass(string $className): ?string
    {
        $dirToFind = base_path() . '/app';
        $directory = new \RecursiveDirectoryIterator($dirToFind);
        $files     = new \RecursiveIteratorIterator($directory);
        foreach ($files as $file) {
            /** @var \SplFileInfo $file */
            if ($file->getFilename() === $className . '.php') {
                $namespace = str_replace($dirToFind, 'App', $file->getPath());
                $namespace = preg_replace('/\//', '\\', $namespace);

                if (in_array($namespace, $this->excludeNamespaces)) {
                    continue;
                }

                return $namespace . '\\' . $className;
            }
        }

        return null;
    }

    /**
     * @param array $docs
     *
     * @return array
     */
    public function getDependencies(array $docs): array
    {
        if (!array_key_exists('components', $docs) || !array_key_exists('schemas', $docs['components'])) {
            return [];
        }
        $schemas      = $docs['components']['schemas'];
        $dependencies = [[]];
        foreach ($schemas as $schema) {
            $dependencies[] = $this->getSchemaDependencies($schema);
        }

        return array_merge(...$dependencies);
    }

    /**
     * @param array $schema
     *
     * @return array
     */
    private function getSchemaDependencies(array $schema): array
    {
        $dependencies = [[]];
        foreach ($schema as $key => $value) {
            if ($key === '$ref') {
                $dependency     = preg_replace('/#\/components\/schemas\//', '', $value);
                $dependencies[] = [$dependency];
            }
            if (is_array($value)) {
                $dependencies[] = $this->getSchemaDependencies($schema[$key]);
            }
        }

        return array_merge(...$dependencies);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->docs;
    }

    /**
     * @param $classname
     *
     * @throws \ReflectionException
     *
     * @return array
     */
    private function getDocs($classname): array
    {
        $reflection = new \ReflectionClass($classname);
        $fileName   = $reflection->getFileName();

        $analyser = new StaticAnalyser();
        $analysis = new Analysis();

        $analysis->addAnalysis($analyser->fromFile($fileName));//\OpenApi\scan($fileName);
        $processors = Analysis::processors();
        $analysis->process($processors);

        $raw = json_decode($analysis->openapi->toJson(), true);

        if (!array_key_exists('components', $raw) || !array_key_exists('schemas', $raw['components'])) {
            return [];
        }

        preg_match('/.*\\\\(.*)$/', $classname, $matches);
        if (!isset($matches[1])) {
            return [];
        }

        $recourseName = $matches[1];

        foreach ($raw['components']['schemas'] as $schemaName => $inlineSchema) {
            if ($schemaName === $recourseName) {
                continue;
            }

            static::$inlineSchemas[$schemaName] = $inlineSchema;
        }

        return $raw['components']['schemas'][$recourseName];
    }
}
