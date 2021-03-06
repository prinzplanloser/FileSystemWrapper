<?php

namespace App\Services;

use App\Exceptions\FileWrapperException;
use FilesystemIterator;

class FileWrapper
{
    private $pathToFiles;

    public function __construct(string $path)
    {
        $this->pathToFiles = $path . '\\';
    }

    private function deleteFile(string $name): ?bool
    {
        $delete = unlink($this->fullPath($name));
        if (!$delete) {
            throw new FileWrapperException('Ошибка удаления файла,проверьте передаваемые параметры');
        }
        return true;
    }

    private function deleteDirectory(string $dir)
    {
        $includes = new FilesystemIterator($dir);
        foreach ($includes as $include) {
            if (is_dir($include) && !is_link($include)) {
                $this->deleteDirectory($include);
                continue;
            }
            unlink($include);
        }
        $delete = rmdir($dir);
        if (!$delete) {
            throw new FileWrapperException('Ошибка удаления директории,проверьте передаваемые параметры');
        }
        return true;
    }

    public function delete(string $name): ?bool
    {
        if (is_dir($name)) {
            try {
                $result = $this->deleteDirectory($name);
            } catch (FileWrapperException $e) {
                echo $e->getMessage();
            }
        }
        try {
            $result = $this->deleteFile($name);
        } catch (FileWrapperException $e) {
            echo $e->getMessage();
        }
        return $result;
    }

    public function scan(): ?array
    {
        $files = array_values(array_diff(scandir($this->pathToFiles), ['..', '.']));
        if ($files === null) {
            throw new FileWrapperException('Ошибка сканирования директории');
        }
        return $files;
    }

    public function rename(string $oldName, string $newPath, string $newName)
    {
        $rename = rename($this->pathToFiles . $oldName, $newPath . '\\' . $newName);
        if (!$rename) {
            throw new FileWrapperException('Произошла ошибка при попытке переименовывания');
        }
        return $rename;
    }

    public function setPath(string $path): void
    {
        $this->pathToFiles = $path . '\\';
    }

    public function downloadFileWithUrl(string $url, string $name)
    {
        $path = $this->fullPath($name);
        file_put_contents($path, file_get_contents($url));
    }

    public function downloadFileWithCurl(string $url, string $name)
    {
        $ch = curl_init($url);
        $fp = fopen($this->pathToFiles . $name, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
    }

    public function fileToArray(string $name): array
    {
        $path = $this->fullPath($name);
        $result = file($path);
        if (!$result) {
            throw new FileWrapperException('Произошла ошибка при чтении файла в массив');
        }
        return $result;
    }

    public function createDirectory(string $name, int $mode = 0777)
    {
        $path = $this->fullPath($name);
        $dir = mkdir($path, $mode);
        if (!$dir) {
            throw new FileWrapperException('Произошла ошибка при создании директории');
        }
        return true;
    }

    public function changeFileMode(string $name, int $mode)
    {
        $path = $this->fullPath($name);
        $newMode = chmod($path, $mode);
        if ($newMode) {
            throw new FileWrapperException('Произошла ошибка при попытке изменения режима доступа к файлу');
        }
        return true;
    }

    public function createFile(string $name, $content = '')
    {
        $path = $this->fullPath($name);
        if (file_exists($path)) {
            throw new FileWrapperException('Файл уже существует');
        }
        $fp = fopen($path, 'w');
        fwrite($fp, $content);
        fclose($fp);
    }

    private function fullPath(string $name): string
    {
        $pathWithName = $this->pathToFiles . $name;

        return $pathWithName;
    }
}

