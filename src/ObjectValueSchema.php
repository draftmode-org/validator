<?php

namespace Terrazza\Component\Validator;

use Terrazza\Component\Validator\Exception\InvalidObjectSchemaException;

class ObjectValueSchema {
    CONST allowed_types = ["number", "integer", "double", "array", "boolean", "string", "object"];
    private string $name;
    private string $type;
    private bool $required=false;
    private bool $nullable=false;
    private ?string $patterns=null;
    private ?string $format=null;
    private ?int $minLength=null;
    private ?int $maxLength=null;
    private ?int $minItems=null;
    private ?int $maxItems=null;
    private ?float $minRange=null;
    private ?float $maxRange=null;
    private ?float $multipleOf=null;
    /**
     * @var ObjectValueSchema[]|null
     */
    public ?array $childSchemas=null;


    public function __construct (string $name, string $type) {
        $this->name = $name;
        $this->validateType($type);
        $this->type = $type;
    }

    public function getName() : string {
        return $this->name;
    }

    public function isRequired() : bool {
        return $this->required;
    }
    public function setRequired(bool $required) : self {
        $this->required = $required;
        return $this;
    }

    public function isNullable() : bool {
        return $this->nullable;
    }
    public function setNullable(bool $nullable) : self {
        $this->nullable = $nullable;
        return $this;
    }

    private function validateType(string $type) : void {
        if (!in_array($type, self::allowed_types)) {
            throw new InvalidObjectSchemaException("allowed types: ".join(",", self::allowed_types).", given $type");
        }
    }
    /**
     * @param string $type
     * @return $this
     * @throws InvalidObjectSchemaException
     */
    public function setType(string $type) : self {
        $this->validateType($type);
        $this->type = $type;
        return $this;
    }
    public function getType() :?string {
        return $this->type;
    }

    /**
     * @param ObjectValueSchema ...$childSchemas
     * @return $this
     */
    public function setChildSchemas(ObjectValueSchema ...$childSchemas) : self {
        $this->childSchemas                         = $childSchemas;
        return $this;
    }
    public function hasChildSchemas() : bool {
        return $this->childSchemas && count($this->childSchemas);
    }
    /**
     * @return ObjectValueSchema[]|null
     */
    public function getChildSchemas() : ?array {
        return $this->childSchemas;
    }

    public function setPatterns(?string $patterns) : self {
        $this->patterns = $patterns;
        return $this;
    }
    public function getPatterns(): ?string {
        return $this->patterns;
    }

    public function setFormat(?string $format) : self {
        $this->format = $format;
        return $this;
    }
    public function getFormat(): ?string {
        return $this->format;
    }

    public function setMinLength(?int $minLength): self {
        $this->minLength = $minLength;
        return $this;
    }
    public function getMinLength(): ?int {
        return $this->minLength;
    }

    public function setMaxLength(?int $maxLength): self {
        $this->maxLength = $maxLength;
        return $this;
    }
    public function getMaxLength(): ?int {
        return $this->maxLength;
    }

    public function setMinItems(?int $minItems): self {
        $this->minItems = $minItems;
        return $this;
    }
    public function getMinItems(): ?int {
        return $this->minItems;
    }

    public function setMaxItems(?int $maxItems): self {
        $this->maxItems = $maxItems;
        return $this;
    }
    public function getMaxItems(): ?int {
        return $this->maxItems;
    }

    public function setMinRange(?float $minRange): self {
        $this->minRange = $minRange;
        return $this;
    }
    public function getMinRange(): ?float {
        return $this->minRange;
    }

    public function setMaxRange(?float $maxRange): self {
        $this->maxRange = $maxRange;
        return $this;
    }
    public function getMaxRange(): ?float {
        return $this->maxRange;
    }

    public function setMultipleOf(?float $multipleOf): self {
        $this->multipleOf = $multipleOf;
        return $this;
    }
    public function getMultipleOf(): ?float {
        return $this->multipleOf;
    }
}