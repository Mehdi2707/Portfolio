<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadService
{
    private  $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function add(UploadedFile $file, ?string $folder = '')
    {
        $newFile = md5(uniqid(rand(),true)) . '.' . $file->getClientOriginalExtension();

        $path = $this->params->get('images_directory').$folder;

        if(!file_exists($path))
            mkdir($path, 0755, true);

        $file->move($path.'/', $newFile);

        return $newFile;
    }

    public function delete(string $fichier, ?string $folder = '', ?int $width = 250, ?int $height = 250)
    {
        if($fichier !== 'default.webp')
        {
            $success = false;
            $path = $this->params->get('images_directory').$folder;

            $mini = $path.'/mini/'.$width.'x'.$height.'-'.$fichier;

            if(file_exists($mini))
            {
                unlink($mini);
                $success = true;
            }

            $original = $path.'/'.$fichier;

            if(file_exists($original))
            {
                unlink($original);
                $success = true;
            }
            return $success;
        }
        return false;
    }
}