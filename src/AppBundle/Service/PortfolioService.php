<?php
/**
 * Created by PhpStorm.
 * User: jobvink
 * Date: 05-11-17
 * Time: 20:26
 */

namespace AppBundle\Service;


use AppBundle\Entity\PortfolioInterface;
use Doctrine\ORM\Mapping\Entity;
use ErrorException;
use http\Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PortfolioService
{

    public function storeAjaxFile(PortfolioInterface $entity, $data, $basepath) {
        $name = $entity->getAttachmentName();
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
        });

        list($type, $data) = explode(';', $data);
        list(, $data) = explode(',', $data);
        $data = base64_decode($data);
        $fileName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $name) . '.' . explode('/', $type)[1];

        try {
            if (file_exists($basepath . $name)) {
                unlink($basepath . $name);
            }
        } catch (ErrorException $exception) {

        }

        if (!file_exists($basepath)) {
            mkdir($basepath, 0777, true);
        }
        // Verplaats het bestand naar de map waar de afbeeldingen opgeslagen worden
        file_put_contents($basepath . $fileName, $data);

        $entity->setAttachment($fileName);
    }
    
    public function storeFile(PortfolioInterface $entity, $basepath) {
        // $file slaat de geuploadde afbeelding op
        /** @var UploadedFile $file */
        $file = $entity->getAttachment();

        // genereer een unique naam voor het bestand voor het opgeslagen wordt
        $fileName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $entity->getAttachmentName()) . '.' . $file->guessExtension();
        if (!file_exists($basepath)) {
            mkdir($basepath, 0777, true);
        }
        // Verplaats het bestand naar de map waar de afbeeldingen opgeslagen worden
        $file->move(
            $basepath,
            $fileName
        );

        $entity->setAttachment($fileName);
    }
    
}