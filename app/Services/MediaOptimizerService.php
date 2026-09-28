<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class MediaOptimizerService
{
    /**
     * Kualitas kompresi WebP default (82-85 memberikan reduksi ukuran 40-80% tanpa penurunan kualitas visual kasat mata).
     */
    protected int $imageQuality = 85;

    /**
     * Optimasi dan simpan berkas unggahan (gambar dikonversi ke WebP, dokumen dikompresi).
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string $disk
     * @return array{path: string, file_name: string, file_type: string, file_size: int, original_size: int}
     */
    public function optimizeAndStore(UploadedFile $file, string $directory = 'media', string $disk = 'public'): array
    {
        $originalName = $file->getClientOriginalName();
        $mime = $file->getMimeType() ?: '';
        $ext = strtolower($file->getClientOriginalExtension());
        $originalSize = $file->getSize() ?: 0;

        // 1. Gambar Raster (PNG, JPG, JPEG, WEBP) -> Konversi & Kompresi ke WebP
        if ($this->isConvertibleImage($mime, $ext)) {
            $result = $this->convertImageToWebp($file, $directory, $disk);
            if ($result) {
                return $result;
            }
        }

        // 2. Dokumen Office (DOCX, XLSX, PPTX) -> Kompresi Arsip Zip Deflate Level 9
        if ($this->isOfficeDocument($mime, $ext)) {
            $result = $this->compressOfficeDocument($file, $directory, $disk);
            if ($result) {
                return $result;
            }
        }

        // 3. Dokumen PDF -> Optimasi Stream PDF
        if ($this->isPdfDocument($mime, $ext)) {
            $result = $this->optimizePdfDocument($file, $directory, $disk);
            if ($result) {
                return $result;
            }
        }

        // 4. Fallback Default untuk format lain (SVG, dll.)
        $storedPath = $file->store($directory, $disk);
        $finalSize = Storage::disk($disk)->size($storedPath);

        return [
            'path' => $storedPath,
            'file_name' => $originalName,
            'file_type' => $mime ?: 'application/octet-stream',
            'file_size' => $finalSize ?: $originalSize,
            'original_size' => $originalSize,
        ];
    }

    /**
     * Periksa apakah berkas merupakan gambar raster yang dapat dikonversi ke WebP.
     */
    protected function isConvertibleImage(string $mime, string $ext): bool
    {
        if (in_array($ext, ['svg', 'ico', 'gif'], true)) {
            return false;
        }

        if (str_starts_with($mime, 'image/') || in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return extension_loaded('gd') && function_exists('imagewebp');
        }

        return false;
    }

    /**
     * Konversi gambar PNG/JPG/JPEG/WEBP ke format WebP berkualitas tinggi dengan kompresi ukuran.
     */
    protected function convertImageToWebp(UploadedFile $file, string $directory, string $disk): ?array
    {
        $realPath = $file->getRealPath();
        if (! $realPath || ! file_exists($realPath)) {
            return null;
        }

        $image = null;
        $mime = $file->getMimeType();
        $ext = strtolower($file->getClientOriginalExtension());

        try {
            if ($mime === 'image/jpeg' || in_array($ext, ['jpg', 'jpeg'], true)) {
                $image = @imagecreatefromjpeg($realPath);
            } elseif ($mime === 'image/png' || $ext === 'png') {
                $image = @imagecreatefrompng($realPath);
            } elseif ($mime === 'image/webp' || $ext === 'webp') {
                $image = @imagecreatefromwebp($realPath);
            }

            if (! $image) {
                // Fallback deteksi via getimagesize
                $info = @getimagesize($realPath);
                if ($info) {
                    $image = match ($info[2] ?? null) {
                        IMAGETYPE_JPEG => @imagecreatefromjpeg($realPath),
                        IMAGETYPE_PNG => @imagecreatefrompng($realPath),
                        IMAGETYPE_WEBP => @imagecreatefromwebp($realPath),
                        default => null,
                    };
                }
            }

            if (! $image) {
                return null;
            }

            // Pertahankan transparansi kanal Alpha (untuk PNG / WebP transparan)
            imagealphablending($image, false);
            imagesavealpha($image, true);

            $tempPath = tempnam(sys_get_temp_dir(), 'webp_');
            $success = @imagewebp($image, $tempPath, $this->imageQuality);
            @imagedestroy($image);

            if (! $success || ! file_exists($tempPath)) {
                @unlink($tempPath);
                return null;
            }

            $optimizedSize = filesize($tempPath);
            $hashName = Str::random(40) . '.webp';
            $targetPath = trim($directory, '/') . '/' . $hashName;

            Storage::disk($disk)->put($targetPath, file_get_contents($tempPath));
            @unlink($tempPath);

            // Ganti ekstensi nama berkas asli menjadi .webp
            $originalBase = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $newFileName = $originalBase . '.webp';

            return [
                'path' => $targetPath,
                'file_name' => $newFileName,
                'file_type' => 'image/webp',
                'file_size' => $optimizedSize,
                'original_size' => $file->getSize() ?: $optimizedSize,
            ];
        } catch (\Throwable) {
            if ($image) {
                @imagedestroy($image);
            }
            return null;
        }
    }

    /**
     * Cek apakah berkas merupakan dokumen Office Open XML (DOCX, XLSX, PPTX).
     */
    protected function isOfficeDocument(string $mime, string $ext): bool
    {
        $officeExts = ['docx', 'xlsx', 'pptx'];
        if (in_array($ext, $officeExts, true)) {
            return class_exists('ZipArchive');
        }

        $officeMimes = [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];

        return in_array($mime, $officeMimes, true) && class_exists('ZipArchive');
    }

    /**
     * Kompresi dokumen Office Open XML dengan re-pack deflate level 9.
     */
    protected function compressOfficeDocument(UploadedFile $file, string $directory, string $disk): ?array
    {
        $realPath = $file->getRealPath();
        if (! $realPath || ! file_exists($realPath) || ! class_exists('ZipArchive')) {
            return null;
        }

        $tempOptimized = tempnam(sys_get_temp_dir(), 'office_');

        try {
            $zipIn = new ZipArchive();
            if ($zipIn->open($realPath) !== true) {
                @unlink($tempOptimized);
                return null;
            }

            $zipOut = new ZipArchive();
            if ($zipOut->open($tempOptimized, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                $zipIn->close();
                @unlink($tempOptimized);
                return null;
            }

            // Kompresi ulang tiap file internal dengan kompresi DEFLATE maksimum (Level 9)
            for ($i = 0; $i < $zipIn->numFiles; $i++) {
                $stat = $zipIn->statIndex($i);
                $name = $stat['name'];
                $content = $zipIn->getFromIndex($i);

                if ($content !== false) {
                    $zipOut->addFromString($name, $content);
                    $zipOut->setCompressionName($name, ZipArchive::CM_DEFLATE, 9);
                }
            }

            $zipIn->close();
            $zipOut->close();

            $originalSize = $file->getSize() ?: filesize($realPath);
            $compressedSize = filesize($tempOptimized);

            // Gunakan hasil kompresi jika lebih kecil atau sama, jika tidak pertahankan original
            $useFile = ($compressedSize > 0 && $compressedSize <= $originalSize)
                ? $tempOptimized
                : $realPath;

            $ext = $file->getClientOriginalExtension() ?: 'docx';
            $hashName = Str::random(40) . '.' . $ext;
            $targetPath = trim($directory, '/') . '/' . $hashName;

            Storage::disk($disk)->put($targetPath, file_get_contents($useFile));
            @unlink($tempOptimized);

            $finalSize = Storage::disk($disk)->size($targetPath);

            return [
                'path' => $targetPath,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType() ?: 'application/vnd.openxmlformats-officedocument',
                'file_size' => $finalSize ?: $compressedSize,
                'original_size' => $originalSize,
            ];
        } catch (\Throwable) {
            @unlink($tempOptimized);
            return null;
        }
    }

    /**
     * Cek apakah berkas merupakan dokumen PDF.
     */
    protected function isPdfDocument(string $mime, string $ext): bool
    {
        return $ext === 'pdf' || str_contains($mime, 'pdf');
    }

    /**
     * Optimasi dokumen PDF (deflate uncompressed stream & strip duplikasi metadata).
     */
    protected function optimizePdfDocument(UploadedFile $file, string $directory, string $disk): ?array
    {
        $realPath = $file->getRealPath();
        if (! $realPath || ! file_exists($realPath)) {
            return null;
        }

        $originalSize = $file->getSize() ?: filesize($realPath);

        try {
            $content = file_get_contents($realPath);
            if ($content === false || ! str_starts_with($content, '%PDF-')) {
                return null;
            }

            // Cari stream yang belum terkompresi dan terapkan flate compression (gzcompress)
            $optimizedContent = preg_replace_callback(
                '/<<([^>]*)>>\s*stream[\r\n]+(.*?)[\r\n]+endstream/s',
                function ($matches) {
                    $dict = $matches[1];
                    $streamData = $matches[2];

                    // Jika stream belum memiliki /Filter /FlateDecode, kompresi dengan gzcompress
                    if (! str_contains($dict, '/Filter') && function_exists('gzcompress')) {
                        $compressed = @gzcompress($streamData, 9);
                        if ($compressed !== false && strlen($compressed) < strlen($streamData)) {
                            $newDict = preg_replace('/\/Length\s+\d+/', '/Length ' . strlen($compressed), $dict);
                            if (! str_contains($newDict, '/Length')) {
                                $newDict .= ' /Length ' . strlen($compressed);
                            }
                            $newDict .= ' /Filter /FlateDecode';
                            return "<<" . $newDict . ">>\nstream\n" . $compressed . "\nendstream";
                        }
                    }

                    return $matches[0];
                },
                $content
            );

            $useContent = ($optimizedContent && strlen($optimizedContent) < strlen($content))
                ? $optimizedContent
                : $content;

            $hashName = Str::random(40) . '.pdf';
            $targetPath = trim($directory, '/') . '/' . $hashName;

            Storage::disk($disk)->put($targetPath, $useContent);
            $finalSize = Storage::disk($disk)->size($targetPath);

            return [
                'path' => $targetPath,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => 'application/pdf',
                'file_size' => $finalSize ?: strlen($useContent),
                'original_size' => $originalSize,
            ];
        } catch (\Throwable) {
            return null;
        }
    }
}
