<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Parsers;

use RobinTheHood\TextProjectManager\Helpers\StringHelper;
use RobinTheHood\TextProjectManager\Project\Entities\MoneyRange;
use RobinTheHood\TextProjectManager\Project\Interfaces\MoneyParserInterface;
use RobinTheHood\TextProjectManager\Project\Interfaces\MoneyRangeParserInterface;
use Exception;

class MoneyRangeParser implements MoneyRangeParserInterface
{
    /**
     * @var MoneyParserInterface
     */
    private $moneyParser;

    public function __construct(MoneyParserInterface $moneyParser)
    {
        $this->moneyParser = $moneyParser;
    }

    public function parse(string $string): MoneyRange
    {
        $parts = StringHelper::getTrimmedLineParts($string, '-');

        if (count($parts) !== 2) {
            throw new Exception("This is not a valid MoneyRange");
        }

        $startMoney = $this->moneyParser->parse($parts[0]);
        $endMoney = $this->moneyParser->parse($parts[1]);

        $moneyRange = new MoneyRange();
        $moneyRange->startMoney = $startMoney;
        $moneyRange->endMoney = $endMoney;

        return $moneyRange;
    }
}
