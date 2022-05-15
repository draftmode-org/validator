<?php
namespace Terrazza\Component\Validator;
use DateTime;
use Psr\Log\LoggerInterface;
use Terrazza\Component\Validator\Exception\InvalidObjectSchemaException;
use Terrazza\Component\Validator\Exception\InvalidObjectValueArgumentException;
use Throwable;

class ObjectValueValidator implements ObjectValueValidatorInterface {
    CONST objectExceptionNoChildSchemasTypeMultiple = "no childSchemas given for multiple types";
    CONST valueExceptionNoMatchMultiple             = "does not match any childSchema";
    CONST valueExceptionNonAssociative              = "expected associative array, given non associative";
    private LoggerInterface $logger;
    CONST boolean_true                              = ["true", "1", "yes", 1];
    CONST boolean_false                             = ["false", "0", "no", 0];
    private array $propertyName                     = [];

    public function __construct(LoggerInterface $logger) {
        $this->logger                               = $logger;
    }

    /**
     * @param $content
     * @param ObjectValueSchema $contentSchema
     * @return bool
     */
    public function isValid($content, ObjectValueSchema $contentSchema) : bool {
        $this->resetPropertyName();
        try {
            $this->validate($content, clone $contentSchema);
            return true;
        } catch (Throwable $exception) {
            $this->logger->debug("isValid exception: ".$exception->getMessage());
            return false;
        }
    }

    /**
     * @param $content
     * @param ObjectValueSchema $contentSchema
     * @throws InvalidObjectValueArgumentException
     */
    public function validate($content, ObjectValueSchema $contentSchema) : void {
        try {
            $this->pushPropertyName($contentSchema->getName());
            if ($this->handleMultipleContent($content, $contentSchema)) {
                $this->validateMultipleContent($content, $contentSchema);
            } else {
                $this->validateSingleContent($content, $contentSchema);
            }
            $this->popPropertyName();
        } catch (InvalidObjectValueArgumentException $exception) {
            $propertyName                           = $this->getPropertyName();
            $this->resetPropertyName();
            throw new InvalidObjectValueArgumentException("$propertyName invalid: ".$exception->getMessage());
        }
    }

    /**
     * @param $content
     * @param ObjectValueSchema $contentSchema
     */
    private function validateSingleContent($content, ObjectValueSchema $contentSchema) {
        $this->logger->debug("validateSingleValue, ".$contentSchema->getName().", type: ".$contentSchema->getType(), ["content" => $content]);
        if ($contentSchema->isMultipleType()) {
            $contentSchema                  = $this->getSchemaFromMultiple($content, $contentSchema);
        }
        $content                            = $this->getEncodeValue($content, $contentSchema);
        $this->validateContentType($content, $contentSchema->isNullable(), $contentSchema->getType());
        if (is_array($content)) {
            $this->validateArray($content, $contentSchema->getMinItems(), $contentSchema->getMaxItems());
        }
        $this->validateString($content, $contentSchema->getMinLength(), $contentSchema->getMaxLength(), $contentSchema->getPatterns());
        $this->validateNumber($content, $contentSchema->getMinRange(), $contentSchema->getMaxRange(), $contentSchema->getMultipleOf());
        if ($contentSchema->getFormat()) {
            $this->validateFormat($content, $contentSchema->getFormat());
        }
        if ($contentSchema->hasEnum()) {
            $this->validateEnum($content, $contentSchema->getEnum());
        }
    }

    /**
     * @param $content
     * @param ObjectValueSchema $contentSchema
     * @return bool
     */
    private function handleMultipleContent($content, ObjectValueSchema $contentSchema): bool {
        if ($contentSchema->hasChildSchemas()) {
            if (in_array($contentSchema->getType(), ["object", "array"])) {
                if (is_array($content)) {
                    return true;
                } elseif (is_object($content)) {
                    return true;
                } else {
                    throw new InvalidObjectValueArgumentException("expected ".$contentSchema->getType().", given ".gettype($content));
                }
            }
        }
        return false;
    }

