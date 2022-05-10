<?php
namespace Terrazza\Component\Validator;

interface ClassValidatorInterface {
    /**
     * @param class-string<T> $className
     * @return ObjectValueSchema[]
     */
    public function getClassSchema(string $className) : array;
    /**
     * @param $content
     * @param ObjectValueSchema[] $schemas
     */
    public function validateSchemas($content, array $schemas) : void;
    /**
     * @param $content
     * @param ObjectValueSchema $schema
     */
    public function validateSchema($content, ObjectValueSchema $schema) : void;
}