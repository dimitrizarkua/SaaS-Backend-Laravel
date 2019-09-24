<?php

namespace App\Utils;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Class FileIO
 *
 * @package App\Utils
 */
class FileIO
{
    /**
     * @param string|null $fileName
     *
     * @return string
     */
    public static function getTmpFilePath(string $fileName = null): string
    {
        $tmpDir = 'app' . DIRECTORY_SEPARATOR . 'tmp';
        File::makeDirectory(storage_path($tmpDir), 0755, true, true);

        if (!$fileName) {
            $fileName = (string)Str::uuid();
        }

        return storage_path($tmpDir . DIRECTORY_SEPARATOR . $fileName);
    }
}
