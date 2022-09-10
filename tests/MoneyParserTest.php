<?php

declare(strict_types=1);

namespace Test;

use App\Project\Entities\Money;
use App\Project\Parsers\MoneyParser;
use Exception;
use PHPUnit\Framework\TestCase;

final class MoneyParserTest extends TestCase
{
    private $moneyParser;

    public function setUp(): void
    {
        $this->moneyParser = new MoneyParser();
    }

    public function testCanParseMoney(): void
    {
        $money = $this->moneyParser->parse('81,99€');

        $expectedMoney = new Money();
        $expectedMoney->value = '81,99';

        $this->assertEquals($expectedMoney, $money);
    }

    public function testCanParseUnformatedMoney(): void
    {
        $money = $this->moneyParser->parse(' 81,99 €');

        $expectedMoney = new Money();
        $expectedMoney->value = '81,99';

        $this->assertEquals($expectedMoney, $money);
    }

    public function testMoneyParserThrowsException1(): void
    {
        $this->expectException(Exception::class);
        $money = $this->moneyParser->parse(' 81,99');
    }

    public function testMoneyParserThrowsException2(): void
    {
        $this->expectException(Exception::class);
        $money = $this->moneyParser->parse(' 81.99 €');
    }

    // public function testMoneyParserThrowsException3(): void
    // {
    //     $this->expectException(Exception::class);
    //     $money = $this->moneyParser->parse(' 81, 99 €');
    // }
}
