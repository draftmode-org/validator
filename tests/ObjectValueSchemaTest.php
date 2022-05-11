<?php
namespace Terrazza\Component\Validator\Tests;
use PHPUnit\Framework\TestCase;
use Terrazza\Component\Validator\Exception\InvalidObjectSchemaException;
use Terrazza\Component\Validator\ObjectValueSchema;

class ObjectValueSchemaTest extends TestCase {
    function testSetters() {
        $schema = (new ObjectValueSchema($name = "price", $type = "string"))
            ->setRequired($required = true)
            ->setPatterns($patterns = "1")
            ->setMinLength($minLength = 1)
            ->setMaxLength($maxLength = 2)
            ->setMinItems($minItems = 1)
            ->setMaxItems($maxItems = 2)
            ->setFormat($format = "Date")
            ->setMinRange($minRange = 1)
            ->setMaxRange($maxRange = 2)
        ;
        $schema->setChildSchemas($schema);
        $this->assertEquals([
            $name,
            $type,
            $required,
            $patterns,
            $minLength,
            $maxLength,
            $minRange,
            $maxRange,
            $minItems,
            $maxItems,
            $format,
            [$schema],
            true
        ],[
            $schema->getName(),
            $schema->getType(),
            $schema->isRequired(),
            $schema->getPatterns(),
            $schema->getMinLength(),
            $schema->getMaxLength(),
            $schema->getMinRange(),
            $schema->getMaxRange(),
            $schema->getMinItems(),
            $schema->getMaxItems(),
            $schema->getFormat(),
            $schema->getChildSchemas(),
            $schema->hasChildSchemas()
        ]);
    }

    function testDefaults() {
        $schema = new ObjectValueSchema("price", "string");
        $this->assertEquals([
            false,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            false
        ],[
            $schema->isRequired(),
            $schema->getPatterns(),
            $schema->getMinLength(),
            $schema->getMaxLength(),
            $schema->getMinItems(),
            $schema->getMaxItems(),
            $schema->getFormat(),
            $schema->getChildSchemas(),
            $schema->hasChildSchemas()
        ]);
    }

    function testInvalidType() {
        $this->expectException(InvalidObjectSchemaException::class);
        new ObjectValueSchema("price", "unknown");
    }

    function testSetInvalidType() {
        $schema = new ObjectValueSchema("price", "string");
        $this->expectException(InvalidObjectSchemaException::class);
        $schema->setType("unknown");
    }
}