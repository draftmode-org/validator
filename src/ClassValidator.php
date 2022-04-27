<?php

namespace Terrazza\Component\Validator;

use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

class ClassValidator implements ClassValidatorInterface {
    CONST validatePattern                           = '/@validate\s\(+([^\s]+)\)/';
    CONST validateIsValueObject                     = '/@validate\/isValueObject/';
    private LoggerInterface $logger;
    private ValueValidatorInterface $valueValidator;

    public function __construct(LoggerInterface $logger) {
        $this->valueValidator                       = new ValueValidator();
        $this->logger                               = $logger;
    }

    /**
     * @param $content
     * @param ValueValidatorSchema[] $schemas
     */
    public function validateSchemas($content, array $schemas) : void {
        $this->valueValidator->validateSchemas($content, $schemas);
    }

    /**
     * @param $content
     * @param ValueValidatorSchema $schema
     */
    public function validateSchema($content, ValueValidatorSchema $schema) : void {
        $this->valueValidator->validateSchema($content, $schema);
    }

    /**
     * @param class-string<T> $className
     * @return ValueValidatorSchema[]
     */
    public function getClassSchema(string $className) : array {
        $arrTypeConvert                             = [
            "int"                                   => "integer"
        ];
        try {
            $properties                             = [];
            $rClass                                 = new ReflectionClass($className);
            foreach ($rClass->getProperties() as $rProperty) {
                $rPropertyName                      = $rProperty->getName();
                $inputSchema                        = (new ValueValidatorSchema($rProperty->getName()));
                if (preg_match(self::validatePattern, $rProperty->getDocComment(), $matches)) {
                    if ($rPropertyType = $rProperty->getType()) {
                        $type                       = strtr($rPropertyType->getName(), $arrTypeConvert);
                        $inputSchema
                            ->setType($type)
                            ->setOptional($rPropertyType->allowsNull())
                        ;
                    }
                    $rInputSchema                   = new ReflectionClass($inputSchema);
                    foreach (explode(",",$matches[1]) as $match) {
                        $matchProperty              = explode("=", $match);
                        list($matchKey,$matchValue) = $matchProperty;
                        $setMethod                  = "set".ucfirst($matchKey);
                        if ($rInputProperty = $rInputSchema->getProperty($matchKey)) {
                            $matchValueType         = gettype($matchValue);
                            if ($rInputPropertyType = $rInputProperty->getType()) {
                                $expectedType       = $rInputPropertyType->getName();
                                if ($matchValueType === "string" && $expectedType === "int") {
                                    if (strval(intval($matchValue)) === $matchValue) {
                                        $matchValue = intval($matchValue);
                                        $matchValueType = gettype($matchValue);
                                    }
                                }
                                $expectedType        = strtr($expectedType, $arrTypeConvert);
                                if ($expectedType === $matchValueType) {
                                    call_user_func([$inputSchema, $setMethod], $matchValue);
                                } else {
                                    throw new RuntimeException("type for $matchKey has to be $expectedType, given ".$matchValueType);
                                }
                            }
                        }
                    }
                    $properties[$rPropertyName]     = $inputSchema;
                } else {
                    if ($rPropertyType = $rProperty->getType()) {
                        $inputSchema
                            ->setOptional($rPropertyType->allowsNull())
                        ;
                        if (!$rPropertyType->isBuiltin()) {
                            $rPropertyTypeName      = $rPropertyType->getName();
                            if (class_exists($rPropertyTypeName)) {
                                $inputSchema        = $this->getClassInputSchema($inputSchema, $rPropertyTypeName);
                                /*$childSchemas       = $this->getClassSchema($rPropertyTypeName);
                                $this->logger->debug("for $rPropertyName we got c:".count($childSchemas));
                                $inputSchema
                                    ->setType("object")
                                    ->setChildSchemas($childSchemas);
                                */
                                $properties[$rPropertyName] = $inputSchema;
                            }
                        }
                    }
                }
            }
            return $properties;
        }
        catch (ReflectionException $exception) {
            throw new RuntimeException("getSchema for class $className failure, ".$exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @param ValueValidatorSchema $inputSchema
     * @param string $className
     * @return ValueValidatorSchema
     * @throws ReflectionException
     */
    private function getClassInputSchema(ValueValidatorSchema $inputSchema, string $className) : ValueValidatorSchema {
        $childSchemas                               = $this->getClassSchema($className);
        $this->logger->debug("count of properties for class $className, ".count($childSchemas));
        if (count($childSchemas) === 1) {
            $rClass                                 = new ReflectionClass($className);
            if (preg_match(self::validateIsValueObject, $rClass->getDocComment())) {
                $this->logger->debug("class has ".self::validateIsValueObject." annotation, return first property");
                return array_shift($childSchemas);
            }
        }
        $inputSchema
            ->setType("object")
            ->setChildSchemas($childSchemas);
        return $inputSchema;
    }
}