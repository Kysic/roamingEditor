<?php

require_once('conf/google.php');
require_once('db/roamingSql.php');

function do_post_request($url, $data, $optional_headers = null) {
  $params = array('http' => array(
              'method' => 'POST',
              'content' => $data
            ));
  if ($optional_headers !== null) {
    $params['http']['header'] = $optional_headers;
  }
  $ctx = stream_context_create($params);
  $fp = @fopen($url, 'rb', false, $ctx);
  if (!$fp) {
    throw new Exception('Unable to call the remote service');
  }
  $response = @stream_get_contents($fp);
  if ($response === false) {
    throw new Exception('Unable to parse the remote service response');
  }
  return $response;
}

function generateSpreadSheets($roamingId) {
    $roamingJson = getRoamingJson($roamingId);
    $response = do_post_request(GOOGLE_SPREADSHEETS_GENERATOR, $roamingJson);
    $jsonResponse = json_decode($response);
    $docId = $jsonResponse->docId;
    if ( !$docId ) {
        throw new Exception('Unable to generate roaming report');
    }
    return $docId;
}

function getOrCreateDocId($roamingId, $userId) {
    $docId = getRoamingDocId($roamingId);
    if ( !$docId ) {
        $docId = generateSpreadSheets($roamingId);
        setRoamingDocId($roamingId, $docId, $userId);
    }
    return $docId;
}

function docIdToEditUrl($docId) {
    return GOOGLE_DOC_URL.$docId.GOOGLE_DOC_CMD_EDIT;
}

function docIdToPrintUrl($docId) {
    return GOOGLE_DOC_URL.$docId.GOOGLE_DOC_CMD_PDF;
}

