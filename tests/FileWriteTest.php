<?php

use PHPUnit\Framework\TestCase;

class FileWriteTest extends TestCase
{
    /**
     * @group writable
     * @group production
     * @covers ::file_put_contents
     */
    public function testWritableFolder()
    {
        $writableDirectories = [
            ['path' => LOGS_PATH, 'label' => 'Logs directory', 'create' => true, 'write' => true],
            ['path' => CACHE_PATH, 'label' => 'Cache directory', 'create' => true, 'write' => true],
            ['path' => CONTENT_PATH, 'label' => 'Content directory', 'create' => false, 'write' => true],
            ['path' => CONTENT_PATH . 'files', 'label' => 'Files directory', 'create' => true, 'write' => true],
            ['path' => CONTENT_PATH . 'img', 'label' => 'Images directory', 'create' => true, 'write' => true],
            ['path' => CONTENT_PATH . 'img/temp', 'label' => 'Images temp directory', 'create' => true, 'write' => true],
        ];
        
        if ($path = ini_get('session.save_path')) {
            $writableDirectories[] = ['path' => $path, 'label' => 'PHP session save path', 'create' => false, 'write' => true];
        }

        foreach ($writableDirectories as $writableDirectory) {
            if (ends_with($writableDirectory['path'], DIRECTORY_SEPARATOR)) {
                $writableDirectory['path'] = substr($writableDirectory['path'], 0, -1);
            }

            if (!$writableDirectory['write'] && file_exists($writableDirectory['path'])) {
                $this->assertTrue(is_readable($writableDirectory['path']), sprintf('Directory %s (%s) is not redeable.', $writableDirectory['path'], $writableDirectory['label']));
            } else {
                if ($writableDirectory['create'] && !file_exists($writableDirectory['path'])) {
                    @mkdir($writableDirectory['path'], 0755, true);
                    $this->assertTrue(file_exists($writableDirectory['path']), sprintf('Directory %s (%s) does not exists and could not be created.', $writableDirectory['path'], $writableDirectory['label']));
                } elseif (file_exists($writableDirectory['path'])) {
                    $this->assertTrue(is_dir($writableDirectory['path']), sprintf('Directory %s (%s) exists but is not a directory.', $writableDirectory['path'], $writableDirectory['label']));
                } elseif (!$writableDirectory['create']) {
                    $this->assertTrue(is_dir($writableDirectory['path']), sprintf('Directory %s (%s) should exist but does not.', $writableDirectory['path'], $writableDirectory['label']));
                }

                $this->assertTrue(is_writable($writableDirectory['path']), sprintf('Directory %s (%s) is not writable.', $writableDirectory['path'], $writableDirectory['label']));

                $test_basename = 'writetest-' . md5($writableDirectory['path'] . DIRECTORY_SEPARATOR);
                $test_file = $writableDirectory['path'] . DIRECTORY_SEPARATOR . $test_basename . '.txt';
                $test_folder = $writableDirectory['path'] . DIRECTORY_SEPARATOR . $test_basename . '';
                clearstatcache();
                (is_dir($test_folder)) && rmdir($test_folder);
                clearstatcache();
                (is_file($test_file)) && unlink($test_file);
                clearstatcache();

                $this->assertNotFalse(file_put_contents($test_file, 'Test content'));
                $this->assertTrue(unlink($test_file));
                $this->assertTrue(mkdir($test_folder, 0755, true));
                $this->assertTrue(rmdir($test_folder));
            }
        }
    }
}
