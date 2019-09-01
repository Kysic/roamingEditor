
CREATE TABLE `vcr_users` (
  `userId` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `username` varchar(50),
  `lastname` varchar(50),
  `firstname` varchar(50),
  `gender` enum('M', 'F'),
  `role` enum('former', 'guest', 'member', 'night_watcher', 'tutor', 'board', 'admin', 'root', 'appli') NOT NULL DEFAULT 'member',
  `passwordSalt` binary(16),
  `passwordHash` binary(32),
  `mailToken` binary(32),
  `registrationDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`userId`),
  UNIQUE KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `vcr_autologin` (
  `autologinId` binary(16) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `autologinTokenHash` binary(32) NOT NULL DEFAULT '\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0',
  `userId` int(11) NOT NULL,
  `connectionDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`autologinId`),
  CONSTRAINT FOREIGN KEY (`userId`) REFERENCES `vcr_users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX (`connectionDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `vcr_bruteforce` (
  `accessIp` varchar(50) NOT NULL,
  `accessDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (`accessIp`),
  INDEX (`accessDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `vcr_roamings` (
  `roamingId` int NOT NULL AUTO_INCREMENT,
  `creationDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `creationUserId` int NOT NULL,
  `roamingDate` date NOT NULL,
  `version` int NOT NULL,
  `rawJson` text NOT NULL,
  `docId` varchar(50),
  `generationDate` timestamp,
  `generationUserId` int,
  PRIMARY KEY (`roamingId`),
  CONSTRAINT FOREIGN KEY (`creationUserId`) REFERENCES `vcr_users` (`userId`),
  CONSTRAINT FOREIGN KEY (`generationUserId`) REFERENCES `vcr_users` (`userId`),
  INDEX (`roamingDate`, `version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

