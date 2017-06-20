
INSERT INTO `it_users` (`userId`, `email`, `lastname`, `firstname`, `role`, `passwordSalt`, `passwordHash`, `mailToken`, `registrationDate`) VALUES
(1, 'tablet1@example.com', '1', 'tablette', 'appli', NULL, NULL, NULL, '2000-01-01 00:00:00'),
(10, 'Laure.Maitre@example.com', 'MAITRE', 'Laure', 'root', 0xb5780de69c9c0514ce46fef731b6c08b, 0x4a2aa172946212cfc80dff01d0c51deae9d8339a425ac2ca6d0ea3b8e65a3e57, NULL, '2016-10-30 09:18:08'),
(11, 'martin-alexis@hotmail.com', 'MARTIN', 'Alexis', 'board', 0xf0c05671ca050838aeea651e82baceaf, 0x75011ae53d3dce47c7a99295dff550f19a8482cd7ae0a71bdadc66b48779fc28, NULL, '2016-10-30 10:40:57'),
(12, 'cerise.matthieu@gmail.com', 'MATTHIEU', 'Cerise', 'tutor', 0x235f3fc50b7e5049b9adc18e731601d9, 0x9a8b355b09fef55d850f5334086aba185fadae3b903f26152ac7702c4cc376e6, NULL, '2016-10-30 20:08:13'),
(13, 'aminaTrump@live.fr', 'TRUMP', 'Amina', 'admin', 0x6f646d777cd077ab5f1aae24ad10216a, 0x720efdd631e252498f1adf49f7c5fcee48b6b707f673760980ee5df1ffab9207, NULL, '2016-11-01 10:55:46'),
(14, 'anaele781@orange.fr', 'CHIRAC', 'Anaële', 'member', 0x92145f8aa0f33461df2789ab70c2d7d6, 0xd4f45f24165464187a83b10a860296d9a29d456e36a092d12ba1868233db4574, NULL, '2016-11-01 22:21:30');

INSERT INTO `it_autologin` (`userId`, `autologinId`, `connectionDate`) VALUES
(1, 0xf14f0c3ebebf64da38ac840753b82f7b307b96453a69823c2d79071fd2deed91765dff28b498261e04e25672671ebd76, '2020-01-01 00:00:00');

