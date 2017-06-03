<?php

require_once('db/sql.php');

define('ROAMINGS_TABLE', 'vcr_roamings');

function addRoaming($roaming, $userId) {
    $query = 'INSERT INTO '.ROAMINGS_TABLE.' (roamingDate, version, creationUserId, rawJson)'.
             ' VALUES (:roamingDate, :version, :creationUserId, :rawJson)';
    $request = getPdo()->prepare($query);
    $request->bindValue(':roamingDate', $roaming->date, PDO::PARAM_STR);
    $request->bindValue(':version', $roaming->version, PDO::PARAM_INT);
    $request->bindValue(':creationUserId', $userId, PDO::PARAM_INT);
    $request->bindValue(':rawJson', json_encode($roaming), PDO::PARAM_STR);
    executeWithException($request);
}

function getRoamings($beginDate, $endDate) {
    $query = 'SELECT roamingId, rawJson FROM '.ROAMINGS_TABLE.' r1'.
             ' WHERE :beginDate <= r1.roamingDate AND r1.roamingDate <= :endDate'.
             ' AND r1.version = (select max(r2.version) from '.ROAMINGS_TABLE.' r2 where r1.roamingDate = r2.roamingDate)';
    $request = getPdo()->prepare($query);
    $request->bindValue(':beginDate', $beginDate, PDO::PARAM_STR);
    $request->bindValue(':endDate', $endDate, PDO::PARAM_STR);
    executeWithException($request);
    return $request->fetchAll(PDO::FETCH_FUNC|PDO::FETCH_GROUP|PDO::FETCH_UNIQUE, 'json_decode');
}

function getRoamingDocId($roamingId) {
    $query = 'SELECT docId FROM '.ROAMINGS_TABLE.' WHERE roamingId = :roamingId';
    $request = getPdo()->prepare($query);
    $request->bindValue(':roamingId', $roamingId, PDO::PARAM_INT);
    executeWithException($request);
    $roaming = $request->fetch(PDO::FETCH_OBJ);
    if ( !$roaming ) {
        throw new NotFoundException('No roaming found with id ' . $roamingId);
    }
    return $roaming->docId;
}

function getRoamingDate($roamingId) {
    $query = 'SELECT roamingDate FROM '.ROAMINGS_TABLE.' WHERE roamingId = :roamingId';
    $request = getPdo()->prepare($query);
    $request->bindValue(':roamingId', $roamingId, PDO::PARAM_INT);
    executeWithException($request);
    $roaming = $request->fetch(PDO::FETCH_OBJ);
    if ( !$roaming ) {
        throw new NotFoundException('No roaming found with id ' . $roamingId);
    }
    return $roaming->roamingDate;
}

function getRoamingJson($roamingId) {
    $query = 'SELECT rawJson FROM '.ROAMINGS_TABLE.' WHERE roamingId = :roamingId';
    $request = getPdo()->prepare($query);
    $request->bindValue(':roamingId', $roamingId, PDO::PARAM_INT);
    executeWithException($request);
    $roaming = $request->fetch(PDO::FETCH_OBJ);
    if ( !$roaming ) {
        throw new NotFoundException('No roaming found with id ' . $roamingId);
    }
    return $roaming->rawJson;
}

function setRoamingDocId($roamingId, $docId, $userId) {
    $query = 'UPDATE '.ROAMINGS_TABLE.
             ' SET docId=:docId, generationDate=NOW(), generationUserId=:generationUserId'.
             ' WHERE roamingId=:roamingId';
    $request = getPdo()->prepare($query);
    $request->bindValue(':roamingId', $roamingId, PDO::PARAM_INT);
    $request->bindValue(':docId', $docId, PDO::PARAM_STR);
    $request->bindValue(':generationUserId', $userId, PDO::PARAM_INT);
    executeWithException($request);
    return $request->errorCode();
}

