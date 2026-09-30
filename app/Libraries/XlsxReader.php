<?php

namespace App\Libraries;

use DOMDocument;
use DOMElement;
use RuntimeException;
use ZipArchive;

/**
 * Minimális, függőség nélküli .xlsx olvasó (ZipArchive + DOM).
 * Kezeli a "Transitional" és a "Strict OOXML" formátumot is (utóbbit pl.
 * a PhpSpreadsheet / openpyxl nem tudja), mert névtértől függetlenül olvas.
 * Csak cellaértékeket ad vissza, formázást nem; képletnél a mentett értéket.
 */
class XlsxReader
{
    /**
     * Az első munkalap sorai: [sorszám => [oszlopbetű => érték]].
     *
     * @return array<int, array<string, string>>
     */
    public static function readFirstSheet(string $path): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Nem nyitható meg: ' . $path);
        }

        try {
            $sharedStrings = self::readSharedStrings($zip);
            $sheetPath = self::firstSheetPath($zip);
            $xml = $zip->getFromName($sheetPath);
            if ($xml === false) {
                throw new RuntimeException('Hiányzó munkalap: ' . $sheetPath);
            }
        } finally {
            $zip->close();
        }

        $doc = self::loadXml($xml);
        $rows = [];

        foreach ($doc->getElementsByTagNameNS('*', 'row') as $row) {
            /** @var DOMElement $row */
            $rowNum = (int) $row->getAttribute('r');
            $cells = [];

            foreach ($row->childNodes as $cell) {
                if (!$cell instanceof DOMElement || $cell->localName !== 'c') {
                    continue;
                }

                $col = preg_replace('/\d+/', '', $cell->getAttribute('r'));
                $value = self::cellValue($cell, $sharedStrings);
                if ($value !== null && $value !== '') {
                    $cells[$col] = $value;
                }
            }

            if ($cells) {
                $rows[$rowNum] = $cells;
            }
        }

        return $rows;
    }

    private static function cellValue(DOMElement $cell, array $sharedStrings): ?string
    {
        $type = $cell->getAttribute('t');

        if ($type === 'inlineStr') {
            return self::textContent($cell);
        }

        $v = null;
        foreach ($cell->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === 'v') {
                $v = $child->textContent;
                break;
            }
        }

        if ($v === null) {
            return null;
        }

        if ($type === 's') {
            return $sharedStrings[(int) $v] ?? '';
        }

        return $v;
    }

    private static function readSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return [];
        }

        $strings = [];
        foreach (self::loadXml($xml)->getElementsByTagNameNS('*', 'si') as $si) {
            $strings[] = self::textContent($si);
        }

        return $strings;
    }

    /** Összefűzi a <t> elemeket (rich text esetén több is lehet), a fonetikus (rPh) részeket kihagyva. */
    private static function textContent(DOMElement $el): string
    {
        $text = '';
        foreach ($el->getElementsByTagNameNS('*', 't') as $t) {
            if ($t->parentNode instanceof DOMElement && $t->parentNode->localName === 'rPh') {
                continue;
            }
            $text .= $t->textContent;
        }

        return $text;
    }

    private static function firstSheetPath(ZipArchive $zip): string
    {
        $workbook = $zip->getFromName('xl/workbook.xml');
        $rels = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if ($workbook === false || $rels === false) {
            return 'xl/worksheets/sheet1.xml';
        }

        $sheet = self::loadXml($workbook)->getElementsByTagNameNS('*', 'sheet')->item(0);
        if (!$sheet instanceof DOMElement) {
            return 'xl/worksheets/sheet1.xml';
        }

        $relId = null;
        foreach ($sheet->attributes as $attr) {
            if ($attr->localName === 'id') {
                $relId = $attr->value;
                break;
            }
        }

        foreach (self::loadXml($rels)->getElementsByTagNameNS('*', 'Relationship') as $rel) {
            /** @var DOMElement $rel */
            if ($rel->getAttribute('Id') === $relId) {
                $target = $rel->getAttribute('Target');
                return strpos($target, '/') === 0 ? ltrim($target, '/') : 'xl/' . $target;
            }
        }

        return 'xl/worksheets/sheet1.xml';
    }

    private static function loadXml(string $xml): DOMDocument
    {
        $doc = new DOMDocument();
        if (!$doc->loadXML($xml, LIBXML_NONET | LIBXML_COMPACT | LIBXML_PARSEHUGE)) {
            throw new RuntimeException('Hibás XML az xlsx fájlban.');
        }

        return $doc;
    }
}
