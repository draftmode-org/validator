<?php
namespace Terrazza\Component\Validator\Tests;
use PHPUnit\Framework\TestCase;
use Terrazza\Component\Validator\ObjectValueSchema;

class ObjectValueSchemaTest extends TestCase {
    function testSetters() {
        $schema = (new ObjectValueSchema($name = "price"))
            ->setType($type = "string")
            ->setOptional($optional = false)
            ->setPatterns($patterns = "1")
            ->setMinLength($minLength = 1)
            ->setMaxLength($maxLength = 2)
            ->setMinItems($minItems = 1)
            ->setMaxItems($maxItems = 2)
            ->setFormat($format = "Date")
            ->setMinRange($minRange = 1)
            ->setMaxRange($maxRange = 2)
        ;
        $schema->setChildSchemas([$schema]);
        $this->assertEquals([
            $name,
            $type,
            $optional,
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
            $schema->isOptional(),
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
        $schema = new ObjectValueSchema("price");
        $this->assertEquals([
            null,
            true,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            false
        ],[
            $schema->getType(),
            $schema->isOptional(),
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

}