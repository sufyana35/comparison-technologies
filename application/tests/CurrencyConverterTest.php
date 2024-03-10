<?php 

declare(strict_types=1);

use App\Helper\CurrencyConverter;
use PHPUnit\Framework\TestCase;

final class OutputTest extends TestCase
{
    public function testConvertPencesToPounds_RoundUp(): void
    {
        $amount = 123; // Pence amount
        $expected = 1.23; // Expected pound amount after conversion

        $convertedAmount = CurrencyConverter::convertPencesToPounds($amount);

        $this->assertEquals($expected, $convertedAmount, "Pence to pounds conversion with rounding up failed");
    }

    public function testConvertPencesToPounds_NoRounding(): void
    {
        $amount = 200; // Pence amount
        $expected = 2.00; // Expected pound amount after conversion

        $convertedAmount = CurrencyConverter::convertPencesToPounds($amount);

        $this->assertEquals($expected, $convertedAmount, "Pence to pounds conversion without rounding failed");
    }

    public function testConvertPencesToPounds_ZeroAmount(): void
    {
        $amount = 0; // Pence amount
        $expected = 0.00; // Expected pound amount after conversion

        $convertedAmount = CurrencyConverter::convertPencesToPounds($amount);

        $this->assertEquals($expected, $convertedAmount, "Pence to pounds conversion with zero amount failed");
    }

    public function testConvertPoundsToPence_RoundUp(): void
    {
        $amount = 1.23; // Pound amount
        $expected = 123; // Expected pence amount after conversion

        $convertedAmount = CurrencyConverter::convertPoundsToPence($amount);

        $this->assertEquals($expected, $convertedAmount, "Pounds to pence conversion with rounding up failed");
    }

    public function testConvertPoundsToPence_NoRounding(): void
    {
        $amount = 2.00; // Pound amount
        $expected = 200; // Expected pence amount after conversion

        $convertedAmount = CurrencyConverter::convertPoundsToPence($amount);

  
        $this->assertEquals($expected, $convertedAmount, "Pounds to pence conversion without rounding failed");
    }

    public function testConvertPoundsToPence_ZeroAmount(): void
    {
        $amount = 0.00; // Pound amount
        $expected = 0; // Expected pence amount after conversion

        $convertedAmount = CurrencyConverter::convertPoundsToPence($amount);

        $this->assertEquals($expected, $convertedAmount, "Pounds to pence conversion with zero amount failed");
    }

    public function testIsAmountInPounds_PoundsFormat(): void
    {
        $amount = "£1.23"; // Amount in pounds format

        $isInPounds = CurrencyConverter::isAmountInPounds($amount);

        $this->assertTrue($isInPounds, "Amount in pounds format not recognized");
    }

    public function testIsAmountInPounds_PenceFormatWithDecimal(): void
    {
        $amount = "123.45p"; // Amount in pence format with decimal

        $isInPounds = CurrencyConverter::isAmountInPounds($amount);

        $this->assertTrue($isInPounds, "Amount in pence format with decimal not recognized");
    }

    public function testIsAmountInPounds_PenceFormatWithoutDecimal(): void
    {
        $amount = "123p"; // Amount in pence format without decimal

        $isInPounds = CurrencyConverter::isAmountInPounds($amount);

        $this->assertFalse($isInPounds, "Amount in pence format without decimal incorrectly recognized as pounds");
    }

    public function testIsAmountInPounds_DecimalOnly(): void
    {
        $amount = ".50"; // Decimal only

        $isInPounds = CurrencyConverter::isAmountInPounds($amount);

        $this->assertTrue($isInPounds, "Decimal only format not recognized");
    }

    public function testIsAmountInPounds_InvalidFormat(): void
        
    {
        $amount = "invalid"; // Invalid format

        $isInPounds = CurrencyConverter::isAmountInPounds($amount);

        $this->assertFalse($isInPounds, "Invalid format recognized as pounds");
    }

    public function testIsAmountInPounds_EmptyValue(): void
    {
        $amount = ""; // Empty value

        $isInPounds = CurrencyConverter::isAmountInPounds($amount);

        $this->assertFalse($isInPounds, "Empty value recognized as pounds");
    }

    public function testFormatCurrency_PoundsFormat(): void
    {
        $amount = "£1.23"; // Amount in pounds format
        $isAmountInPounds = true;
        $expected = "1.23";

        $formattedAmount = CurrencyConverter::formatCurrency($amount, $isAmountInPounds);

        $this->assertEquals($expected, $formattedAmount, "Pounds format not stripped correctly");
    }

    public function testFormatCurrency_PenceFormatWithDecimal(): void
    {
        $amount = "123.45p"; // Amount in pence format with decimal
        $isAmountInPounds = true;
        $expected = "123.45";

        $formattedAmount = CurrencyConverter::formatCurrency($amount, $isAmountInPounds);

        $this->assertEquals($expected, $formattedAmount, "Pence format with decimal not stripped correctly");
    }

    public function testFormatCurrency_PenceFormatWithoutDecimal(): void
    {
        $amount = "123p"; // Amount in pence format without decimal
        $isAmountInPounds = true;
        $expected = "123";

        $formattedAmount = CurrencyConverter::formatCurrency($amount, $isAmountInPounds);

        $this->assertEquals($expected, $formattedAmount, "Pence format without decimal not stripped correctly");
    }

    public function testFormatCurrency_DecimalOnly(): void
    {
        $amount = ".50"; // Decimal only
        $isAmountInPounds = true;
        $expected = ".50";

        $formattedAmount = CurrencyConverter::formatCurrency($amount, $isAmountInPounds);

        $this->assertEquals($expected, $formattedAmount, "Decimal only format not preserved");
    }

    public function testFormatCurrency_EmptyValue(): void
    {
        $amount = ""; // Empty value
        $isAmountInPounds = true;
        $expected = ""; // Function should not modify empty value

        $formattedAmount = CurrencyConverter::formatCurrency($amount, $isAmountInPounds);

        $this->assertEquals($expected, $formattedAmount, "Empty value modified");
    }

    public function testFormatCurrency_PenceFormat_NotPounds(): void
    {
        $amount = "123p"; // Amount in pence format without decimal
        $isAmountInPounds = false;
        $expected = "123";

        $formattedAmount = CurrencyConverter::formatCurrency($amount, $isAmountInPounds);

        $this->assertEquals($expected, $formattedAmount, "Pence format not stripped for non-pound amount");
    }

    public function testFormatCurrency_OtherCurrencySymbol(): void
    {
        $amount = "$1.23"; // Amount with different currency symbol
        $isAmountInPounds = true;
        $expected = "1.23"; // Should only remove currency symbol used for pounds

        $formattedAmount = CurrencyConverter::formatCurrency($amount, $isAmountInPounds);

        $this->assertEquals($expected, $formattedAmount, "Other currency symbol not preserved");
    }
}