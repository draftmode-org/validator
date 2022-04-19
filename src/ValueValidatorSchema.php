<?php

namespace Terrazza\Component\Validator;

class ValueValidatorSchema {
    public string $name;
    //public bool $array=false;
    //public bool $builtIn=false;
    public bool $optional=true;
    public ?string $type=null;
    /**
     * @var ValueValidatorSchema[]|null
     */
    public ?array $childSchemas=null;

    public ?string $patterns=null;
    public ?string $format=null;
    public ?int $minLength=null;
    public ?int $maxLength=null;
    public ?int $minItems=null;
    public ?int $maxItems=null;

    public function __construct (string $name) {
        $this->name = $name;
    }

    /*public function isArray() : bool {
        return $this->array;
    }
    public function setArray(bool $array) : void {
        $this->array = $array;
    }*/

    public function getName() : string {
        return $this->name;
    }
    public function __toString() : string {
        return $this->name;
    }

    /*public function isBuiltIn() : bool {
        return $this->builtIn;
    }
    public function setBuiltIn(bool $builtIn) : void {
        $this->builtIn = $builtIn;
    }*/

    public function isOptional() : bool {
        return $this->optional;
    }
    public function setOptional(bool $optional) : self {
        $this->optional = $optional;
        return $this;
    }

    public function setType(string $type) : self {
        $this->type = $type;
        return $this;
    }
    public function getType() :?string {
        return $this->type;
    }

    /**
     * @param ValueValidatorSchema[]|null $childSchemas
     * @return $this
     */
    public function setChildSchemas(array $childSchemas=null) : self {
        $this->childSchemas                         = $childSchemas;
        return $this;
    }
    public function hasChildSchemas() : bool {
        return $this->childSchemas && count($this->childSchemas);
    }
    /**
     * @return ValueValidatorSchema[]|null
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
}