<?php

namespace MyTravel\Core\Model;

class ImageDataBag {
  /**
   * Image new width
   * @var int
   */
  public $w;

  /**
   * Image new height
   * @var int
   */
  public $h;

  /**
   * Image original width
   * @var int
   */
  public $ow;

  /**
   * Image original height
   * @var int
   */
  public $oh;

  /**
   * Image Crop width
   * @var int
   */
  public $cw;

  /**
   * Image Crop height
   * @var int 
   */
  public $ch;

  /**
   * Image Quality.
   * Used for JPEG only.
   * @var int
   */
  public $q;

  /**
   * Updates ImageDataBag with correct width and height,
   * according to given requested width and height,
   * keeping original file ratios, or with crop.
   */
  public function keepRatio() {
    // Requested ratio
    $requestRatio = $this->w / $this->h;
    // Original ratio
    $ratio = $this->ow / $this->oh;
    // Check crop
    $doCrop = isset($this->cw) || isset($this->ch);
    // With crop set inner bounds
    if ($doCrop && $ratio >= $requestRatio) {
      $this->w = $this->h * $ratio;
    } else if ($doCrop) {
      $this->h = $this->w / $ratio;
    }
    // Without crop set outer bounds
    else if ($ratio >= $requestRatio) {
      $this->w = ($this->ow > $this->w ? $this->w : $this->ow);
      $this->h = ($this->ow > $this->w ? $this->w / $ratio : $this->oh);
    } else {
      $this->w = ($this->oh > $this->h ? $this->h * $ratio : $this->ow);
      $this->h = ($this->oh > $this->h ? $this->h : $this->oh);
    }
  }

}
