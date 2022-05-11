<?php
namespace Terrazza\Component\Validator\Tests;

use PHPUnit\Framework\TestCase;
use Terrazza\Component\Validator\ObjectValueValidator;
use Terrazza\Component\Validator\ObjectValueSchema;

class ObjectValueValidatorSchemaTest extends TestCase {

    function testIsValidString() {
        $validator              = new ObjectValueValidator;
        $validateSchemaString   = (new ObjectValueSchema("name", "string"))
            ->setMinLength(10)
            ->setMaxLength(12)
            ->setPatterns('^\d{3}-\d{2}-\d{4}$')
        ;
        $this->assertEquals([
            true,
            false,
            false,
            false
        ],[
            $validator->isValid("111-22-4444", $validateSchemaString),
            $validator->isValid("len4", $validateSchemaString), // min:false, max:true, pattern: true
            $validator->isValid("len12len12len12", $validateSchemaString),// min:true, max:false, pattern: true
            $validator->isValid("abc-11-2222", $validateSchemaString), // min:true, max:true, pattern: false (not numeric)
        ]);
    }

    function testIsValidInteger() {
        $validator              = new ObjectValueValidator;
        $validateSchemaInteger   = (new ObjectValueSchema("name", "integer"))
            ->setMinRange(3)
            ->setMaxRange(6)
            ->setMultipleOf(2);

        $this->assertEquals([
            true,
            false,
            false,
            false,
        ],[
            $validator->isValid(4, $validateSchemaInteger),
            $validator->isValid(2, $validateSchemaInteger), // min:false, max: true, multipleOf: true
            $validator->isValid(8, $validateSchemaInteger), // min:true, max: false, multipleOf: true
            $validator->isValid(3, $validateSchemaInteger), // min:true, max: true, multipleOf: false
        ]);
    }

    function testIsValidNumber() {
        $validator              = new ObjectValueValidator;
        $validateSchemaNumber = (new ObjectValueSchema("name", "number"));
        $this->assertEquals([
            false,
            true,
            true,
            true
        ],[
            $validator->isValid("12a", $validateSchemaNumber),
            $validator->isValid("12", $validateSchemaNumber),
            $validator->isValid(12, $validateSchemaNumber),
            $validator->isValid(12.12, $validateSchemaNumber),
        ]);
    }

    function testIsValidBoolean() {
        $validator              = new ObjectValueValidator;

        $validateSchemaBoolean = (new ObjectValueSchema("name", "boolean"));

        $this->assertEquals([
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            true,
            false
        ],[
            $validator->isValid(true, $validateSchemaBoolean),
            $validator->isValid(false, $validateSchemaBoolean),
            $validator->isValid(1, $validateSchemaBoolean),
            $validator->isValid(0, $validateSchemaBoolean),
            $validator->isValid("true", $validateSchemaBoolean),
            $validator->isValid("false", $validateSchemaBoolean),
            $validator->isValid("yes", $validateSchemaBoolean),
            $validator->isValid("no", $validateSchemaBoolean),
            $validator->isValid("nxo", $validateSchemaBoolean),
        ]);
    }

    function testIsValidArray() {
        $validator              = new ObjectValueValidator;

        $validateSchemaArray   = (new ObjectValueSchema("name", "array"))
            ->setMinItems(2)
            ->setMaxItems(3);

        $this->assertEquals([
            true,
            false,
            false,
            false,
        ],[
            $validator->isValid([1,2], $validateSchemaArray),
            $validator->isValid("12", $validateSchemaArray),
            $validator->isValid([1], $validateSchemaArray),
            $validator->isValid([1,2,3,4], $validateSchemaArray),
        ]);
    }

    function testIsValidNullable() {
        $validator              = new ObjectValueValidator;
        $validateSchemaNullableFalse   = (new ObjectValueSchema("name", "string"));
        $validateSchemaNullableTrue   = (new ObjectValueSchema("name", "string"))
            ->setNullable(true);

        $this->assertEquals([
            false,
            true,
        ],[
            $validator->isValid(null, $validateSchemaNullableFalse),
            $validator->isValid(null, $validateSchemaNullableTrue),
        ]);
    }

    function testIsValidFormatEmail() {
        $validator              = new ObjectValueValidator;
        $validateSchemaEmail = (new ObjectValueSchema("name", "string"))
            ->setFormat("email");

        $this->assertEquals([
            true,
            false,
        ],[
            $validator->isValid("technic@terrazza.io", $validateSchemaEmail),
            $validator->isValid("technic@terrazza", $validateSchemaEmail),
        ]);
    }

    function testIsValidFormatDate() {
        $validator              = new ObjectValueValidator;
        $validateSchemaDate = (new ObjectValueSchema("name", "string"))
            ->setFormat("date");

        $this->assertEquals([
            true,
            false,
            false
        ],[
            $validator->isValid("2021-01-12", $validateSchemaDate),
            $validator->isValid("2021-13-12", $validateSchemaDate),
            $validator->isValid("2021-01-32", $validateSchemaDate),
        ]);
    }
}