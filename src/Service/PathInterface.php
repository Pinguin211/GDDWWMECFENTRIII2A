<?php

namespace App\Service;

use App\Kernel;

class PathInterface
{
    private Kernel $kernel;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    public function getProjectDirPath(): string
    {
        return $this->kernel->getProjectDir() . '/';
    }

    public function getSourceDirPath(bool $create_path = true): string
    {
        return $this->getPath('getProjectDirPath', 'sources/', $create_path);
    }

    public function getPublicDirPath(bool $create_path = true): string
    {
        return $this->getPath('getProjectDirPath', 'public/', $create_path);
    }

    public function getUserCvDirPath(bool $create_path = true): string
    {
        return $this->getPath('getPublicDirPath', 'cv/', $create_path);
    }

    private function getPath(string $start_func, string $end, bool $create_path): string
    {
        $path = $this->$start_func() . $end;
        if ($create_path)
            self::createPath($path);
        return $path;
    }

    private static function createPath(string $path)
    {
        if (!file_exists($path))
            mkdir($path, 0777, true);
    }
}