<?php

namespace App\Core\Utils;

/**
 * Class Curl
 *
 * @package App\Core\Utils
 */
class Curl
{
    /**
     * Downloads file and saves it to specific path.
     *
     * @param string     $url      Url of the file.
     * @param string     $filePath Location where file should be saved.
     * @param array|null $headers  Additional headers to be passed with request.
     *
     * @return bool True if there were no errors during download or false otherwise.
     */
    public static function downloadFile(string $url, string $filePath, array $headers = null)
    {
        $file = fopen($filePath, 'w+');
        chmod($filePath, 01775);

        $options = [
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_USERAGENT      => 'Mozilla/5.0',
            CURLOPT_FAILONERROR    => true,
            CURLOPT_BINARYTRANSFER => true,
            CURLOPT_HEADER         => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FILE           => $file,
        ];
        if (!empty($headers)) {
            $options[CURLOPT_HTTPHEADER] = $headers;
        }

        $ch = curl_init($url);
        foreach ($options as $option => $value) {
            curl_setopt($ch, $option, $value);
        }
        curl_exec($ch);

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errors = curl_errno($ch);
        curl_close($ch);

        return $code === 200 && !$errors;
    }
}
