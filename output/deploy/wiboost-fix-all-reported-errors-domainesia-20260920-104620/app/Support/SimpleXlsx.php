<?php

namespace App\Support;

class SimpleXlsx
{
    public static function download(
        string $title,
        string $sheetName,
        string $fileName,
        string $subtitle,
        array $headers,
        array $dataRows,
        array $widths = [],
        array $numericColumns = [],
        array $currencyColumns = [],
        array $totalRow = []
    ) {
        $workbook = self::make($title, $sheetName, $subtitle, $headers, $dataRows, $widths, $numericColumns, $currencyColumns, $totalRow);

        return response($workbook)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->header('Content-Length', strlen($workbook))
            ->header('Cache-Control', 'max-age=0');
    }

    public static function make(
        string $title,
        string $sheetName,
        string $subtitle,
        array $headers,
        array $dataRows,
        array $widths = [],
        array $numericColumns = [],
        array $currencyColumns = [],
        array $totalRow = []
    ): string {
        $columnCount = max(1, count($headers));
        $lastColumn = self::columnName($columnCount);
        $rows = [];
        $rows[] = [1, [['A', $title, 'text', 1]]];
        $rows[] = [2, [['A', $subtitle, 'text', 5]]];

        $headerCells = [];
        foreach ($headers as $index => $header) {
            $headerCells[] = [self::columnName($index + 1), $header, 'text', 2];
        }
        $rows[] = [4, $headerCells];

        $rowNumber = 5;
        foreach ($dataRows as $row) {
            $cells = [];
            for ($index = 0; $index < $columnCount; $index++) {
                $columnIndex = $index + 1;
                $type = in_array($columnIndex, $numericColumns, true) || in_array($columnIndex, $currencyColumns, true) ? 'number' : 'text';
                $style = in_array($columnIndex, $currencyColumns, true) ? 3 : 0;
                $cells[] = [self::columnName($columnIndex), $row[$index] ?? '', $type, $style];
            }
            $rows[] = [$rowNumber, $cells];
            $rowNumber++;
        }

        if ($dataRows === []) {
            $rows[] = [$rowNumber, [['A', 'Tidak ada data pada periode dan filter ini.', 'text', 0]]];
            $rowNumber++;
        }

        if ($totalRow !== []) {
            $cells = [];
            for ($index = 0; $index < $columnCount; $index++) {
                $columnIndex = $index + 1;
                $value = $totalRow[$index] ?? '';
                $type = in_array($columnIndex, $numericColumns, true) || in_array($columnIndex, $currencyColumns, true) ? 'number' : 'text';
                $style = in_array($columnIndex, $currencyColumns, true) ? 4 : 6;
                $cells[] = [self::columnName($columnIndex), $value, $type, $style];
            }
            $rows[] = [$rowNumber, $cells];
        }

        $sheetRows = collect($rows)->map(function ($row) {
            [$number, $cells] = $row;
            $cellXml = collect($cells)
                ->map(fn ($cell) => self::xlsxCell($cell[0] . $number, $cell[1], $cell[2], $cell[3]))
                ->implode('');

            return '<row r="' . $number . '">' . $cellXml . '</row>';
        })->implode('');

        $cols = '';
        for ($index = 1; $index <= $columnCount; $index++) {
            $width = max(8, (float) ($widths[$index - 1] ?? 22));
            $cols .= '<col min="' . $index . '" max="' . $index . '" width="' . $width . '" customWidth="1"/>';
        }

        $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<cols>' . $cols . '</cols>'
            . '<sheetData>' . $sheetRows . '</sheetData>'
            . '<mergeCells count="2"><mergeCell ref="A1:' . $lastColumn . '1"/><mergeCell ref="A2:' . $lastColumn . '2"/></mergeCells>'
            . '</worksheet>';

        return self::zipStored([
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/><Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/></Relationships>',
            'docProps/app.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes"><Application>Wiboost Store</Application></Properties>',
            'docProps/core.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><dc:title>' . self::xmlSafe($title) . '</dc:title><dc:creator>Wiboost Store</dc:creator><cp:lastModifiedBy>Wiboost Store</cp:lastModifiedBy></cp:coreProperties>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="' . self::xmlAttributeSafe($sheetName) . '" sheetId="1" r:id="rId1"/></sheets></workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>',
            'xl/styles.xml' => self::xlsxStyles(),
            'xl/worksheets/sheet1.xml' => $sheet,
        ]);
    }

    private static function xlsxCell(string $cell, mixed $value, string $type = 'text', int $style = 0): string
    {
        $styleAttribute = $style > 0 ? ' s="' . $style . '"' : '';

        if ($type === 'number' && is_numeric($value)) {
            return '<c r="' . $cell . '"' . $styleAttribute . '><v>' . (float) $value . '</v></c>';
        }

        return '<c r="' . $cell . '" t="inlineStr"' . $styleAttribute . '><is><t>' . self::xmlSafe($value) . '</t></is></c>';
    }

    private static function xlsxStyles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="1"><numFmt numFmtId="164" formatCode="&quot;Rp&quot; #,##0"/></numFmts>'
            . '<fonts count="3"><font><sz val="11"/><name val="Arial"/></font><font><b/><sz val="18"/><color rgb="FF5A76C8"/><name val="Arial"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Arial"/></font></fonts>'
            . '<fills count="4"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF5A76C8"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFE6FFF7"/><bgColor indexed="64"/></patternFill></fill></fills>'
            . '<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"><color rgb="FFBDE0FE"/></left><right style="thin"><color rgb="FFBDE0FE"/></right><top style="thin"><color rgb="FFBDE0FE"/></top><bottom style="thin"><color rgb="FFBDE0FE"/></bottom><diagonal/></border></borders>'
            . '<cellXfs count="7">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"><alignment vertical="top" wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"><alignment vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="2" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"><alignment vertical="top"/></xf>'
            . '<xf numFmtId="164" fontId="0" fillId="3" borderId="1" xfId="0" applyNumberFormat="1" applyFill="1" applyBorder="1"><alignment vertical="top"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"><alignment vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1"><alignment vertical="top" wrapText="1"/></xf>'
            . '</cellXfs>'
            . '</styleSheet>';
    }

    private static function zipStored(array $files): string
    {
        $localFiles = '';
        $centralDirectory = '';
        $offset = 0;
        $time = 0;
        $date = 0;

        foreach ($files as $name => $content) {
            $nameLength = strlen($name);
            $size = strlen($content);
            $crc = crc32($content);

            if ($crc < 0) {
                $crc += 4294967296;
            }

            $localHeader = pack('VvvvvvVVVvv', 0x04034b50, 20, 0, 0, $time, $date, $crc, $size, $size, $nameLength, 0) . $name;
            $localFiles .= $localHeader . $content;

            $centralDirectory .= pack('VvvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0, 0, $time, $date, $crc, $size, $size, $nameLength, 0, 0, 0, 0, 0, $offset) . $name;
            $offset += strlen($localHeader) + $size;
        }

        $entryCount = count($files);
        $centralSize = strlen($centralDirectory);
        $end = pack('VvvvvVVv', 0x06054b50, 0, 0, $entryCount, $entryCount, $centralSize, $offset, 0);

        return $localFiles . $centralDirectory . $end;
    }

    private static function columnName(int $column): string
    {
        $name = '';

        while ($column > 0) {
            $column--;
            $name = chr(65 + ($column % 26)) . $name;
            $column = intdiv($column, 26);
        }

        return $name;
    }

    private static function xmlSafe(mixed $value): string
    {
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string) $value) ?? '';

        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private static function xmlAttributeSafe(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
