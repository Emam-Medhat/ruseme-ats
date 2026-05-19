<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use ZipArchive;

class DocumentExtractorService
{
    public function extract(string $filePath, ?string $extension = null): string
    {
        $extension = strtolower($extension ?? pathinfo($filePath, PATHINFO_EXTENSION));

        return match ($extension) {
            'docx' => $this->extractFromDocx($filePath),
            'pdf' => $this->extractFromPdf($filePath),
            default => throw new \InvalidArgumentException("Unsupported file type: {$extension}"),
        };
    }

    public function extractFromPdf(string $filePath): string
    {
        $fullPath = Storage::disk('local')->path($filePath);
        $parser = new Parser();
        $pdf = $parser->parseFile($fullPath);

        return trim($pdf->getText());
    }

    public function extractFromDocx(string $filePath): string
    {
        $fullPath = Storage::disk('local')->path($filePath);
        $zip = new ZipArchive();

        if ($zip->open($fullPath) !== true) {
            throw new \RuntimeException('Could not open DOCX archive.');
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false || $xml === '') {
            throw new \RuntimeException('DOCX document body is empty.');
        }

        $xml = preg_replace('/<w:tab[^>]*\/>/', "\t", $xml) ?? $xml;
        $xml = preg_replace('/<w:br[^>]*\/>/', "\n", $xml) ?? $xml;
        $xml = preg_replace('/<\/w:p>/', "\n", $xml) ?? $xml;
        $text = strip_tags($xml);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $text = preg_replace("/[ \t]+/", ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text);
    }
}
