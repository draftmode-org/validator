<?php
namespace Terrazza\Component\Validator\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Terrazza\Component\Validator\Exception\InvalidObjectSchemaException;
use Terrazza\Component\Validator\ObjectValueValidator;
use Terrazza\Component\Validator\ObjectValueSchema;
use Terrazza\Component\Validator\ObjectValueValidatorInterface;
use Terrazza\Dev\Logger\Logger;

class ObjectValueValidatorSchemasTest extends TestCase {

    private function getValidator($stream=null) : ObjectValueValidatorInterface {
        $logger                         = (new Logger("ObjectValueValidator"))->createLogger($stream);
        return new ObjectValueValidator($logger);
    }

    function testIsValidSchemas() {
        $validator                      = $this->getValidator(false);
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
            true, // passed as array, required object
            true, // passed as object, required object
            true, // passed as array, required array
            true, // passed as object, required array

            false,
            false,
            false, // invalid argument a
            true,
        ],[
            $validator->isValid(["s" => "111-22-4444", "i" => 4], (new ObjectValueSchema("isValidSchemas", "object"))->setChildSchemas($validateSchemaString, $validateSchemaInteger)),
            $validator->isValid((object)["s" => "111-22-4444", "i" => 4], (new ObjectValueSchema("isValidSchemas", "object"))->setChildSchemas($validateSchemaString, $validateSchemaInteger)),
            $validator->isValid(["s" => "111-22-4444", "i" => 4], (new ObjectValueSchema("isValidSchemas", "array"))->setChildSchemas($validateSchemaString, $validateSchemaInteger)),
            $validator->isValid((object)["s" => "111-22-4444", "i" => 4], (new ObjectValueSchema("isValidSchemas", "array"))->setChildSchemas($validateSchemaString, $validateSchemaInteger)),
            $validator->isValid(["s" => "111-22-a444", "i" => 4], (new ObjectValueSchema("isValidSchemas", "object"))->setChildSchemas($validateSchemaString, $validateSchemaInteger)),
            $validator->isValid(["s" => "111-22-4444", "i" => "a"], (new ObjectValueSchema("isValidSchemas", "object"))->setChildSchemas($validateSchemaString, $validateSchemaInteger)),
            $validator->isValid(["a" => "111-22-4444"], (new ObjectValueSchema("isValidSchemas", "object"))->setChildSchemas($validateSchemaString, $validateSchemaInteger)),
            $validator->isValid([], (new ObjectValueSchema("isValidSchemas", "object"))->setChildSchemas($validateSchemaString, $validateSchemaInteger)),
        ]);
    }

    function testInvalidMessage() {
        $validateSchemaInteger          = (new ObjectValueSchema("i", "integer"))
            ->setMinRange(3)
            ->setMaxRange(6)
            ->setMultipleOf(2);
        $validator                      = $this->getValidator(false);
        $this->expectErrorMessage("i invalid: type integer expected, given string");
        $validator->validate("a", $validateSchemaInteger);
    }

    function testNonAssociativeContentFailure() {
        $validateSchemaInteger          = (new ObjectValueSchema("i", "integer"));
        $validator                      = $this->getValidator(false);
        $this->expectException(InvalidArgumentException::class);
        $validator->validate([12], (new ObjectValueSchema("associativeContentFailure", "object"))->setChildSchemas($validateSchemaInteger));
    }

    function testMultipleNoChildFailure() {
        $validator                      = $this->getValidator(false);
        $this->expectException(InvalidObjectSchemaException::class);
        $validator->validate(["a" => 12],
            (new ObjectValueSchema("multipleNoChildFailure", "oneOf")));

    }

    function testRequiredFailure() {
        $validator                      = $this->getValidator(false);
        $validateSchemaStringR          = (new ObjectValueSchema("r", "string"))
            ->setRequired(true);

        $this->assertEquals([
            false
        ],[
            $validator->isValid([], (new ObjectValueSchema("", "object"))->setChildSchemas($validateSchemaStringR)),
        ]);
    }
}