    /**
     * @param $content
     * @param ObjectValueSchema $contentSchema
     * @return void
     */
    private function validateMultipleContent($content, ObjectValueSchema $contentSchema) : void {
        $this->logger->debug("validateMultipleContent, schema.type: ".$contentSchema->getType().", content.type ".gettype($content));
        $validateChildSchemas                       = false;
        if (is_array($content)) {
            $isAssociative                          = $this->isAssociative($content);
            if ($contentSchema->getType() === "array") {
                if ($isAssociative) {
                    $this->logger->debug("given associative array, switch to object, continue with validateSchemas");
                    $contentSchema->setType("object");
                    $validateChildSchemas           = true;
                } else {
                    $childSchemas                   = $contentSchema->getChildSchemas();
                    if (count($childSchemas) === 1) {
                        $this->logger->debug("given non associative array, use first childSchema");
                        $childSchema                = array_shift($childSchemas);
                        foreach ($content as $iChildCounter => $childValue) {
                            $this->pushPropertyName("[$iChildCounter]");
                            $this->validateSingleContent($childValue, $childSchema);
                            $this->popPropertyName();
                        }
                        return;
                    } else {
                        throw new InvalidObjectValueArgumentException("multiple childSchemas for non associative arrays are not possible");
                    }
                }
            } elseif ($contentSchema->getType() === "object") {
                if ($isAssociative) {
                    $this->logger->debug("given associative array, continue with validateSchemas");
                    $validateChildSchemas           = true;
                } else {
                    throw new InvalidObjectValueArgumentException("expected type object, given non associative array");
                }
            } else {
                throw new InvalidObjectValueArgumentException("expected type object, given non associative array");
            }
        }
        elseif (is_object($content)) {
            if ($contentSchema->getType() === "object") {
                $this->logger->debug("expected object, given object, validate content for childSchemas");
                $validateChildSchemas               = true;
            }
        }
        if ($validateChildSchemas) {
            $this->validateSchemas($content, ...array_values($contentSchema->getChildSchemas()));
        }
    }


    /**
     * @param $content
     * @param ObjectValueSchema ...$contentSchemas
     * @return void
     */
    private function validateSchemas($content, ObjectValueSchema ...$contentSchemas) : void {
        $this->logger->debug("validateSchemas", ["content" => $content]);
        $content                                    = (array)$content;
        foreach ($contentSchemas as $contentSchema) {
            $inputSchema                            = clone $contentSchema;
            $propertyName                           = $inputSchema->getName();
            $this->logger->debug("validate property $propertyName, type: ".$inputSchema->getType());
            $inputExists                            = array_key_exists($propertyName, $content);
            $this->logger->debug("property $propertyName ".($inputExists ? "exists" : "does not exists")." in content");
            if ($inputSchema->isRequired() && !$inputExists) {
                $this->pushPropertyName($propertyName);
                throw new InvalidObjectValueArgumentException($this->getPropertyName()." required, missing");
            }
            if ($inputExists) {
                $inputValue                         = $content[$propertyName];
                $this->validate($inputValue, $inputSchema);
                unset($content[$propertyName]);
            }
        }
        $unmappedKeys                               = [];
        foreach ($content as $cKey => $cValue) {
            $this->pushPropertyName($cKey);
            $unmappedKeys[]                         = $this->getPropertyName();
            $this->popPropertyName();
        }
        if (count($unmappedKeys)) {
            throw new InvalidObjectValueArgumentException("(".join(", ", $unmappedKeys).") not allowed");
        }
    }

    /**
     * @param $content
     * @param ObjectValueSchema $schema
     * @return mixed
     */
    public function getEncodeValue($content, ObjectValueSchema $schema) {
        if (is_null($content)) {
            return null;
        }
        $expectedType                               = $schema->getType();
        $inputType                                  = gettype($content);
        if ($inputType === $expectedType) {
            return $content;
        }
        if ($expectedType === "number") {
            if ($inputType === "integer" || $inputType === "double") {
                $this->logger->debug("expected number, type of input $inputType, setType=$inputType");
                $schema->setType($inputType);
                return $content;
            }
            if ($inputType === "string" && strval(intval($content)) === $content) {
                $this->logger->debug("expected number, type of input $inputType, but numeric content, setType=integer");
                $schema->setType("integer");
                return intval($content);
            }
        }
        elseif ($expectedType === "boolean") {
            if ($inputType === "string") {
                if (in_array(strtolower($content), self::boolean_true, true)) {
                    return true;
                }
                elseif (in_array(strtolower($content), self::boolean_false, true)) {
                    return false;
                }
            }
            elseif ($inputType === "integer") {
                if (in_array($content, self::boolean_true, true)) {
                    return true;
                }
                elseif (in_array($content, self::boolean_false, true)) {
                    return false;
                }
            }
        }
        return $content;
    }

