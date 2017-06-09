<?php

function getPlanning($browser, $roamingDate) {
    return $browser->get(END_POINT.'/api/getPlanning.php?roamingDate='.$roamingDate);
}

function getRoamings($browser) {
    return $browser->get(END_POINT.'/api/getRoamings.php');
}

function saveRoaming($browser, $roaming) {
    return $browser->post(END_POINT.'/api/saveRoaming.php', (object) array('roaming'=>$roaming));
}

function getDocUrl($browser, $roamingId) {
    return $browser->get(END_POINT.'/api/getDocUrl.php?roamingId='.$roamingId);
}

function getPdf($browser, $roamingId) {
    return $browser->get(END_POINT.'/api/getPdf.php?roamingId='.$roamingId);
}


function generateRoamingReport($roamingDate, $roamingVersion) {
    $report = <<<EOTEOT
{
  "date": "$roamingDate",
  "tutor": "Bernard D",
  "volunteers": [
    "Yves S.",
    "Flore M",
    "Lyse B"
  ],
  "vehicle": "2",
  "interventions": [
    {
      "time": "21:50",
      "location": "Gare",
      "people": [
        "Jean",
        "Paul"
      ],
      "source": "115",
      "nbAdults": 2,
      "nbChildren": 0,
      "blankets": 0,
      "tents": 0,
      "comments": "DIACA"
    },
    {
      "time": "22:05",
      "location": "CHU",
      "people": [
        "Albert"
      ],
      "source": "Maraude",
      "nbAdults": 1,
      "nbChildren": 0,
      "blankets": 1,
      "tents": 0,
      "comments": ""
    }
  ],
  "version": $roamingVersion,
  "synchroStatus": "SYNCHRONIZED"
}
EOTEOT;
    return json_decode($report);
}


