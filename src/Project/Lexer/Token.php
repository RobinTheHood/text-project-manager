<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Lexer;

class Token
{
    public const TYPE_UNKOWN_TOKEN = -1;
    public const TYPE_EOF = 0;
    public const TYPE_ITENTIFIER = 1;
    public const TYPE_NUMBER = 2;
    public const TYPE_TIME = 4;
    public const TYPE_DATE = 5;
    public const TYPE_TASK_START = 6;
    public const TYPE_SPACE = 7;
    public const TYPE_NEW_LINE = 8;
    public const TYPE_SEPARATOR = 9;
    public const TYPE_SHOW_START = 10;
    public const TYPE_USER_START = 11;
    public const TYPE_REPORT_START = 12;
    public const TYPE_STRING = 13;
    public const TYPE_UNIT = 14;
    public const TYPE_INT = 15;
    public const TYPE_FLOAT = 16;
    public const TYPE_USER_NAME = 17;

    /**
     * @var int
     */
    public $type;

    /**
     * @var string
     */
    public $string;

    public function __construct(int $type, string $string)
    {
        $this->type = $type;
        $this->string = $string;
    }

    public function typeToString(int $type): string
    {
        if ($type == self::TYPE_ITENTIFIER) {
            return 'TYPE_ITENTIFIER';
        } elseif ($type == self::TYPE_NUMBER) {
            return 'TYPE_NUMBER';
        } elseif ($type == self::TYPE_TIME) {
            return 'TYPE_TIME';
        } elseif ($type == self::TYPE_DATE) {
            return 'TYPE_DATE';
        } elseif ($type == self::TYPE_EOF) {
            return 'TYPE_EOF';
        } elseif ($type == self::TYPE_TASK_START) {
            return 'TYPE_TASK_START';
        } elseif ($type == self::TYPE_SPACE) {
            return 'TYPE_SPACE';
        } elseif ($type == self::TYPE_NEW_LINE) {
            return 'TYPE_NEW_LINE';
        } elseif ($type == self::TYPE_SEPARATOR) {
            return 'TYPE_SEPARATOR';
        } elseif ($type == self::TYPE_SHOW_START) {
            return 'TYPE_SHOW_START';
        } elseif ($type == self::TYPE_USER_START) {
            return 'TYPE_USER_START';
        } elseif ($type == self::TYPE_REPORT_START) {
            return 'TYPE_REPORT_START';
        } elseif ($type == self::TYPE_STRING) {
            return 'TYPE_STRING';
        } elseif ($type == self::TYPE_UNIT) {
            return 'TYPE_UNIT';
        } elseif ($type == self::TYPE_INT) {
            return 'TYPE_INT';
        } elseif ($type == self::TYPE_FLOAT) {
            return 'TYPE_FLOAT';
        } elseif ($type == self::TYPE_USER_NAME) {
            return 'TYPE_USER_NAME';
        } elseif ($type == self::TYPE_UNKOWN_TOKEN) {
            return 'TYPE_UNKOWN_TOKEN';
        }
        return 'Unkown type';
    }

    public function __toString()
    {
        $string = $this->string;
        $string = str_replace("\n", '<newline>', $string);
        $string = str_replace("\r", '<newline>', $string);
        $string = str_replace(" ", '<space>', $string);
        $string = str_replace("\t", '<tab>', $string);
        return $this->typeToString($this->type) . ' : ' . $string;
    }

    public function __debugInfo()
    {
        return [
            'type' => $this->typeToString($this->type),
            'string' => $this->string
        ];
    }
}
