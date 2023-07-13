<?php

declare(strict_types=1);

namespace RobinTheHood\TextProjectManager\Project\Creators;

use DateTime;

class Compiler
{
    private $templatePath = __DIR__ . '/Templates/';
    private $templateExtention = '.tmpl.md';

    public function compile(string $string): string
    {
        $string = $this->compileTemplate($string);
        $string = $this->compileTime($string);
        return $string;
    }

    private function compileTime($string): string
    {
        $today = date('d.m.Y');
        $yesterday = date('d.m.Y', time() - 60 * 60 * 24);
        $monday = $this->getNearestDate('Monday');
        $tuesday = $this->getNearestDate('tuesday');
        $wednesday = $this->getNearestDate('wednesday');
        $thursday = $this->getNearestDate('thursday');
        $friday = $this->getNearestDate('friday');
        $saturday = $this->getNearestDate('saturday');
        $sunday = $this->getNearestDate('sunday');

        $string = $this->replaceTime('heute', $today, $string);
        $string = $this->replaceTime('gestern', $yesterday, $string);

        $string = $this->replaceTime('montag', $monday, $string);
        $string = $this->replaceTime('Montag', $monday, $string);
        $string = $this->replaceTime('Dienstag', $tuesday, $string);
        $string = $this->replaceTime('dienstag', $tuesday, $string);
        $string = $this->replaceTime('Mittwoch', $wednesday, $string);
        $string = $this->replaceTime('mittwoch', $wednesday, $string);
        $string = $this->replaceTime('Donnerstag', $thursday, $string);
        $string = $this->replaceTime('donnerstag', $thursday, $string);
        $string = $this->replaceTime('Freitag', $friday, $string);
        $string = $this->replaceTime('freitag', $friday, $string);
        $string = $this->replaceTime('Samstag', $saturday, $string);
        $string = $this->replaceTime('samstag', $saturday, $string);
        $string = $this->replaceTime('Sonntag', $sunday, $string);
        $string = $this->replaceTime('sonntag', $sunday, $string);

        return $string;
    }

    private function replaceTime($search, $replace, $string): string
    {
        $string = str_replace('- ' . $search . ';', '- ' . $replace . ';', $string);
        $string = str_replace('+ ' . $search . ';', '+ ' . $replace . ';', $string);
        return $string;
    }

    private function compileTemplate(string $string): string
    {
        $string = trim($string);

        $templatesFiles = $this->getFilesEndingWith($this->templatePath, $this->templateExtention);

        foreach ($templatesFiles as $templatesFile) {
            $fileNameWithoutExtention = basename($templatesFile, $this->templateExtention);
            var_dump($fileNameWithoutExtention);
            $command = '> ' . $fileNameWithoutExtention;
            if ($string === $command) {
                return file_get_contents($templatesFile);
            }
        }

        return $string;
    }


    private function getFilesEndingWith($directory, $extension): array
    {
        $files = [];

        if (is_dir($directory)) {
            $handle = opendir($directory);

            while (($file = readdir($handle)) !== false) {
                if ($file != '.' && $file != '..' && is_file($directory . '/' . $file) && substr($file, -strlen($extension)) === $extension) {
                    $files[] = $directory . '/' . $file;
                }
            }

            closedir($handle);
        }

        return $files;
    }


    private function getNearestDate(string $weekday): string
    {
        $weekday = strtolower($weekday);
        $weekday = ucfirst($weekday);

        $now = new DateTime();
        $currentWeekday = $now->format('N');
        $targetWeekday = date('N', strtotime("next $weekday"));

        if ($currentWeekday >= $targetWeekday) {
            $date = $now->modify("last $weekday")->format('d.m.Y');
        } else {
            $date = $now->modify("this $weekday")->format('d.m.Y');
        }

        return $date;
    }
}
