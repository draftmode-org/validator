<?php
namespace Terrazza\Component\Validator\Tests;

use PHPUnit\Framework\TestCase;
use Terrazza\Component\Validator\ObjectValueValidator;
use Terrazza\Component\Validator\ObjectValueSchema;
use Terrazza\Component\Validator\ObjectValueValidatorInterface;
use Terrazza\Dev\Logger\Logger;

class ObjectValueValidatorSchemaTest extends TestCase {

    private function getValidator($stream=null) : ObjectValueValidatorInterface {
        $logger                         = (new Logger("ObjectValueValidator"))->createLogger($stream);
        return new ObjectValueValidator($logger);
    }

    function testIsValidString() {
        $validator                      = $this->getValidator(false);
        $validateSchemaString           = (new ObjectValueSchema("validString", "string"))
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
        $validator                      = $this->getValidator(false);
        $validateSchemaInteger          = (new ObjectValueSchema("validInteger", "integer"))
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
        $validator                      = $this->getValidator(false);
        $validateSchemaNumber           = (new ObjectValueSchema("validNumber", "number"));
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
        $validator                      = $this->getValidator(false);
        $validateSchemaBoolean          = (new ObjectValueSchema("validBoolean", "boolean"));

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
        $validator                      = $this->getValidator(false);
        $validateSchemaArray            = (new ObjectValueSchema("validArray", "array"))
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
        $validator                      = $this->getValidator(false);
        $validateSchemaNullableFalse    = (new ObjectValueSchema("validNullableFalse", "string"));
        $validateSchemaNullableTrue     = (new ObjectValueSchema("validNullableTrue", "string"))
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
        $validator                      = $this->getValidator(false);
        $validateSchemaEmail            = (new ObjectValueSchema("validFormatEmail", "string"))
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
        $validator                      = $this->getValidator(false);
        $validateSchemaDate             = (new ObjectValueSchema("validFormatDate", "string"))
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

    function testEnum() {
        $validator                      = $this->getValidator(false);
        $validateSchemaEnum             = (new ObjectValueSchema("enum", "integer"))
            ->setEnum([1,2]);
        $this->assertEquals([
            true,
            true,
            false
        ],[
            $validator->isValid(1, $validateSchemaEnum),
            $validator->isValid(2, $validateSchemaEnum),
            $validator->isValid(4, $validateSchemaEnum),
        ]);
    }

    function testOneOf() {
        $validator                      = $this->getValidator(false);
        $validateSchemaOneOf            = (new ObjectValueSchema("oneOf", "oneOf"))
            ->setChildSchemas(new ObjectValueSchema("int", "integer"), new ObjectValueSchema("string", "string"));

        $this->assertEquals([
            true,
            true,
            false
        ],[
            $validator->isValid("22", $validateSchemaOneOf),
            $validator->isValid(22, $validateSchemaOneOf),
            $validator->isValid(true, $validateSchemaOneOf),
        ]);
    }
}