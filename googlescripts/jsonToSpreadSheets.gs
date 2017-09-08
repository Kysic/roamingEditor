function doPost(request) {
  revokeEditPermissions();
  var roaming = JSON.parse(request.postData.getDataAsString());
  var crFile = createRoamingCR(roaming);
  var responseJson = JSON.stringify({ docId: crFile.getId(), docUrl: crFile.getUrl() });
  return ContentService.createTextOutput(responseJson).setMimeType(ContentService.MimeType.JSON);
}

function revokeEditPermissions() {
  var folder = DriveApp.getFolderById("0000000000000000000000000000");
  var files = folder.getFiles();
  while (files.hasNext()) {
    var file = files.next();
    if (file.getSharingAccess() == DriveApp.Access.ANYONE_WITH_LINK && file.getSharingPermission() == DriveApp.Permission.EDIT) {
      file.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW);
    }
  }
}

function createRoamingCR(roaming) {
  var crFile = createNewCRFromTemplate(roaming.date);
  var crSpreadSheep = SpreadsheetApp.open(crFile);
  var sheet = crSpreadSheep.getSheets()[0];
  fillRoamingSheet(roaming, sheet);
  crFile.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.EDIT);
  return crFile;
}

function createNewCRFromTemplate(roamingDate) {
  var template = DriveApp.getFileById('aaaaaaaaaaaaaaaa-aaaaaaaaaaaaaaaaaaaaaaa_aaa');
  return template.makeCopy('CR_' + roamingDate);
}

function fillRoamingSheet(roaming, sheet) {
  sheet.getRange('c1').setValue(roaming.date);
  sheet.getRange('c2').setValue(roaming.vehicle);
  sheet.getRange('c3').setValue(roaming.teammates.join(', ') + ' et ' + roaming.tutor);
  for (var i = 0; i < roaming.interventions.length; i++) {
    var intervention = roaming.interventions[i];
    var line = i + 15;
    sheet.getRange('a' + line).setValue(intervention.people.join(', '));
    sheet.getRange('c' + line).setValue(intervention.location);
    sheet.getRange('d' + line).setValue(intervention.time);
    sheet.getRange('e' + line).setValue(intervention.source);
    sheet.getRange('f' + line).setValue(intervention.nbAdults);
    sheet.getRange('g' + line).setValue(intervention.nbChildren);
    sheet.getRange('h' + line).setValue(intervention.blankets);
    sheet.getRange('i' + line).setValue(intervention.tents);
    sheet.getRange('j' + line).setValue(intervention.comments);
  }
}

