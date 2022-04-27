<?php
namespace Terrazza\Component\Validator;
interface ValueValidatorInterface {
    /**
     * @param $content
     * @param ValueValidatorSchema $contentSchema
     * @param string|null $parentPropertyName
     */
    public function validateSchema($content, ValueValidatorSchema $contentSchema, ?string $parentPropertyName=null) : void;

    /**
     * @param $content
     * @param array|ValueValidatorSchema[] $contentSchema
     * @param string|null $parentPropertyName
     * @return void
     */
    public function validateSchemas($content, array $contentSchema, ?string $parentPropertyName=null) : void;
}