<?php
namespace Terrazza\Component\Validator;
interface ObjectValueValidatorInterface {
    /**
     * @param $content
     * @param ObjectValueSchema $contentSchema
     * @param string|null $parentPropertyName
     * @return bool
     */
    public function isValidSchema($content, ObjectValueSchema $contentSchema, ?string $parentPropertyName=null) : bool;
    /**
     * @param $content
     * @param ObjectValueSchema $contentSchema
     * @param string|null $parentPropertyName
     */
    public function validateSchema($content, ObjectValueSchema $contentSchema, ?string $parentPropertyName=null) : void;

    /**
     * @param $content
     * @param array $contentSchema
     * @param string|null $parentPropertyName
     * @return bool
     */
    public function isValidSchemas($content, array $contentSchema, ?string $parentPropertyName=null) : bool;

    /**
     * @param $content
     * @param array|ObjectValueSchema[] $contentSchema
     * @param string|null $parentPropertyName
     * @return void
     */
    public function validateSchemas($content, array $contentSchema, ?string $parentPropertyName=null) : void;
}