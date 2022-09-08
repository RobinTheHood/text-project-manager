<?php

declare(strict_types=1);

namespace App\Project\Parsers;

use App\Helpers\StringHelper;
use App\Project\Entities\Report;
use App\Project\Interfaces\AmountParserInterface;
use App\Project\Interfaces\PriceParserInterface;

class ReportParser
{
    private $amountParser;
    private $priceParser;

    public function __construct(AmountParserInterface $amountParser, PriceParserInterface $priceParser)
    {
        $this->amountParser = $amountParser;
        $this->priceParser = $priceParser;
    }

    public function parse(string $string): ?Report
    {
        $string = trim($string);

        if (!$this->isValidReportLineStart($string)) {
            return null;
        }

        $typeString = mb_substr($string, 0, 2, 'utf-8');

        $type = Report::TYPE_BILLABLE;
        if ($typeString == '++') {
            $type = Report::TYPE_BILLABLE;
        } elseif ($typeString == '--') {
            $type = Report::TYPE_UNBILLABLE;
        }

        $string = StringHelper::skipLetters($string, 2);
        $stringParts = StringHelper::getTrimmedLineParts($string, ';');

        $description = $stringParts[2] ?? '';
        $date = $stringParts[0] ?? '';
        $amount = $this->amountParser->parse($stringParts[1] ?? '');

        $externalPrice = $this->priceParser->parse($stringParts[3] ?? '');
        $internalPrice = $this->priceParser->parse($stringParts[4] ?? '');

        $report = new Report();
        $report->description = $description;
        $report->type = $type;
        $report->date = $date;
        $report->amount = $amount;
        $report->externalPrice = $externalPrice;
        $report->internalPrice = $internalPrice;

        return $report;
    }

    /**
     * Kontrolliert ob es sich um eine um eine Zeile mit einem validen Zeichen
     * Anfang für einen Task handelt. Ein Task fängt mit einem - an und darf danach
     * nicht direkt einen weiteren - haben.
     */
    private function isValidReportLineStart(string $string): bool
    {
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
