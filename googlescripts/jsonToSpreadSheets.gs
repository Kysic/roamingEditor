/**
Script properties :
reportFolderId = "0000000000000000000000000000"
crTemplateId = "0000000000000000000000000000"
*/

/** Called every month */
function monthlyCleanUp() {
  revokeReadPermissions();
  sortLastMonthReportInFolder();
}

function sortLastMonthReportInFolder() {
  var reportFolder = DriveApp.getFolderById(PropertiesService.getScriptProperties().getProperty('reportFolderId'));
  var lastMonth = new Date();
  lastMonth.setMonth(lastMonth.getMonth() - 1);
  var lastMonthFolderName = lastMonth.toISOString().substring(0, 7);
  var lastMonthYearFolderName = lastMonthFolderName.substring(0, 4);
  var yearFolders = reportFolder.getFoldersByName(lastMonthYearFolderName);
  var yearFolder;
  if (yearFolders.hasNext()) {
    yearFolder = yearFolders.next();
  } else {
    yearFolder = reportFolder.createFolder(lastMonthYearFolderName);
  }
  var monthFolders = yearFolder.getFoldersByName(lastMonthFolderName);
  var monthFolder;
  if (monthFolders.hasNext()) {
    monthFolder = monthFolders.next();
  } else {
    monthFolder = yearFolder.createFolder(lastMonthFolderName);
  }
  var files = reportFolder.getFiles();
  while (files.hasNext()) {
    var file = files.next();
    if (file.getName().indexOf('CR_' + lastMonthFolderName) === 0) {
      monthFolder.addFile(file);
      reportFolder.removeFile(file);
      if (file.getSharingAccess() == DriveApp.Access.ANYONE_WITH_LINK && file.getSharingPermission() == DriveApp.Permission.EDIT) {
        file.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW);
      }
    }
  }
}

function revokeReadPermissions() {
  var reportFolder = DriveApp.getFolderById(PropertiesService.getScriptProperties().getProperty('reportFolderId'));
  var fourMonthsAgo = new Date();
  fourMonthsAgo.setMonth(fourMonthsAgo.getMonth() - 4);
  var fourMonthsAgoFolderName = fourMonthsAgo.toISOString().substring(0, 7);
  Logger.log('Revoking read permissions on ' + fourMonthsAgoFolderName);
  var yearFolders = reportFolder.getFoldersByName(fourMonthsAgoFolderName.substring(0, 4));
  if (yearFolders.hasNext()) {
    var monthFolders = yearFolders.next().getFoldersByName(fourMonthsAgoFolderName);
    if (monthFolders.hasNext()) {
      var files = monthFolders.next().getFiles();
      while (files.hasNext()) {
        var file = files.next();
        file.setSharing(DriveApp.Access.ANYONE, DriveApp.Permission.NONE);
        file.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.NONE);
      }
    }
  }
}

/** Called every day */
function revokeEditPermissions() {
  var folder = DriveApp.getFolderById(PropertiesService.getScriptProperties().getProperty('reportFolderId'));
  var files = folder.getFiles();
  while (files.hasNext()) {
    var file = files.next();
    if (file.getSharingAccess() == DriveApp.Access.ANYONE_WITH_LINK && file.getSharingPermission() == DriveApp.Permission.EDIT) {
      if (new Date().getTime() - file.getDateCreated().getTime() > 24*3600*1000) {
        file.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW);
      }
    }
  }
}

/** Webservice call for roaming report generation */
function doPost(request) {
  var roaming = JSON.parse(request.postData.getDataAsString());
  var crFile = createRoamingCR(roaming);
  var responseJson = JSON.stringify({ docId: crFile.getId(), docUrl: crFile.getUrl() });
  sendLog();
  return ContentService.createTextOutput(responseJson).setMimeType(ContentService.MimeType.JSON);
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
  var template = DriveApp.getFileById(PropertiesService.getScriptProperties().getProperty('crTemplateId'));
  return template.makeCopy('CR_' + roamingDate);
}

function fillRoamingSheet(roaming, sheet) {
  tryToSetInCell(sheet, 'c1', undefToEmpty(roaming.date));
  tryToSetInCell(sheet, 'c2', undefToEmpty(roaming.vehicle));
  tryToSetInCell(sheet, 'c3', joinList(roaming.teammates) + ' et ' + undefToEmpty(roaming.tutor));
  for (var i = 0; i < roaming.interventions.length; i++) {
    var intervention = roaming.interventions[i];
    var line = i + 15;
    tryToSetInCell(sheet, 'a' + line, joinList(intervention.people));
    tryToSetInCell(sheet, 'c' + line, undefToEmpty(intervention.location));
    tryToSetInCell(sheet, 'd' + line, undefToEmpty(intervention.time));
    tryToSetInCell(sheet, 'e' + line, undefToEmpty(intervention.source));
    tryToSetInCell(sheet, 'f' + line, undefToZero(intervention.nbAdults));
    tryToSetInCell(sheet, 'g' + line, undefToZero(intervention.nbChildren));
    tryToSetInCell(sheet, 'h' + line, undefToZero(intervention.blankets));
    tryToSetInCell(sheet, 'i' + line, undefToZero(intervention.tents));
    if (intervention.bai) { tryToSetInCell(sheet, 'j' + line, 'X'); };
    if (intervention.hygiene) { tryToSetInCell(sheet, 'k' + line, 'X'); };
    tryToSetInCell(sheet, 'l' + line, undefToEmpty(intervention.comments));
  }
}

function joinList(list) {
  return list ? list.join(', ') : '';
}

function undefToEmpty(value) {
  return value ? value : '';
}

function undefToZero(value) {
  return value ? value : 0;
}

function tryToSetInCell(sheet, range, value) {
  try {
    sheet.getRange(range).setValue(value);
  } catch (e) {
    Logger.log('Unable to set ' + value + ' into cell ' + range + ' : ' + e);
  }
}

function sendLog() {
  if (Logger.getLog()) {
    var email = Session.getActiveUser().getEmail();
    var subject = '[VINCI] Error while generating the CR';
    var body = Logger.getLog();
    MailApp.sendEmail(email, subject, body);
  }
}

function dev_findFolderId() {
  Logger.log(DriveApp.getFoldersByName("CR VINCI").next().getId());
}

function testGenerationCR() {
  var roaming = {
    "date": "2017-05-19",
    "tutor": "Tintin",
    "teammates": [
       "Gerda",
       "Popeye"
    ],
    "vehicle": "2",
    "interventions": [
      {
        "time": "21:45",
        "location": "Gare",
        "people": [
          "Pierrot",
          "Gertrude"
        ],
        "source": "115",
        "nbAdults": 2,
        "nbChildren": 0,
        "blankets": 3,
        "tents": 0,
        "bai": true,
        "hygiene": false,
        "comments": "DIACA"
      },
      {
        "undefIntervention": "withoutFields"
      },
      {
        "time": "22:30",
        "location": "CHU",
        "people": [
          "Jean",
          "Jeanne"
        ],
        "source": "Autre",
        "nbAdults": 1,
        "nbChildren": 1,
        "blankets": 1,
        "tents": 2,
        "bai": false,
        "hygiene": true,
        "comments": "My comment"
      }
    ],
    "version": 8,
    "synchroStatus": "SYNCHRONIZED"
  };
  createRoamingCR(roaming);
}

