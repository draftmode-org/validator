<?php
namespace Terrazza\Component\Validator\Tests;

use PHPUnit\Framework\TestCase;
use Terrazza\Component\Validator\ObjectValueValidator;
use Terrazza\Component\Validator\ObjectValueSchema;

class ObjectValueValidatorSchemasTest extends TestCase {

    function testIsValidSchemas() {
        $validator                      = new ObjectValueValidator;
        $validateSchemaString           = (new ObjectValueSchema("s"))
            ->setType("string")
            ->setMinLength(10)
            ->setMaxLength(12)
            ->setPatterns('^\d{3}-\d{2}-\d{4}$')
        ;
        $validateSchemaInteger          = (new ObjectValueSchema("i"))
            ->setType("integer")
            ->setMinRange(3)
            ->setMaxRange(6)
            ->setMultipleOf(2);
        $validateSchemaStringR          = (new ObjectValueSchema("r"))
            ->setType("string")
            ->setOptional(false);

        $this->assertEquals([
            true,
            false,
            false,
            false,

            true,
            false
        ],[
            $validator->isValidSchemas(["s" => "111-22-4444", "i" => 4], [$validateSchemaString, $validateSchemaInteger]),
            $validator->isValidSchemas(["s" => "111-22-a444", "i" => 4], [$validateSchemaString, $validateSchemaInteger]),
            $validator->isValidSchemas(["s" => "111-22-4444", "i" => "a"], [$validateSchemaString, $validateSchemaInteger]),
            $validator->isValidSchemas(["a" => "111-22-4444"], [$validateSchemaString, $validateSchemaInteger]),

            $validator->isValidSchemas([], [$validateSchemaString, $validateSchemaInteger]),
            $validator->isValidSchemas([], [$validateSchemaStringR]),
        ]);
    }
}