<?php
namespace Terrazza\Component\Validator\Tests;

use PHPUnit\Framework\TestCase;
use Terrazza\Component\Validator\ValueValidator;
use Terrazza\Component\Validator\ValueValidatorSchema;

class ValueValidatorTest extends TestCase {
    function testValidSchema() {
        $validator              = new ValueValidator;
        $validateSchemaString   = (new ValueValidatorSchema("name"))
            ->setType("string")
            ->setMinLength(13)
        ;
        $validator->validateSchema("string", $validateSchemaString);
        $this->assertTrue(true);
    }
}