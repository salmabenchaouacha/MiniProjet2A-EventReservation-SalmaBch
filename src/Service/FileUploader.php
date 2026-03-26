<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    public function __construct(
        private string $targetDirectory
    ) {
    }

    public function upload(UploadedFile $file): string
    {
        $filename = uniqid().'.'.$file->guessExtension();
        $file->move($this->targetDirectory, $filename);

        return $filename;
    }
}