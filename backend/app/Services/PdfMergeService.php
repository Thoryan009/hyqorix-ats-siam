<?php

namespace App\Services;

use Exception;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Storage;

class PdfMergeService
{
    public function mergeFromStoragePaths(array $paths): string
    {
        $merger = new Merger();

        $tempFiles = [];
        $normalizedFiles = [];

        try {

            foreach ($paths as $path) {

                // Full storage path
                $fullPath = storage_path('app/public/' . $path);

                if (!file_exists($fullPath)) {
                    continue;
                }

                // Normalize PDF before merge
                $normalizedPath = $this->normalizePdf($fullPath);

                $normalizedFiles[] = $normalizedPath;

                $merger->addFile($normalizedPath);
            }

            // Merge all PDFs
            $mergedPdf = $merger->merge();

            // Save merged PDF
            $mergedPath = 'documents/merged_' . time() . '.pdf';

            Storage::disk('public')->put($mergedPath, $mergedPdf);

            return $mergedPath;

        } catch (\Throwable $e) {

            throw $e;

        } finally {

            // Cleanup normalized temp files
            foreach ($normalizedFiles as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }

            // Cleanup extra temp files if needed
            foreach ($tempFiles as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }

    private function normalizePdf(string $inputPath): string
    {
        $tempDir = storage_path('app/temp');

        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $outputPath = $tempDir . '/' . uniqid('normalized_') . '.pdf';

        $command = sprintf(
            'gs -o %s -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 %s 2>&1',
            escapeshellarg($outputPath),
            escapeshellarg($inputPath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception(
                'PDF normalization failed: ' . implode("\n", $output)
            );
        }

        return $outputPath;
    }

}
