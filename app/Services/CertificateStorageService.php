<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateStorageService {
    private string $bucket = 'certificates';

    public function __construct() {
        $this->bucket = config('filesystems.disks.supabase.bucket', 'certificates');
    }

    /**
     * Upload certificate to Supabase Storage.
     *
     * @return string path to the uploaded file
     */
    public function uploadCertificate(UploadedFile $file): string {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        Storage::disk('supabase')->put($filename, $file->get());

        return $filename;
    }

    /**
     * Get signed URL for certificate.
     */
    public function getSignedUrl(string $path): string {
        return Storage::disk('supabase')->url($path);
    }

    /**
     * Delete certificate from storage.
     */
    public function deleteCertificate(string $path): bool {
        try {
            if (Storage::disk('supabase')->exists($path)) {
                return Storage::disk('supabase')->delete($path);
            }
        } catch (\Exception $e) {
            // If file doesn't exist or permission denied, just return false
            return false;
        }

        return false;
    }
}
