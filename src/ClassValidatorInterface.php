<?php
namespace Terrazza\Component\Validator;

interface ClassValidatorInterface {
    /**
     * @param class-string<T> $className
     * @return ValueValidatorSchema[]
     */
    public function getClassSchema(string $className) : array;
    /**
     * @param $content
     * @param ValueValidatorSchema[] $schemas
     */
    public function validateSchemas($content, array $schemas) : void;
    /**
     * @param $content
     * @param ValueValidatorSchema $schema
     */
    public function validateSchema($content, ValueValidatorSchema $schema) : void;
}