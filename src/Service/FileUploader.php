<?php

namespace App\Service;

use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class FileUploader
{
    private $slugger;
    private $targetPath;

    public function __construct(string $targetPath, SluggerInterface $sluggerInterface)
    {
        $this->targetPath = $targetPath;
        $this->slugger = $sluggerInterface;
    }

    public function upload(UploadedFile $uploadedFile)
    {
        $filename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $filename = $this->getTargetPath() . $this->slugger->slug($filename) . '.' . $uploadedFile->guessExtension();

        if ($uploadedFile) {
            try {
                move_uploaded_file($uploadedFile, $filename);
            } catch (FileException $e) {
                throw new FileException($e->getMessage());
            }
        }

        return $filename;
    }

    public function getTargetPath()
    {
        return $this->targetPath;
    }
}