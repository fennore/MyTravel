<?php

namespace MyTravel\Core\Controller;

use ErrorException;
use DateTime;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use MyTravel\Core\OutputInterface;
use MyTravel\Core\Controller\Config;

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
    $response->headers->set('Content-Type', $this->file->type);
    switch ($this->file->type) {
      case 'image/jpeg':
        $this->jpegOutput($response, $event->getRequest()->attributes->get('trailing'));
        break;
      default:
        throw new ErrorException(sprintf('Unknown file format % request', array($file->type)));
    }
    return $response;
  }

  /**
   *
   * @param type $input
   * @param type $width
   * @param type $height
   * @param type $crop
   * @return type
   */
  private function createImage($input, $width, $height, $crop = array()) {
    isset($crop['w']) ?: $crop['w'] = $width;
    isset($crop['h']) ?: $crop['h'] = $height;
    $exif = $this->file->property->exif;
    $intermediate = imagecreatetruecolor($width, $height);
    $color = imagecolorallocate($intermediate, 255, 255, 255);
    imagestring($intermediate, 5, 5, 5, Config::get()->appname, $color);
    imagecopyresampled($intermediate, $input, 0, 0, 0, 0, $width, $height, $exif['COMPUTED']['Width'], $exif['COMPUTED']['Height']);
    if ($crop['w'] != $width || $crop['h'] != $height) {
      $output = imagecreatetruecolor($crop['w'], $crop['h']);
      $x = ($width - $crop['w']) / 2;
      $y = ($height - $crop['h']) / 2;
      imagecopyresampled($output, $intermediate, 0, 0, $x, $y, $crop['w'], $crop['h'], $crop['w'], $crop['h']);
      imagedestroy($intermediate);
    } else {
      $output = $intermediate;
    }
    imagedestroy($input);

    return $output;
  }
  /**
   *
   * @param type $widthLimit
   * @param type $heightLimit
   * @param type $doCrop
   * @return stdClass
   */
  private function calculateImageBounds($widthLimit, $heightLimit, $doCrop) {
    // Requested ratio
    $ratioDefault = $widthLimit / $heightLimit;
    // EXIF data
    $exif = $this->file->property->exif;
    $ratio = $exif['COMPUTED']['Width'] / $exif['COMPUTED']['Height'];
    // With crop set inner bounds
    if ($doCrop && $ratio >= $ratioDefault) {
      $width = $heightLimit * $ratio;
      $height = $heightLimit;
    } else if ($doCrop) {
      $width = $widthLimit;
      $height = $widthLimit / $ratio;
    }
    // Without crop set outer bounds
    else if ($ratio >= $ratioDefault) {
      $width = ($exif['COMPUTED']['Width'] > $widthLimit ? $widthLimit : $exif['COMPUTED']['Width']);
      $height = ($exif['COMPUTED']['Width'] > $widthLimit ? $widthLimit / $ratio : $exif['COMPUTED']['Height']);
    } else {
      $width = ($exif['COMPUTED']['Height'] > $heightLimit ? $heightLimit * $ratio : $exif['COMPUTED']['Width']);
      $height = ($exif['COMPUTED']['Height'] > $heightLimit ? $heightLimit : $exif['COMPUTED']['Height']);
    }
    return (object) array(
      'w' => $width,
      'h' => $height
    );
  }

  /**
   * @todo introduce image config settings?
   * And refactor into something proper.
   */
  private function jpegOutput(Response $response, $trailing = '') {
    // Set crop
    $crop = ($trailing === 'thumbnail') ? array('w' => 160, 'h' => 120) : array();
    // Bounds
    $heightLimit = ($trailing === 'thumbnail') ? 120 : 1080;
    $widthLimit = ($trailing === 'thumbnail') ? 160 : 1920;
    $bounds = $this->calculateImageBounds($widthLimit, $heightLimit, !empty($crop));
    // Set quality
    $quality = ($trailing === 'thumbnail') ? 75 : 84;
        
    $output = $this->createImage(imagecreatefromjpeg($this->file->getFullSource()), $bounds->w, $bounds->h, $crop);
    // Do the jpeg thing
    ob_start();
    imagejpeg($output, NULL, $quality);
    imagedestroy($output);
    $size = ob_get_length();
    $img = ob_get_clean();
    // We need to set Content-Length for caching to work
    // For some reason the browser fails to apply it itself
    $response->headers->set('Content-Length', $size);
    $response->setContent($img);
  }

}
