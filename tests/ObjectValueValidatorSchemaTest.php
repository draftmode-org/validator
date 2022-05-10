<?php
namespace Terrazza\Component\Validator\Tests;

use PHPUnit\Framework\TestCase;
use Terrazza\Component\Validator\ObjectValueValidator;
use Terrazza\Component\Validator\ObjectValueSchema;

class ObjectValueValidatorSchemaTest extends TestCase {

    function testIsValidSchema() {
        $validator              = new ObjectValueValidator;
        $validateSchemaString   = (new ObjectValueSchema("name"))
            ->setType("string")
            ->setMinLength(10)
            ->setMaxLength(12)
            ->setPatterns('^\d{3}-\d{2}-\d{4}$')
        ;
        $validateSchemaNullableFalse   = (new ObjectValueSchema("name"))
            ->setType("string")
        ;
        $validateSchemaNullableTrue   = (new ObjectValueSchema("name"))
            ->setType("string")
            ->setNullable(true)
        ;
        $validateSchemaInteger   = (new ObjectValueSchema("name"))
            ->setType("integer")
            ->setMinRange(3)
            ->setMaxRange(6)
            ->setMultipleOf(2);

        $validateSchemaArray   = (new ObjectValueSchema("name"))
            ->setType("array")
            ->setMinItems(2)
            ->setMaxItems(3);

        $validateSchemaBoolean = (new ObjectValueSchema("name"))
            ->setType("boolean");

        $validateSchemaEmail = (new ObjectValueSchema("name"))
            ->setType("string")
            ->setFormat("email");

        $validateSchemaNumber = (new ObjectValueSchema("name"))
            ->setType("number");

        $validateSchemaDate = (new ObjectValueSchema("name"))
            ->setType("string")
            ->setFormat("date");

        $this->assertEquals([
            false,

            true,
            false,
            false,
            false,

            false,
            true,

            true,
            false,
            false,
            false,

            true,
            false,
            false,
            false,

            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            false,

            false,
            true,
            true,
            true,

            true,
            false,

            true,
            false,
            false
        ],[
            $validator->isValidSchema("111", new ObjectValueSchema("name")), // missing type

            $validator->isValidSchema("111-22-4444", $validateSchemaString),
            $validator->isValidSchema("len4", $validateSchemaString), // min:false, max:true, pattern: true
            $validator->isValidSchema("len12len12len12", $validateSchemaString),// min:true, max:false, pattern: true
            $validator->isValidSchema("abc-11-2222", $validateSchemaString), // min:true, max:true, pattern: false (not numeric)

            $validator->isValidSchema(null, $validateSchemaNullableFalse),
            $validator->isValidSchema(null, $validateSchemaNullableTrue),

            $validator->isValidSchema(4, $validateSchemaInteger),
            $validator->isValidSchema(2, $validateSchemaInteger), // min:false, max: true, multipleOf: true
            $validator->isValidSchema(8, $validateSchemaInteger), // min:true, max: false, multipleOf: true
            $validator->isValidSchema(3, $validateSchemaInteger), // min:true, max: true, multipleOf: false

            $validator->isValidSchema([1,2], $validateSchemaArray),
            $validator->isValidSchema("12", $validateSchemaArray),
            $validator->isValidSchema([1], $validateSchemaArray),
            $validator->isValidSchema([1,2,3,4], $validateSchemaArray),

            $validator->isValidSchema(true, $validateSchemaBoolean),
            $validator->isValidSchema(false, $validateSchemaBoolean),
            $validator->isValidSchema(1, $validateSchemaBoolean),
            $validator->isValidSchema(0, $validateSchemaBoolean),
            $validator->isValidSchema("true", $validateSchemaBoolean),
            $validator->isValidSchema("false", $validateSchemaBoolean),
            $validator->isValidSchema("yes", $validateSchemaBoolean),
            $validator->isValidSchema("no", $validateSchemaBoolean),
            $validator->isValidSchema("nxo", $validateSchemaBoolean),

            $validator->isValidSchema("12a", $validateSchemaNumber),
            $validator->isValidSchema("12", $validateSchemaNumber),
            $validator->isValidSchema(12, $validateSchemaNumber),
            $validator->isValidSchema(12.12, $validateSchemaNumber),

            $validator->isValidSchema("technic@terrazza.io", $validateSchemaEmail),
            $validator->isValidSchema("technic@terrazza", $validateSchemaEmail),

            $validator->isValidSchema("2021-01-12", $validateSchemaDate),
            $validator->isValidSchema("2021-13-12", $validateSchemaDate),
            $validator->isValidSchema("2021-01-32", $validateSchemaDate),
        ]);
    }
}