CREATE TABLE `vcr_users` (
  `userId` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `lastname` varchar(50),
  `firstname` varchar(50),
  `role` enum('former', 'guest', 'member', 'tutor', 'board', 'admin', 'root') NOT NULL DEFAULT 'member',
  `passwordSalt` binary(16),
  `passwordHash` binary(32),
  `mailToken` binary(32),
  `registrationDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`userId`),
  UNIQUE KEY `login` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `vcr_autologin` (
  `autologinId` binary(48) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `userId` int(11) NOT NULL,
  `connectionDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`autologinId`),
  CONSTRAINT `vcr_autologin_userId_ref` FOREIGN KEY (`userId`) REFERENCES `vcr_users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX `idx_autologinConnectionDate` (`connectionDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `vcr_roamings` (
  `roamingId` int NOT NULL AUTO_INCREMENT,
  `creationDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `roamingDate` date NOT NULL,
  `version` int NOT NULL,
  `rawJson` text NOT NULL,
  `docId` varchar(50),
  `generationDate` timestamp,
  `generationUserId` int,
  PRIMARY KEY (`roamingId`),
  CONSTRAINT `vcr_roamings_userId_ref` FOREIGN KEY (`generationUserId`) REFERENCES `vcr_users` (`userId`),
  INDEX `idx_roamingDateVersion` (`roamingDate`, `version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

