<?php

namespace App\Support;

use Illuminate\Http\Request;

trait UploadsFiles
{
    protected function storeUpload(Request $request, string $field, string $directory): ?string
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        return $request->file($field)->store($directory, 'public');
    }
}
