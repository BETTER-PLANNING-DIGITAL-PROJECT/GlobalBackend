<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\UrlHelper;

class InstitutionFileUploader
{
    private $uploadPath;
    private $slugger;
    private $urlHelper;
    private $relativeUploadsDir;

    public function __construct($publicPath, $uploadPath, SluggerInterface $slugger, UrlHelper $urlHelper)
    {
        $this->uploadPath = $uploadPath;
        $this->slugger = $slugger;
        $this->urlHelper = $urlHelper;

        // get uploads directory relative to public path //  "/uploads/"
        $this->relativeUploadsDir = str_replace($publicPath, '', $this->uploadPath).'/';
    }

    public function deleteUpload(?string $existingFilename = null){
        // Check if we have an old image
        if ($existingFilename) {
            try {
                unlink($this->getuploadPath().'/'.$existingFilename);
            } catch (\Exception $e) {
                // ... handle exception if something happens during file deleting
            }
        }
    }

    public function upload(UploadedFile $file, ?string $existingFilename = null)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($this->getuploadPath(), $newFilename);
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }

        // Check if we have an old image
        if ($existingFilename) {
            try {
                if ($existingFilename !== '7.jpg'){
                    unlink($this->getuploadPath().'/'.$existingFilename);
                }

            } catch (\Exception $e) {
                // ... handle exception if something happens during file deleting
            }
        }

        return $newFilename;

    }

    public function getuploadPath()
    {
        return $this->uploadPath;
    }

    public function getUrl(?string $fileName, bool $absolute = true)
    {
        if (empty($fileName)) return null;

        if ($absolute) {
            return $this->urlHelper->getAbsoluteUrl($this->relativeUploadsDir.$fileName);
        }

        return $this->urlHelper->getRelativePath($this->relativeUploadsDir.$fileName);
    }

}
