<?php

namespace App\Helpers;

/**
 * Class Decimal
 *
 * @package App\Helpers
 */
class Decimal
{
    /**
     * Add two arbitrary precision numbers.
     *
     * @param float $leftOperand
     * @param float $rightOperand
     *
     * @return float
     */
    public static function add(float $leftOperand, float $rightOperand): float
    {
        return (float)bcadd((string)$leftOperand, (string)$rightOperand, 2);
    }

    /**
     * Divide two arbitrary precision numbers.
     *
     * @param float $dividend
     * @param float $divisor
     *
     * @return float
     */
    public static function div(float $dividend, float $divisor): float
    {
        return (float)bcdiv((string)$dividend, (string)$divisor, 2);
    }

    /**
     * Multiply two arbitrary precision numbers.
     *
     * @param float $leftOperand
     * @param float $rightOperand
     *
     * @return float
     */
    public static function mul(float $leftOperand, float $rightOperand): float
    {
        return (float)bcmul((string)$leftOperand, (string)$rightOperand, 2);
    }

    /**
     * Subtract one arbitrary precision number from another.
     *
     * @param float $leftOperand
     * @param float $rightOperand
     *
     * @return float
     */
    public static function sub(float $leftOperand, float $rightOperand): float
    {
        return (float)bcsub((string)$leftOperand, (string)$rightOperand, 2);
    }

    /**
     * Checks whether is given float value equals to zero.
     *
     * @param float $value
     *
     * @return bool
     */
    public static function isZero(float $value): bool
    {
        return bccomp(0, $value, 2) === 0;
    }

    /**
     * Checks whether given float values are equals.
     *
     * @param float $leftValue
     * @param float $rightValue
     *
     * @return bool
     */
    public static function areEquals(float $leftValue, float $rightValue): bool
    {
        return bccomp($leftValue, $rightValue, 2) === 0;
    }

    /**
     * Checks whether given left value less than right value.
     *
     * @param float $leftValue
     * @param float $rightValue
     *
     * @return bool
     */
    public static function lt(float $leftValue, float $rightValue): bool
    {
        return bccomp($leftValue, $rightValue, 2) < 0;
    }

    /**
     * Checks whether given left value greater than right value.
     *
     * @param float $leftValue
     * @param float $rightValue
     *
     * @return bool
     */
    public static function gt(float $leftValue, float $rightValue): bool
    {
        return bccomp($leftValue, $rightValue, 2) > 0;
    }

    /**
     * Checks whether given float value less or equals than right value.
     *
     * @param float $leftValue
     * @param float $rightValue
     *
     * @return bool
     */
    public static function lte(float $leftValue, float $rightValue): bool
    {
        return bccomp($leftValue, $rightValue, 2) <= 0;
    }

    /**
     * Checks whether given left value greater or equals than right value.
     *
     * @param float $leftValue
     * @param float $rightValue
     *
     * @return bool
     */
    public static function gte(float $leftValue, float $rightValue): bool
    {
        return bccomp($leftValue, $rightValue, 2) >= 0;
    }
}
