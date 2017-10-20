<?php

namespace MyTravel\Core\Output;

use ErrorException;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use MyTravel\Core\OutputInterface;
use MyTravel\Core\Service\Config;
use MyTravel\Core\Model\ImageDataBag;

class FileOutput implements OutputInterface {
  private $requestFormat;
  private $file;

  public function __construct(Request $request) {
    $this->requestFormat = $request->getRequestFormat();
  }

  /**
   *
   * @param GetResponseForControllerResultEvent $event Kernel event
   * @return Response Symfony Response object
   * @throws ErrorException
   */
  public function output(GetResponseForControllerResultEvent $event) {
    $this->file = $event->getControllerResult();
    $response = new Response();
    // Images
    switch ($this->file->getSupportedImageType()) {
      case image_type_to_mime_type(IMAGETYPE_JPEG):
        $this->jpegOutput($response);
        break;
      case image_type_to_mime_type(IMAGETYPE_PNG):
        $this->pngOutput($response);
        break;
      default:
        throw new ErrorException(sprintf('Unknown file format %s request.', $this->file->type));
    }
    //
    $response->headers->set('Content-Type', $this->file->type);
    // 
    return $response;
  }

  /**
   * Create new image from different source.
   * Can be cropped with crop attribute for imgdata parameter.
   * @param resource $input Image resource
   * @param ImageDataBag $imgdata Image output data
   * @return Updated image resource
   */
  private function createImage($input, ImageDataBag $imgdata) {
    $cropW = $imgdata->cw ?? $imgdata->w;
    $cropH = $imgdata->ch ?? $imgdata->h;
    $intermediate = imagecreatetruecolor($imgdata->w, $imgdata->h);
    $color = imagecolorallocate($intermediate, 255, 255, 255);
    imagestring($intermediate, 5, 5, 5, Config::get()->appname, $color);
    imagecopyresampled($intermediate, $input, 0, 0, 0, 0, $imgdata->w, $imgdata->h, $imgdata->ow, $imgdata->oh);
    if ($cropW != $imgdata->w || $cropH != $imgdata->h) {
      $output = imagecreatetruecolor($cropW, $cropH);
      $x = ($imgdata->w - $cropW) / 2;
      $y = ($imgdata->h - $cropH) / 2;
      imagecopyresampled($output, $intermediate, 0, 0, $x, $y, $cropW, $cropH, $cropW, $cropH);
      imagedestroy($intermediate);
    } else {
      $output = $intermediate;
    }
    imagedestroy($input);

    return $output;
  }

  /**
   * Create jpeg response.
   */
  private function jpegOutput(Response $response) {
    $output = $this->createImage(
      imagecreatefromjpeg($this->file->getFullSource()), $this->file->imgdata
    );
    // Do the jpeg thing
    ob_start();
    imagejpeg($output, NULL, $this->file->imgdata->q);
    imagedestroy($output);
    $size = ob_get_length();
    $img = ob_get_clean();
    // We need to set Content-Length for caching to work
    // For some reason the browser fails to apply it itself
    $response->headers->set('Content-Length', $size);
    $response->setContent($img);
  }

  /**
   * Create png response.
   */
  private function pngOutput(Response $response) {
    $output = $this->createImage(
      imagecreatefrompng($this->file->getFullSource()), $this->file->imgdata
    );
    // Do the jpeg thing
    ob_start();
    imagepng($output);
    imagedestroy($output);
    $size = ob_get_length();
    $img = ob_get_clean();
    // We need to set Content-Length for caching to work
    // For some reason the browser fails to apply it itself
    $response->headers->set('Content-Length', $size);
    $response->setContent($img);
  }

}
