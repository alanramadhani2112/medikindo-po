<?php

namespace Tests\Unit\Services;

use App\Services\BCMathCalculatorService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BCMathCalculatorServiceTest extends TestCase
{
    private BCMathCalculatorService $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new BCMathCalculatorService();
    }

    public function test_it_adds_two_values_correctly()
    {
        $result = $this->calculator->add('10.50', '5.25');
        $this->assertEquals('15.75', $result);
    }

    public function test_it_adds_with_proper_precision()
    {
        $result = $this->calculator->add('0.01', '0.01');
        $this->assertEquals('0.02', $result);
    }

    public function test_it_subtracts_two_values_correctly()
    {
        $result = $this->calculator->subtract('10.50', '5.25');
        $this->assertEquals('5.25', $result);
    }

    public function test_it_handles_negative_subtraction_results()
    {
        $result = $this->calculator->subtract('5.00', '10.00');
        $this->assertEquals('-5.00', $result);
    }

    public function test_it_multiplies_two_values_correctly()
    {
        $result = $this->calculator->multiply('10.00', '5.00');
        $this->assertEquals('50.00', $result);
    }

    public function test_it_multiplies_with_decimals()
    {
        $result = $this->calculator->multiply('10.50', '2.00');
        $this->assertEquals('21.00', $result);
    }

    public function test_it_divides_two_values_correctly()
    {
        $result = $this->calculator->divide('10.00', '2.00');
        $this->assertEquals('5.00', $result);
    }

    public function test_it_divides_with_proper_precision()
    {
        $result = $this->calculator->divide('10.00', '3.00');
        $this->assertEquals('3.33', $result);
    }

    public function test_it_throws_exception_on_division_by_zero()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Division by zero is not allowed');
        
        $this->calculator->divide('10.00', '0.00');
    }

    public function test_it_rounds_using_half_up_banker_rounding()
    {
        // 2.005 -> second decimal is 0 (even), round down to 2.00
        $this->assertEquals('2.00', $this->calculator->round('2.005'));
        
        // 3.015 -> second decimal is 1 (odd), round up to 3.02
        $this->assertEquals('3.02', $this->calculator->round('3.015'));
        
        // 2.004 -> third decimal < 5, round down to 2.00
        $this->assertEquals('2.00', $this->calculator->round('2.004'));
        
        // 2.506 -> third decimal > 5, round up to 2.51
        $this->assertEquals('2.51', $this->calculator->round('2.506'));
    }

    public function test_it_compares_two_values()
    {
        $this->assertEquals(0, $this->calculator->compare('10.00', '10.00'));
        $this->assertEquals(1, $this->calculator->compare('10.00', '5.00'));
        $this->assertEquals(-1, $this->calculator->compare('5.00', '10.00'));
    }

    public function test_it_checks_equality()
    {
        $this->assertTrue($this->calculator->equals('10.00', '10.00'));
        $this->assertFalse($this->calculator->equals('10.00', '10.01'));
    }

    public function test_it_calculates_percentage()
    {
        // 10% of 100.00 = 10.00
        $result = $this->calculator->percentage('100.00', '10.00');
        $this->assertEquals('10.00', $result);
        
        // 12.5% of 200.00 = 25.00
        $result = $this->calculator->percentage('200.00', '12.50');
        $this->assertEquals('25.00', $result);
    }

    public function test_it_sums_array_of_values()
    {
        $values = ['10.00', '20.50', '5.25', '3.75'];
        $result = $this->calculator->sum($values);
        $this->assertEquals('39.50', $result);
    }

    public function test_it_formats_values()
    {
        $this->assertEquals('10.50', $this->calculator->format(10.5));
        $this->assertEquals('10.00', $this->calculator->format(10));
        $this->assertEquals('10.50', $this->calculator->format('10.5'));
    }

    public function test_it_calculates_absolute_value()
    {
        $this->assertEquals('10.00', $this->calculator->abs('10.00'));
        $this->assertEquals('10.00', $this->calculator->abs('-10.00'));
        $this->assertEquals('0.00', $this->calculator->abs('0.00'));
    }

    public function test_it_provides_constants()
    {
        $this->assertEquals('0.00', $this->calculator->zero());
        $this->assertEquals('1.00', $this->calculator->one());
        $this->assertEquals('100.00', $this->calculator->hundred());
    }

    public function test_it_throws_exception_for_invalid_input()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->calculator->add('invalid', '10.00');
    }

    public function test_addition_is_commutative()
    {
        // a + b = b + a
        $a = '123.45';
        $b = '678.90';
        
        $result1 = $this->calculator->add($a, $b);
        $result2 = $this->calculator->add($b, $a);
        
        $this->assertEquals($result1, $result2);
    }

    public function test_addition_is_associative()
    {
        // (a + b) + c = a + (b + c)
        $a = '10.50';
        $b = '20.25';
        $c = '30.75';
        
        $result1 = $this->calculator->add($this->calculator->add($a, $b), $c);
        $result2 = $this->calculator->add($a, $this->calculator->add($b, $c));
        
        $this->assertEquals($result1, $result2);
    }

    public function test_subtraction_inverse_property()
    {
        // a - b + b = a
        $a = '100.00';
        $b = '37.50';
        
        $result = $this->calculator->subtract($a, $b);
        $result = $this->calculator->add($result, $b);
        
        $this->assertEquals($a, $result);
    }

    public function test_multiplication_is_commutative()
    {
        // a * b = b * a
        $a = '12.50';
        $b = '8.00';
        
        $result1 = $this->calculator->multiply($a, $b);
        $result2 = $this->calculator->multiply($b, $a);
        
        $this->assertEquals($result1, $result2);
    }

    public function test_multiplication_is_associative()
    {
        // (a * b) * c = a * (b * c)
        $a = '2.00';
        $b = '3.00';
        $c = '4.00';
        
        $result1 = $this->calculator->multiply($this->calculator->multiply($a, $b), $c);
        $result2 = $this->calculator->multiply($a, $this->calculator->multiply($b, $c));
        
        $this->assertEquals($result1, $result2);
    }
}
