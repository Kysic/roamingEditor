// https://docs.google.com/spreadsheets/d/XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
var docId = PropertiesService.getScriptProperties().getProperty('docId');
var roamingSheetId = PropertiesService.getScriptProperties().getProperty('roamingSheetId');
var soupAndEventsSheetId = PropertiesService.getScriptProperties().getProperty('soupAndEventsSheetId');
var newMembersSheetId = PropertiesService.getScriptProperties().getProperty('newMembersSheetId');

function doGet(request) {
  var param = request.parameter;
  var action = param['action'];
  var spreadSheet = SpreadsheetApp.openById(docId);
  if (action == 'GET_ROAMING') {
    return emailListTextOutput(extractEmails(spreadSheet, roamingSheetId, all));
  } else if (action == 'GET_TUTORS') {
    return emailListTextOutput(extractEmails(spreadSheet, roamingSheetId, isTutor));
  } else if (action == 'GET_BOARD') {
    return emailListTextOutput(extractEmails(spreadSheet, roamingSheetId, isBoard));
  } else if (action == 'GET_OTHERS') {
    return emailListTextOutput(extractEmails(spreadSheet, soupAndEventsSheetId, all));
  }
  return ContentService.createTextOutput('no action specified');
}

function emailListTextOutput(emails) {
  return ContentService.createTextOutput(emails.join(' '));
}

function extractEmails(spreadSheet, sheetId, filter) {
  var emails = [];
  var sheet = getSheetById(spreadSheet, sheetId);
  var data = sheet.getDataRange().getValues();
  for (var i = 0; i < data.length; i++) {
    var email = data[i][5].trim().toLowerCase();
    if (email.indexOf('@') !== -1) {
      if ( filter(data[i]) ) {
        emails.push(email);
      }
    }
  }
  return emails.sort();
}

function all(row) {
  return true;
}

function isTutor(row) {
  return row[7].trim().length != 0 || row[8].trim().length != 0;
}

function isBoard(row) {
  return row[7].trim().length != 0;
}

function getSheetById(spreadsheet, sheetId) {
  var sheets = spreadsheet.getSheets();
  for (var sheetIndex = 0; sheetIndex < sheets.length; sheetIndex++) {
    var sheet = sheets[sheetIndex];
    if (sheet.getSheetId() == sheetId) {
      return sheet;
    }
  }
  throw 'Sheet id ' + sheetId + ' not found';
}

function test() {
  var spreadSheet = SpreadsheetApp.openById(docId);
  Logger.log(extractEmails(spreadSheet, roamingSheetId, all))
}

