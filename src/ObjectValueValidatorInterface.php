<?php
namespace Terrazza\Component\Validator;
use Terrazza\Component\Validator\Exception\InvalidObjectValueArgumentException;

interface ObjectValueValidatorInterface {
    /**
     * @param $content
     * @param ObjectValueSchema $contentSchema
     * @return bool
     */
    public function isValid($content, ObjectValueSchema $contentSchema) : bool;

    /**
     * @param $content
     * @param ObjectValueSchema $contentSchema
     * @throws InvalidObjectValueArgumentException
     */
    public function validate($content, ObjectValueSchema $contentSchema) : void;

    /**
     * @param $content
     * @param ObjectValueSchema $schema
     * @return mixed
     */
    public function getEncodeValue($content, ObjectValueSchema $schema);
}