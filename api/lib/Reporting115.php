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
    exec("grep '^From: ' $mailFile | sed 's/From: //'", $from);
    exec("grep '^Subject: ' $mailFile | sed 's/Subject: //'", $subject);
    unlink($mailFile);
    return array(@$from[0], @$subject[0]);
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
    $csvArray = array_filter($csvArray, [$this, 'hasNomPrenomTelephoneOrLieu']);
    $this->normalizeReportPhoneNumber($csvArray);
    $reports = json_encode($csvArray);
    return $reports;
  }

  private function normalizeReportPhoneNumber(&$reports) {
    foreach ($reports as &$report) {
      if (@$report['telephone']) {
        $report['telephone'] = $this->normalizePhoneNumber($report['telephone']);
      }
    }
  }

  public function normalizePhoneNumber($phone) {
    if (preg_match("/^[0-9]{9}$/", $phone)) {
      // handle 601020304 => 0601020304
      $phone = '0' . $phone;
    } else if (strlen($phone) == 12 && substr($phone, -2) === 'E8' && substr($phone, 1, 1) === '.') {
      // handle 6.01020304E8 => 0601020304
      $phone = '0' . substr($phone, 0, 1) . substr($phone, 2, 8);
    }
    // format with space every 2 digits if french number
    $phoneNoSpaces = str_replace(' ', '', $phone);
    if (strlen($phoneNoSpaces) == 10 && substr($phone, 0, 1) === '0') {
      $phone = wordwrap($phoneNoSpaces, 2, ' ', true);
    }
    return $phone;
  }

  private function combineArrayRow(&$row, $key, $header) {
    $row = @array_combine($header, $row);
  }

  private function hasNomPrenomTelephoneOrLieu($row) {
    return trim($row['nom']) || trim($row['prenom']) || trim($row['telephone']) || trim($row['lieu']);
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

