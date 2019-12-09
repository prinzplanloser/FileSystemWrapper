<?php


class FileWrapper
{
    private $pathToFile;

    public function __construct(string $path)
    {
        $this->pathToFile = $path . '\\';
    }

    private function deleteFile(string $name): ?bool
    {
        $delete = unlink($this->fullPath($name));
        if ($delete) {
            return true;
        } else {
            throw new Exception('Ошибка удаления файла,проверьте передаваемые параметры');
        }
    }

    private function deleteDirectory(string $name): ?bool
    {
        $delete = rmdir($this->pathToFile . $name);
        if ($delete) {
            return true;
        } else {
            throw new Exception('Ошибка удаления директории,проверьте передаваемые параметры');
        }
    }

    public function delete(string $name): ?bool
    {
        if (is_dir($name)) {
            try {
                $result = $this->deleteDirectory($name);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        } else {
            try {
                $result = $this->deleteFile($name);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
        return $result;
    }

    public function scan(): ?array
    {
        $files = array_values(array_diff(scandir($this->pathToFile), ['..', '.']));
        if ($files === false) {
            throw new Exception('Ошибка сканирования директории');
        } else {
            return $files;
        }
    }

    public function rename(string $oldName, string $newPath, string $newName)
    {
        $rename = rename($this->pathToFile . $oldName, $newPath . '\\' . $newName);
        if ($rename) {
            return true;
        } else {
            throw new Exception('Произошла ошибка при попытке переименовыания');
        }
    }

    public function setPath(string $path): void
    {
        $this->pathToFile = $path . '\\';
    }

    public function downloadFileWithUrl(string $url, string $name)
    {
        $path = $this->fullPath($name);
        file_put_contents($path, file_get_contents($url));
    }

    public function downloadFileWithCurl(string $url, string $name)
    {
        $ch = curl_init($url);
        $fp = fopen($this->pathToFile . $name, 'wb');
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
        if ($result) {
            return $result;
        } else {
            throw new Exception('Произошла ошибка при чтении файла в массив');
        }
    }

    public function createDirectory(string $name, int $mode = 0777)
    {
        $path = $this->fullPath($name);
        $dir = mkdir($path, $mode);
        if ($dir) {
            return true;
        } else {
            throw new Exception('Произошла ошибка при создании директории');
        }
    }

    public function changeFileMode(string $name, int $mode)
    {
        $path = $this->fullPath($name);
        $newMode = chmod($path, $mode);
        if ($newMode) {
            return true;
        } else {
            throw new Exception('Произошла ошибка при попытке изменения режима доступа к файлу');
        }
    }

    public function createFile(string $name, $content = '')
    {
        $path = $this->fullPath($name);
        if (!file_exists($path)) {
            $fp = fopen($path, 'w');
            fwrite($fp, $content);
            fclose($fp);
        } else {
            throw new Exception('Файл уже существует');
        }
    }

    private function fullPath(string $name): string
    {
        $path = $this->pathToFile . $name;
        return $path;
    }
}

