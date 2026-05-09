<?php

namespace App\Services;

use App\Models\Appointment;
use DOMDocument;
use DOMElement;
use DOMNode;

class AppointmentPdfService
{
    public function render(Appointment $appointment): string
    {
        $appointment->loadMissing(['client', 'barbershop', 'service']);

        $barberComment = $appointment->barber_comment ?: $appointment->rejection_reason;
        $html = view('appointments.pdf', [
            'appointment' => $appointment,
            'barberComment' => $barberComment,
            'generatedAt' => now(),
            'statusLabel' => 'Aceptada',
            'codigo' => $appointment->confirmation_code,
        ])->render();

        return $this->buildPdf($this->blocksFromHtml($html));
    }

    private function blocksFromHtml(string $html): array
    {
        $document = new DOMDocument();
        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();

        $blocks = [];
        foreach ($document->getElementsByTagName('section') as $childNode) {
            $items = [];
            $this->appendItemsFromNode($childNode, $items);

            if ($items !== []) {
                $blocks[] = [
                    'class' => $childNode->getAttribute('class'),
                    'items' => $items,
                ];
            }
        }

        return $blocks;
    }

    private function appendItemsFromNode(DOMNode $node, array &$items): void
    {
        if ($node instanceof DOMElement && in_array($node->tagName, ['h1', 'h2', 'p'], true)) {
            $text = trim(preg_replace('/\s+/', ' ', $node->textContent));

            if ($text !== '') {
                $items[] = [
                    'tag' => $node->tagName,
                    'class' => $node->getAttribute('class'),
                    'text' => $text,
                ];
            }

            return;
        }

        foreach ($node->childNodes as $childNode) {
            $this->appendItemsFromNode($childNode, $items);
        }
    }

    private function buildPdf(array $blocks): string
    {
        $content = $this->drawRectangle(0, 0, 595, 842, [249, 250, 251]);
        $y = 790;

        foreach ($blocks as $block) {
            if ($this->hasClass($block['class'], 'bg-indigo-600')) {
                $content .= $this->drawHero($block['items'], $y);
                $y -= 142;
                continue;
            }

            if ($this->hasClass($block['class'], 'text-gray-500')) {
                foreach ($block['items'] as $item) {
                    $content .= $this->drawText(72, $y, $item['text'], 9, [107, 114, 128]);
                    $y -= 16;
                }
                continue;
            }

            $card = $this->drawCard($block['items'], $y);
            $content .= $card['content'];
            $y -= $card['height'] + 18;
        }

        $objects = [
            "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
            "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n",
            "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>\nendobj\n",
            "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>\nendobj\n",
            "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>\nendobj\n",
            "6 0 obj\n<< /Length " . strlen($content) . " >>\nstream\n" . $content . "endstream\nendobj\n",
        ];

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF\n";

        return $pdf;
    }

    private function drawHero(array $items, int $topY): string
    {
        $content = $this->drawRectangle(48, $topY - 118, 499, 118, [79, 70, 229]);
        $textY = $topY - 28;

        foreach ($items as $item) {
            if ($this->hasClass($item['class'], 'text-violet-200')) {
                $content .= $this->drawText(72, $textY, $item['text'], 11, [221, 214, 254], 'F2');
                $textY -= 20;
                continue;
            }

            if ($item['tag'] === 'h1') {
                $content .= $this->drawText(72, $textY, $item['text'], 22, [255, 255, 255], 'F2');
                $textY -= 30;
                continue;
            }

            if ($this->hasClass($item['class'], 'bg-green-100')) {
                $content .= $this->drawRectangle(72, $textY - 6, 132, 20, [220, 252, 231]);
                $content .= $this->drawText(82, $textY, $item['text'], 10, [22, 101, 52], 'F2');
                $textY -= 26;
                continue;
            }

            $content .= $this->drawText(72, $textY, $item['text'], 11, [238, 242, 255]);
            $textY -= 18;
        }

        return $content;
    }

    private function drawCard(array $items, int $topY): array
    {
        $preparedItems = [];
        $contentHeight = 22;

        foreach ($items as $item) {
            $fontSize = $item['tag'] === 'h2' ? 13 : 10;
            $font = $item['tag'] === 'h2' ? 'F2' : 'F1';
            $color = $item['tag'] === 'h2' ? [31, 41, 55] : [75, 85, 99];
            $maxChars = $item['tag'] === 'h2' ? 58 : 74;
            $wrappedLines = explode("\n", wordwrap($item['text'], $maxChars, "\n", false));

            $preparedItems[] = compact('wrappedLines', 'fontSize', 'font', 'color');
            $contentHeight += count($wrappedLines) * ($fontSize + 6);
            $contentHeight += $item['tag'] === 'h2' ? 5 : 1;
        }

        $height = max(82, $contentHeight + 18);
        $bottomY = $topY - $height;
        $content = $this->drawRectangle(48, $bottomY, 499, $height, [255, 255, 255], [229, 231, 235]);
        $content .= $this->drawRectangle(48, $topY - 4, 499, 4, [124, 58, 237]);
        $textY = $topY - 28;

        foreach ($preparedItems as $preparedItem) {
            foreach ($preparedItem['wrappedLines'] as $line) {
                $content .= $this->drawText(72, $textY, $line, $preparedItem['fontSize'], $preparedItem['color'], $preparedItem['font']);
                $textY -= $preparedItem['fontSize'] + 6;
            }

            $textY -= $preparedItem['font'] === 'F2' ? 5 : 1;
        }

        return [
            'content' => $content,
            'height' => $height,
        ];
    }

    private function drawRectangle(float $x, float $y, float $width, float $height, array $fillRgb, ?array $strokeRgb = null): string
    {
        $fill = $this->rgbCommand($fillRgb, 'rg');

        if ($strokeRgb === null) {
            return sprintf("q %s %.2f %.2f %.2f %.2f re f Q\n", $fill, $x, $y, $width, $height);
        }

        return sprintf(
            "q %s %s %.2f %.2f %.2f %.2f re B Q\n",
            $fill,
            $this->rgbCommand($strokeRgb, 'RG'),
            $x,
            $y,
            $width,
            $height,
        );
    }

    private function drawText(float $x, float $y, string $text, int $fontSize, array $rgb, string $font = 'F1'): string
    {
        return sprintf(
            "BT %s /%s %d Tf %.2f %.2f Td (%s) Tj ET\n",
            $this->rgbCommand($rgb, 'rg'),
            $font,
            $fontSize,
            $x,
            $y,
            $this->escapePdfText($text),
        );
    }

    private function rgbCommand(array $rgb, string $operator): string
    {
        return sprintf('%.3f %.3f %.3f %s', $rgb[0] / 255, $rgb[1] / 255, $rgb[2] / 255, $operator);
    }

    private function hasClass(string $classes, string $class): bool
    {
        return in_array($class, preg_split('/\s+/', trim($classes)) ?: [], true);
    }

    private function escapePdfText(string $text): string
    {
        $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);

        return str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\\(', '\\)'],
            $encoded === false ? $text : $encoded,
        );
    }
}
