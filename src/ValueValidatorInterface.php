<?php
namespace Terrazza\Component\Validator;
interface ValueValidatorInterface {
    public function validateContent($content, ValueValidatorSchema $contentSchema) : void;
    public function validateContentType($content, ?string $expectedType) : void;
}