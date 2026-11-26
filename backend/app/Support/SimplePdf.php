<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Minimal PDF writer (no extra packages): A4 pages, Helvetica text, lines
 * and filled boxes. Text is plain ASCII (other letters are transliterated).
 * Coordinates are in points from the TOP-LEFT of the page.
 */
class SimplePdf
{
    public const WIDTH  = 595.28;
    public const HEIGHT = 841.89;

    /** Helvetica glyph widths for characters 32..126 (1/1000 em). */
    private const WIDTHS = [
        278, 278, 355, 556, 556, 889, 667, 191, 333, 333, 389, 584, 278, 333, 278, 278,
        556, 556, 556, 556, 556, 556, 556, 556, 556, 556, 278, 278, 584, 584, 584, 556,
        1015, 667, 667, 722, 722, 667, 611, 778, 722, 278, 500, 667, 556, 833, 722, 778,
        667, 778, 722, 667, 611, 722, 667, 944, 667, 667, 611, 278, 278, 278, 469, 556,
        333, 556, 556, 500, 556, 556, 278, 556, 556, 222, 222, 500, 222, 833, 556, 556,
        556, 556, 333, 500, 278, 556, 500, 722, 500, 500, 500, 334, 260, 334, 584,
    ];

    /** @var array<int, string> page content streams */
    private array $pages = [];

    private string $footer = '';

    public function __construct(private string $title = 'Document')
    {
        $this->addPage();
    }

    public function addPage(): void
    {
        $this->pages[] = '';
    }

    public function pageCount(): int
    {
        return count($this->pages);
    }

    /** Footer text; {p} = page number, {n} = number of pages. */
    public function setFooter(string $text): void
    {
        $this->footer = $text;
    }

    public static function clean(string $text): string
    {
        $text = Str::ascii($text);

        return preg_replace('/[^\x20-\x7E]/', '', $text) ?? '';
    }

    public function width(string $text, float $size, bool $bold = false): float
    {
        $sum = 0;

        foreach (str_split(self::clean($text)) as $char) {
            $sum += self::WIDTHS[ord($char) - 32] ?? 556;
        }

        return $sum * $size / 1000 * ($bold ? 1.04 : 1);
    }

    /** Shorten text with "..." so it fits in $maxWidth. */
    public function fit(string $text, float $maxWidth, float $size, bool $bold = false): string
    {
        $text = self::clean($text);

        if ($this->width($text, $size, $bold) <= $maxWidth) {
            return $text;
        }

        while ($text !== '' && $this->width($text . '...', $size, $bold) > $maxWidth) {
            $text = substr($text, 0, -1);
        }

        return rtrim($text) . '...';
    }

    public function text(float $x, float $y, string $text, float $size = 10, bool $bold = false, string $align = 'left'): void
    {
        $text = self::clean($text);

        if ($text === '') {
            return;
        }

        $w = $this->width($text, $size, $bold);

        if ($align === 'right') {
            $x -= $w;
        } elseif ($align === 'center') {
            $x -= $w / 2;
        }

        $this->put(sprintf(
            "BT /%s %.2f Tf %.2f %.2f Td (%s) Tj ET\n",
            $bold ? 'F2' : 'F1',
            $size,
            $x,
            self::HEIGHT - $y,
            str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text)
        ));
    }

    public function line(float $x1, float $y1, float $x2, float $y2, float $width = 0.5, float $gray = 0.6): void
    {
        $this->put(sprintf(
            "%.2f G %.2f w %.2f %.2f m %.2f %.2f l S\n",
            $gray, $width, $x1, self::HEIGHT - $y1, $x2, self::HEIGHT - $y2
        ));
    }

    /** Filled box; $y is the TOP edge. */
    public function box(float $x, float $y, float $w, float $h, float $gray): void
    {
        $this->put(sprintf(
            "%.2f g %.2f %.2f %.2f %.2f re f 0 g\n",
            $gray, $x, self::HEIGHT - $y - $h, $w, $h
        ));
    }

    private function put(string $ops): void
    {
        $this->pages[count($this->pages) - 1] .= $ops;
    }

    public function output(): string
    {
        $n = count($this->pages);

        // Objects: 1 catalog, 2 pages, 3 Helvetica, 4 Helvetica-Bold, 5 info,
        // then for each page: page object + content stream.
        $objects = [];
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';

        $kids = [];

        foreach ($this->pages as $i => $content) {
            $pageObj = 6 + $i * 2;
            $kids[] = "$pageObj 0 R";

            if ($this->footer !== '') {
                $footer = str_replace(['{p}', '{n}'], [(string) ($i + 1), (string) $n], $this->footer);
                $content .= sprintf(
                    "BT /F1 8 Tf 0.4 g %.2f 24 Td (%s) Tj ET\n",
                    40,
                    str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], self::clean($footer))
                );
            }

            $objects[$pageObj] = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2f %.2f] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents %d 0 R >>',
                self::WIDTH, self::HEIGHT, $pageObj + 1
            );
            $objects[$pageObj + 1] = "<< /Length " . strlen($content) . " >>\nstream\n" . $content . "endstream";
        }

        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . "] /Count $n >>";
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objects[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
        $objects[5] = '<< /Title (' . str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], self::clean($this->title)) . ') /Producer (EcoTech OS) >>';

        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $num => $body) {
            $offsets[$num] = strlen($pdf);
            $pdf .= "$num 0 obj\n$body\nendobj\n";
        }

        $xref = strlen($pdf);
        $count = max(array_keys($objects)) + 1;
        $pdf .= "xref\n0 $count\n0000000000 65535 f \n";

        for ($i = 1; $i < $count; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n<< /Size $count /Root 1 0 R /Info 5 0 R >>\nstartxref\n$xref\n%%EOF\n";

        return $pdf;
    }
}
