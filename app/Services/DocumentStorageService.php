<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentStorageService
{
    /**
     * Store a file in the private storage
     *
     * @param UploadedFile $file
     * @param string $path
     * @return string The relative path to the stored file
     */
    public function store(UploadedFile $file, string $path = 'documents'): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs($path, $filename, 'private');
    }

    /**
     * Get a temporary URL for a file
     *
     * @param string $path
     * @return string
     */
    public function getUrl(string $path): string
    {
        return Storage::disk('private')->temporaryUrl($path, now()->addMinutes(30));
    }

    /**
     * Download a file
     *
     * @param string $path
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(string $path)
    {
        return Storage::disk('private')->download($path);
    }

    /**
     * Delete a file
     *
     * @param string $path
     * @return bool
     */
    public function delete(string $path): bool
    {
        if (Storage::disk('private')->exists($path)) {
            return Storage::disk('private')->delete($path);
        }
        return false;
    }
}
