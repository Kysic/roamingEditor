/*
* action: enrol/cancel
* docId: '1z0sOYFqJSganHU2RJ0t3Wlw8ETg-3jqiFm5qIiObSbE'
* sheetId: '1203788933'
* day: 12
* position: 0=tutor, [1;3]=volunteer
* user: 'Paul Garnier'
*/
function doGet(request) {
  try {
    var param = request.parameter;
    var action = param['action'];
    var docId = param['docId'];
    var sheetId = param['sheetId'];
    var day = parseInt(param['day']);
    var position = parseInt(param['position']);
    var user = param['user'];
    if (action == 'enrol') {
      enrol(docId, sheetId, day, position, user);
    } else if (action == 'cancel') {
      cancel(docId, sheetId, day, position, user);
    }
    return jsonTextoutput({ status: 'success' });
  } catch (e) {
    return jsonTextoutput({ status: 'error', errorMsg: e });
  }
}

function enrol(docId, sheetId, day, position, user) {
  var range = getUserRange(docId, sheetId, day, position);
  if (range.getValue() != "") {
      throw 'The user ' + range.getValue() + ' is already enroled for this day';
  }
  range.setValue(user);
}

function cancel(docId, sheetId, day, position, user) {
  var range = getUserRange(docId, sheetId, day, position);
  if (range.getValue() != user) {
      throw 'The user ' + user + ' is not enroled for this day (' + range.getValue() + ')';
  }
  range.setValue('');
}

function jsonTextoutput(object) {
    return ContentService.createTextOutput(JSON.stringify(object)).setMimeType(ContentService.MimeType.JSON);
}

function getUserRange(docId, sheetId, day, position) {
  var spreadsheet = SpreadsheetApp.openById(docId);
  var sheet = getSheetById(spreadsheet, sheetId);
  var dayRow = findRowOfDay(sheet, day);
  var userColumn = getColumnFor(position);
  return sheet.getRange(dayRow, userColumn);
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

function findRowOfDay(sheet, day) {
  var data = sheet.getDataRange().getValues();
  for (var row = 0; row < 50; row++) {
    if (day == data[row][1]) {
      return row + 1;
    }
  }
  throw 'Unable to find day ' + day + ' in sheet ' + sheet.getSheetId();
}

function getColumnFor(position) {
  switch(position) {
    case 0:
      return 3;
    case 1:
      return 5;
    case 2:
      return 7;
    case 3:
      return 9;
    default:
      throw 'Invalid position ' + position;
  }
}
