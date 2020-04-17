<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\IOFactory as FileReader;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SpreadsheetService
{
    /**
     * @param $file
     * @return Worksheet
     */
    public function loadFile($file)
    {
        $format = FileReader::identify($file);
        $reader = FileReader::createReader($format);

        if ($format === 'Csv') {
            $reader->setInputEncoding('CP1252');
            $reader->setDelimiter(';');
            $reader->setEnclosure('');
            $reader->setSheetIndex(0);
        }

        $spreadsheet = $reader->load($file)->getActiveSheet();

        return $spreadsheet;
    }

    /**
     * @param Worksheet $spreadsheet
     * @return array
     */
    public function createAssociativeArray(Worksheet $spreadsheet)
    {
        $titles = [];
        $data = [];

        $readFrom = 1;

        foreach ($spreadsheet->getRowIterator() as $line => $row)
        {
            // If second cell haven`t value -> row is spreadsheet title and must be skipped
            if ($readFrom !== 0 && !$spreadsheet->getCellByColumnAndRow(2, $readFrom)->getValue()) {
                $readFrom++;
                continue;
            }

            // From here we start reading correct data
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            foreach ($cellIterator as $i => $cell) {
                // If number of line equal to started read from number -> save titles
                if ($line == $readFrom) {
                    $titles[$i] = $cell->getValue();
                } else {
                    $data[$line][$titles[$i]] = $cell->getValue();
                }
            }

            // Set read from to 0 means that we will not skip rows anymore
            $readFrom = 0;
        }

        return $data;
    }
}
