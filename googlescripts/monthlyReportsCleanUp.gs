function monthlyCleanUp() {
  revokeReadPermissions();
  sortLastMonthReportInFolder();
}

function sortLastMonthReportInFolder() {
  var reportFolder = DriveApp.getFolderById("0000000000000000000000000000");
  var lastMonth = new Date();
  lastMonth.setMonth(lastMonth.getMonth() - 1);
  var lastMonthFolderName = lastMonth.toISOString().substring(0, 7);
  Logger.log('Moving reports in ' + lastMonthFolderName);
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
  var reportFolder = DriveApp.getFolderById("0000000000000000000000000000");
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
