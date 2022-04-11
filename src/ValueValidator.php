<?php
namespace Terrazza\Component\Validator;
use DateTime;
use InvalidArgumentException;
use RuntimeException;

class ValueValidator implements ValueValidatorInterface {

    public function validateContent($content, ValueValidatorSchema $contentSchema) : void {
        $this->validateLength($content, $contentSchema->getMinLength(), $contentSchema->getMaxLength());
        $this->validateItems($content, $contentSchema->getMinItems(), $contentSchema->getMaxItems());
        $this->validateFormat($content, $contentSchema->getFormat());
        $this->validatePatterns($content, $contentSchema->getPatterns());
    }

    /**
     * @param $content
     * @param string|null $expectedType
     */
    public function validateContentType($content, ?string $expectedType) : void {
        if (!$expectedType) {
            throw new RuntimeException("no type to be validated given");
        }
        $inputType                                  = gettype($content);
        if ($inputType === $expectedType) return;
        throw new InvalidArgumentException("type $expectedType expected, given $inputType");
    }

    /**
     * @param $content
     * @param string|null $pattern
     */
    private function validatePatterns($content, ?string $pattern) : void {
        if (!$pattern) return;
        if (!is_scalar($content)) return;
        if (!preg_match("#$pattern#", $content)) {
            throw new InvalidArgumentException("pattern $pattern does not match, given $content");
        }
    }

    /**
     * @param $content
     * @param string|null $format
     */
    private function validateFormat($content, ?string $format): void {
        if (!is_scalar($content)) return;
        switch ($format) {
            case "date":
                $dFormat                            = "Y-m-d";
                $cDate                              = DateTime::createFromFormat($dFormat, $content);
                if (!$cDate || $cDate->format($dFormat) !== $content) {
                    throw new InvalidArgumentException("valid date expected, given $content");
                }
        }
    }

    /**
     * @param $content
     * @param int|null $minItems
     * @param int|null $maxItems
     */
    private function validateItems($content, ?int $minItems, ?int $maxItems) : void {
        if (($minItems || $maxItems) && !is_array($content)) {
            throw new InvalidArgumentException("type array expected, given ".gettype($content));
        }
        if ($minItems && count($content) < $minItems) {
            throw new InvalidArgumentException("min items $minItems expected, given items ".count($content));
        }
        if ($maxItems && count($content) > $maxItems) {
            throw new InvalidArgumentException("max items $maxItems expected, given items ".count($content));
        }
    }

    /**
     * @param $content
     * @param int|null $minLength
     * @param int|null $maxLength
     */
    private function validateLength($content, ?int $minLength, ?int $maxLength) : void {
        if (!is_scalar($content)) return;
        if ($minLength && strlen($content) < $minLength) {
            throw new InvalidArgumentException("min length $minLength expected, given length ".strlen($content));
        }
        if ($maxLength && strlen($content) > $maxLength) {
            throw new InvalidArgumentException("max length $maxLength expected, given length ".strlen($content));
        }
    }

}