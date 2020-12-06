<?php

class Reporting115 {

  private $reportsStorage;

  public function __construct($reportsStorage) {
    $this->reportsStorage = $reportsStorage;
  }

  public function extractFromMailStdin() {
    $mailFile = ROAMING_TMP_DIR.'/mail-'.uniqid().'.eml';
    copy("php://stdin", $mailFile);
    $this->extractFromMailFile($mailFile);
    unlink($mailFile);
  }

  public function extractFromMailFile($mailFile) {
    $attachmentDir = ROAMING_TMP_DIR.'/attachment-'.uniqid();
    // extract mail attachment
    exec("ripmime -i $mailFile -d $attachmentDir", $output, $result);
    if ($result !== 0) {
      throw new Exception('Error while executing ripmime '.$result);
    }

    $objects = scandir($attachmentDir);
    foreach ($objects as $object) {
      if ($object != "." && $object != "..") {
        $file = $attachmentDir. DIRECTORY_SEPARATOR .$object;
        if (is_file($file)) {
          if (stripos($file, '.xlsx')) {
            $this->extractFromXlsxFile($file);
          }
          unlink($file);
        }
      }
    }
    rmdir($attachmentDir);
  }

  public function extractFromXlsxFile($xslxFile) {
    $csvFile = ROAMING_TMP_DIR.'/signalements-'.uniqid().'.csv';
    exec("xlsx2csv \"$xslxFile\" > $csvFile", $output, $result);
    if ($result !== 0) {
      throw new Exception('Error while executing xlsx2csv '.$result);
    }
    $this->extractFromCsvFile($csvFile);
    unlink($csvFile);
  }

  public function extractFromCsvFile($csvFile) {
    $csvArray = array_map('str_getcsv', file($csvFile));
    $reports = $this->formatCsvArray($csvArray);
    $this->reportsStorage->add($reports);
  }

  public function formatCsvArray($csvArray) {
    $headers = $this->extractHeaders($csvArray);
    array_walk($csvArray, [$this, 'combineArrayRow'], $headers);
    $reports = json_encode($csvArray);
    return $reports;
  }

  private function combineArrayRow(&$row, $key, $header) {
    $row = array_combine($header, $row);
  }

  private function extractHeaders(&$csvArray) {
    while (count($csvArray) > 0) {
      $headers = array_map(array($this, 'cleanUpHeader'), array_shift($csvArray));
      if (
        in_array('nom', $headers) && in_array('prenom', $headers) && in_array('telephone', $headers)
        && in_array('lieu', $headers) && in_array('besoins', $headers)
      ) {
        return $headers;
      }
    }
    throw new Exception('Error, array headers not found in report');
  }

  private function cleanUpHeader($header) {
    return str_replace(['é'], ['e'], strtolower(trim($header)));
  }

}

