<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    private const ALLOWED_EXTENSIONS = [
        'pdf', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx', 'xls', 'xlsx',
        'zip', 'rar', 'txt', 'csv',
    ];

    private const MAX_FILE_SIZE = 10240;

    public function upload(UploadedFile $file, string $path = 'uploads', array $allowedExtensions = []): string
    {
        $this->validate($file, $allowedExtensions);

        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs($path, $fileName, 'public');

        if (!$filePath) {
            throw new \RuntimeException('File upload failed');
        }

        return $filePath;
    }

    public function uploadMultiple(array $files, string $path = 'uploads'): array
    {
        $paths = [];
        foreach ($files as $key => $file) {
            if ($file instanceof UploadedFile) {
                $paths[$key] = $this->upload($file, $path);
            }
        }
        return $paths;
    }

    public function delete(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        return false;
    }

    public function validate(UploadedFile $file, array $allowedExtensions = []): void
    {
        $extensions = $allowedExtensions ?: self::ALLOWED_EXTENSIONS;
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, $extensions, true)) {
            throw new \InvalidArgumentException(
                "File type '{$extension}' is not allowed. Allowed: " . implode(', ', $extensions)
            );
        }

        if ($file->getSize() > self::MAX_FILE_SIZE * 1024) {
            throw new \InvalidArgumentException(
                'File size exceeds maximum allowed size of ' . self::MAX_FILE_SIZE . ' KB'
            );
        }

        $mimeType = $file->getMimeType();
        $allowedMimes = [
            'application/pdf',
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip', 'application/x-rar-compressed',
            'text/plain', 'text/csv',
        ];

        if (!in_array($mimeType, $allowedMimes, true)) {
            throw new \InvalidArgumentException("MIME type '{$mimeType}' is not allowed");
        }
    }
}