    /**
     * @param array $input
     * @return bool
     */
    private function isAssociative(array $input) : bool {
        if ([] === $input) {
            return true;
        }
        $cElements 									= count($input);
        for ($iElement = 0; $iElement < $cElements; $iElement++) {
            if(!array_key_exists($iElement, $input)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $propertyName
     */
    private function pushPropertyName(string $propertyName) : void {
        $this->propertyName[]                       = $propertyName;
    }

    private function popPropertyName() : void {
        array_pop($this->propertyName);
    }

    private function resetPropertyName() : void {
        $this->propertyName                         = [];
    }

    /**
     * @return string
     */
    private function getPropertyName() : string {
        return join(".", $this->propertyName);
    }

    private function getSchemaFromMultiple($content, ObjectValueSchema $contentSchema) : ObjectValueSchema {
        if ($contentSchema->hasChildSchemas()) {
            foreach ($contentSchema->getChildSchemas() as $schema) {
                if ($this->isValid($content, $schema)) {
                    switch ($contentSchema->getType()) {
                        case "oneOf":
                            return $schema;
                    }
                }
            }
            throw new InvalidObjectValueArgumentException(self::valueExceptionNoMatchMultiple);
        } else {
            throw new InvalidObjectSchemaException(self::objectExceptionNoChildSchemasTypeMultiple);
        }
    }

    /**
     * @param $content
     * @param bool $nullable
     * @param string $expectedType
     * @throws InvalidObjectValueArgumentException
     */
    private function validateContentType($content, bool $nullable, string $expectedType) : void {
        if (is_null($content)) {
            if ($nullable) {
                return;
            } else {
                throw new InvalidObjectValueArgumentException("value expected, given null");
            }
        }
        $inputType                                  = gettype($content);
        if ($inputType === $expectedType) return;
        throw new InvalidObjectValueArgumentException("type $expectedType expected, given $inputType");
    }

    /**
     * @param $content
     * @param int|null $minLength
     * @param int|null $maxLength
     * @param string|null $pattern
     */
    private function validateString($content, ?int $minLength, ?int $maxLength, ?string $pattern) : void {
        if (!is_scalar($content)) return;
        if ($minLength && strlen($content) < $minLength) {
            throw new InvalidObjectValueArgumentException("min length $minLength expected, given length ".strlen($content));
        }
        if ($maxLength && strlen($content) > $maxLength) {
            throw new InvalidObjectValueArgumentException("max length $maxLength expected, given length ".strlen($content));
        }
        if ($pattern) {
            if (!preg_match("#$pattern#", $content)) {
                throw new InvalidObjectValueArgumentException("pattern $pattern does not match, given $content");
            }
        }
    }

    /**
     * @param $content
     * @param string $format
     */
    private function validateFormat($content, string $format): void {
        if (!is_scalar($content)) return;
        switch ($format) {
            case "date":
                $dFormat                            = "Y-m-d";
                $cDate                              = DateTime::createFromFormat($dFormat, $content);
                if (!$cDate || $cDate->format($dFormat) !== $content) {
                    throw new InvalidObjectValueArgumentException("valid date expected, given $content");
                }
                break;
            case "email":
                if (filter_var($content, FILTER_VALIDATE_EMAIL)) {
                    return;
                }
                throw new InvalidObjectValueArgumentException("valid email expected, given $content");
        }
    }

    /**
     * @param $content
     * @param array $enum
     */
    private function validateEnum($content, array $enum) : void {
        if (!is_scalar($content)) return;
        foreach ($enum as $enumValue) {
            if ($content === $enumValue) return;
        }
        throw new InvalidObjectValueArgumentException("possible values are ".join(", ", $enum).", given $content");
    }

    /**
     * @param array $content
     * @param int|null $minItems
     * @param int|null $maxItems
     */
    private function validateArray(array $content, ?int $minItems, ?int $maxItems) : void {
        if ($minItems && count($content) < $minItems) {
            throw new InvalidObjectValueArgumentException("min items $minItems expected, given items ".count($content));
        }
        if ($maxItems && count($content) > $maxItems) {
            throw new InvalidObjectValueArgumentException("max items $maxItems expected, given items ".count($content));
        }
    }

    /**
     * @param $content
     * @param float|null $minRange
     * @param float|null $maxRange
     * @param int|null $multipleOf
     */
    private function validateNumber($content, ?float $minRange, ?float $maxRange, ?int $multipleOf) : void {
        if (!is_numeric($content)) return;
        if ($minRange && $content < $minRange) {
            throw new InvalidObjectValueArgumentException("min range $minRange expected, given ".$content);
        }
        if ($maxRange && $content > $maxRange) {
            throw new InvalidObjectValueArgumentException("max range $maxRange expected, given ".$content);
        }
        if ($multipleOf && $content % $multipleOf !== 0) {
            throw new InvalidObjectValueArgumentException("multipleOf $multipleOf expected, given ".$content);
        }
    }

}