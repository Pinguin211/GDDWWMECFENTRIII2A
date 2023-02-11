<?php

namespace App\Service;

class CheckerInterface
{
    public function checkUploadedFile(array $file, int $maxSize, array $extensions, array $mimeType): bool
    {
        if (!$this->checkData($file, 'array', ['name', 'full_path', 'type', 'tmp_name', 'error', 'size']))
            return false;
        if ($file['size'] > $maxSize)
            return false;
        if (!in_array($file['type'], $mimeType, true))
        return false;
        foreach ($extensions as $extension)
        {
            if (str_contains($file['name'], $extension))
                return true;
        }
        return false;
    }


    public function checkData(mixed $data, string $type, array $array_values = []): bool
    {
        if (empty($data))
            return false;
        switch ($type) {
            case 'int':
                if (!is_int($data))
                    return false;
                break;
            case 'string':
                if (!is_string($data))
                    return false;
                break;
            case 'numeric':
                if (!is_numeric($data))
                    return false;
                break;
            case 'array':
                if (!is_array($data))
                    return false;
                foreach ($array_values as $value)
                {
                    if (!isset($data[$value]))
                        return false;
                }
                break;
            default:
                return false;
        }
        return true;
    }
}