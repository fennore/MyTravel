<?php

namespace MyTravel\Story;

use DOMDocument;
use ZipArchive;
use XMLReader;
use MyTravel\Core\Model\File;

/**
 * Reads content from documents.
 * Can read from .odt and .docx.
 *
 * 
 * @todo Support more file types
 * @todo WYSIWYG support, now strips all tags except <br>
 *
 */
class StoryReader {

  private $file;
  private $contentIdentifier;
  private $nsIdentifier;

  public function __construct(File $file) {
    $this->file = $file;
    switch ($this->file->type) {
      case 'application/vnd.oasis.opendocument.text':
        $this->contentIdentifier = 'content.xml';
        // xpath('text:p/*?/text()')
        $this->nsIdentifier = 'text';
        break;
      /**
       * @todo check why octet-stream happens on docx.
       * It's default MIME for unknown and could be anything.
       */
      case 'application/octet-stream':
      case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
        $this->contentIdentifier = 'word/document.xml';
        // xpath('w:p/*?/text()')
        $this->nsIdentifier = 'w';
        break;
    }
  }

  public function getContent() {
    if (!empty($this->contentIdentifier)) {
      return $this->readZippedXML();
    } else {
      // @todo inform about unsupported file type.
      return '';
    }
  }

  private function readZippedXML() {
    // Create new ZIP archive
    $zip = new ZipArchive();
    // Open received archive file
    $isOpen = $zip->open($this->file->getFullSource());
    // If done, search for the data file in the archive
    if ($isOpen === true) {
      $index = $zip->locateName($this->contentIdentifier);
    }
    // If found, read it to the string
    if ($isOpen === true && $index !== false) {
      $content = $zip->getFromIndex($index);
      // Close archive file
      $zip->close();
      // Load XML from a string
      // Skip errors and warnings
      $doc = new DOMDocument();
      $doc->loadXML($content, LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
      // Read XML data
      return $this->xmlToHtml($doc->saveXML());
    }

    // Close archive file
    $zip->close();

    // In case of failure return empty string
    return '';
  }

  /**
   * Convert an XML string to a simple HTML string.
   * @param string $xmlString
   * @return string
   */
  private function xmlToHtml($xmlString) {
    // Read XML string
    $reader = new XMLReader;
    $reader->xml($xmlString);

    // Initialize variables
    $text = '';
    $formatting['header'] = 0;

    // Loop through XML DOM
    while ($reader->read()) {
      // Look for new paragraphs
      $nodeTypeCheck = $reader->nodeType === XMLReader::ELEMENT;
      $nsCheck = $reader->name === $this->nsIdentifier . ':p';
      if (!$nodeTypeCheck || !$nsCheck) {
        continue;
      }
      $ns = $this->nsIdentifier;
      // Read paragraph outerXML
      $p = $reader->readOuterXML();

      // Search for heading
      preg_match('/<' . $ns . ':pStyle ' . $ns . ':val="Heading.*?([1-6])"/', $p, $matches);

      if (!empty($matches)) {
        $formatting['header'] = $matches[1];
      }

      // Open h-tag
      $text .= ($formatting['header'] > 0) ? '<h' . $formatting['header'] . '>' : '';
      // Concat text
      $text .= htmlentities(iconv('UTF-8', 'ASCII//TRANSLIT', $reader->expand()->textContent));
      // Close h-tag or add newline
      $text .= ($formatting['header'] > 0) ? '</h' . $formatting['header'] . '>' : '<br>';
    }
    $reader->close();

    // Suppress warnings. loadHTML does not require valid HTML but still warns against it...
    // Fixex invalid html.
    $doc = new DOMDocument();
    $doc->encoding = 'UTF-8';
    // Load as HTML without html/body and doctype
    @$doc->loadHTML($text, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    // @todo remove strip tags if allowed with contenteditable / wysiwyg implementation
    return strip_tags(simplexml_import_dom($doc)->asXML(), '<br>');
  }

}
