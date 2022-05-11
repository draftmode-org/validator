<?php
namespace Terrazza\Component\Validator;
use DateTime;
use Terrazza\Component\Validator\Exception\InvalidObjectSchemaException;
use Terrazza\Component\Validator\Exception\InvalidObjectValueArgumentException;
use Throwable;

class ObjectValueValidator implements ObjectValueValidatorInterface {
    CONST boolean_true = ["true", "1", "yes", 1];
    CONST boolean_false = ["false", "0", "no", 0];
    private ?string $parentPropertyName=null;

    /**
     * @param $content
     * @param ObjectValueSchema ...$contentSchema
     * @return bool
     */
    public function isValid($content, ObjectValueSchema ...$contentSchema) : bool {
        try {
            $this->validate($content, ...$contentSchema);
            return true;
        } catch (Throwable $exception) {
            return false;
        }
    }

    /**
     * @param $content
     * @param ObjectValueSchema ...$contentSchema
     * @throws InvalidObjectValueArgumentException
     */
    public function validate($content, ObjectValueSchema ...$contentSchema) : void {
        $useSchema                                  = null;
        $useSchemas                                 = null;
        if (count($contentSchema) === 1) {
            $useSchema                              = array_shift($contentSchema);
            if ($useSchema->hasChildSchemas() && !$useSchema->isMultipleType()) {
                $useSchemas                         = $useSchema->getChildSchemas();
                $useSchema                          = null;
            }
        } else {
            $useSchemas                             = $contentSchema;
        }
        if ($useSchema) {
            $schema                                 = clone $useSchema;
            $this->validateSchema($content, $schema);
        } elseif ($useSchemas) {
            $this->validateSchemas($content, $useSchemas);
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
                $schema->setType($inputType);
                return $content;
            }
            if ($inputType === "string" && strval(intval($content)) === $content) {
                $schema->setType("integer");
                return intval($content);
            }
        }
        if ($expectedType === "boolean") {
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
     * @param $content
     * @param ObjectValueSchema $contentSchema
     * @throws InvalidObjectValueArgumentException
     */
    private function validateSchema($content, ObjectValueSchema $contentSchema) : void {
        try {
            if ($contentSchema->isMultipleType()) {
                $contentSchema                      = $this->getSchemaFromMultiple($content, $contentSchema);
            }
            $content                                = $this->getEncodeValue($content, $contentSchema);
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
        } catch (InvalidObjectValueArgumentException $exception) {
            $argumentName                       = $contentSchema->getName();
            $fullPropertyName                   = $this->parentPropertyName ? $this->parentPropertyName.".".$argumentName : $argumentName;
            throw new InvalidObjectValueArgumentException("argument $fullPropertyName invalid: ".$exception->getMessage());
        }
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
            throw new InvalidObjectValueArgumentException("does not match any childSchema");
        } else {
            $argumentName                       = $contentSchema->getName();
            $fullPropertyName                   = $this->parentPropertyName ? $this->parentPropertyName.".".$argumentName : $argumentName;
            throw new InvalidObjectSchemaException("argument $fullPropertyName invalid: no childSchema given");
        }
    }

    /**
     * @param $content
     * @param array|ObjectValueSchema[] $contentSchema
     * @return void
     */
    private function validateSchemas($content, array $contentSchema) : void {
        $content                                    = (array)$content;
        foreach ($contentSchema as $inputSchema) {
            $propertyName                           = $inputSchema->getName();
            $fullPropertyName                       = $this->parentPropertyName ? $this->parentPropertyName.".".$propertyName : $propertyName;
            $inputExists                            = array_key_exists($propertyName, $content);
            if ($inputSchema->isRequired() && !$inputExists) {
                throw new InvalidObjectValueArgumentException("argument $fullPropertyName required, missing");
            }
            if ($inputExists) {
                $inputValue                         = $content[$propertyName];
                $this->parentPropertyName           = $fullPropertyName;
                $this->validateSchema($inputValue, $inputSchema);
                unset($content[$propertyName]);
            }
        }
        $unmappedKeys                               = [];
        foreach ($content as $cKey => $cValue) {
            $unmappedKeys[]                         = $this->parentPropertyName ? $this->parentPropertyName.".".$cKey : $cKey;
        }
        if (count($unmappedKeys)) {
            $arguments                              = "argument".(count($unmappedKeys) > 1 ? "s" : "");
            throw new InvalidObjectValueArgumentException("$arguments (".join(", ", $unmappedKeys).") not allowed");
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