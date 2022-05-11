<?php
namespace Terrazza\Component\Validator\Tests;

use PHPUnit\Framework\TestCase;
use Terrazza\Component\Validator\ObjectValueValidator;
use Terrazza\Component\Validator\ObjectValueSchema;

class ObjectValueValidatorSchemasTest extends TestCase {

    function testIsValidSchemas() {
        $validator                      = new ObjectValueValidator;
        $validateSchemaString           = (new ObjectValueSchema("s", "string"))
            ->setMinLength(10)
            ->setMaxLength(12)
            ->setPatterns('^\d{3}-\d{2}-\d{4}$')
        ;
        $validateSchemaInteger          = (new ObjectValueSchema("i", "integer"))
            ->setMinRange(3)
            ->setMaxRange(6)
            ->setMultipleOf(2);

        $this->assertEquals([
            true, // pass multiple schemas as child
            true, // pass multiple schemas as variadic
            false,
            false,
            false, // invalid argument a
            true,
        ],[
            $validator->isValid(["s" => "111-22-4444", "i" => 4], (new ObjectValueSchema("", "object"))->setChildSchemas($validateSchemaString, $validateSchemaInteger)),
            $validator->isValid(["s" => "111-22-4444", "i" => 4], $validateSchemaString, $validateSchemaInteger),
            $validator->isValid(["s" => "111-22-a444", "i" => 4], (new ObjectValueSchema("", "object"))->setChildSchemas($validateSchemaString, $validateSchemaInteger)),
            $validator->isValid(["s" => "111-22-4444", "i" => "a"], (new ObjectValueSchema("", "object"))->setChildSchemas($validateSchemaString, $validateSchemaInteger)),
            $validator->isValid(["a" => "111-22-4444"], (new ObjectValueSchema("", "object"))->setChildSchemas($validateSchemaString, $validateSchemaInteger)),
            $validator->isValid([], (new ObjectValueSchema("", "object"))->setChildSchemas($validateSchemaString, $validateSchemaInteger)),
        ]);
    }

    function testRequiredFailure() {
        $validator                      = new ObjectValueValidator;
        $validateSchemaStringR          = (new ObjectValueSchema("r", "string"))
            ->setRequired(true);

        $this->assertEquals([
            false
        ],[
            $validator->isValid([], (new ObjectValueSchema("", "object"))->setChildSchemas($validateSchemaStringR)),
        ]);
    }
}