<?php

declare(strict_types=1);

namespace App\Project\Parsers;

use App\Helpers\StringHelper;
use App\Project\Entities\Money;
use App\Project\Entities\Report;
use App\Project\Interfaces\AmountParserInterface;
use App\Project\Interfaces\MoneyParserInterface;
use App\Project\Entities\Amount;

class ReportParser
{
    /**
     * @var AmountParserInterface
     */
    private $amountParser;

    /**
     * @var MoneyParserInterface
     */
    private $moneyParser;

    public function __construct(AmountParserInterface $amountParser, MoneyParserInterface $moneyParser)
    {
        $this->amountParser = $amountParser;
        $this->moneyParser = $moneyParser;
    }

    public function parse(string $string): ?Report
    {
        if (!$this->isValidReportLineStart($string)) {
            return null;
        }

        $string = StringHelper::skipLetters($string, 2);
        $stringParts = StringHelper::getTrimmedLineParts($string, ';');

        $report = new Report();
        $report->type = $this->parseType($string);
        $report->date = $this->parseDate($stringParts[0] ?? '');
        $report->amount = $this->parseAmount($stringParts[1] ?? '');
        $report->description = $this->parseDescription($stringParts[2] ?? '');
        $report->externalPrice = $this->parsePrice($stringParts[3] ?? '');
        $report->internalPrice = $this->parsePrice($stringParts[4] ?? '');

        return $report;
    }

    private function parseType($string): int
    {
        $string = trim($string);
        $typeString = mb_substr($string, 0, 2, 'utf-8');

        if ($typeString == '++') {
            return Report::TYPE_BILLABLE;
        }

        return Report::TYPE_UNBILLABLE;
    }

    private function parseDate(string $string): string
    {
        $string = trim($string);
        return $string;
    }

    private function parseAmount(string $string): Amount
    {
        $string = trim($string);
        return $this->amountParser->parse($string);
    }

    private function parseDescription(string $string): string
    {
        $string = trim($string);
        return $string;
    }

    private function parsePrice(string $string): ?Money
    {
        $string = trim($string);
        if ($string) {
            return $this->moneyParser->parse($string);
        }
        return null;
    }

    /**
     * Kontrolliert ob es sich um eine um eine Zeile mit einem validen Zeichen
     * Anfang für einen Task handelt. Ein Task fängt mit einem - an und darf danach
     * nicht direkt einen weiteren - haben.
     */
    private function isValidReportLineStart(string $string): bool
    {
        $string = trim($string);

        $char0 = mb_substr($string, 0, 1, 'utf-8');
        $char1 = mb_substr($string, 1, 1, 'utf-8');
        $char2 = mb_substr($string, 2, 1, 'utf-8');

        if ($char0 === '-' && $char1 === '-' && $char2 !== '-') {
            return true;
        }

        if ($char0 === '+' && $char1 === '+' && $char2 !== '+') {
            return true;
        }

        if ($char0 === '?' && $char1 === '?' && $char2 !== '?') {
            return true;
        }

        return false;
    }
}
