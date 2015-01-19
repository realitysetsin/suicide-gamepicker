
CREATE TABLE `schedule` (
  `kickoff` datetime NOT NULL,
  `hteamid` int NOT NULL,
  `ateamid` int NOT NULL,
  `hscore` int NOT NULL,
  `ascore` int NOT NULL
) COMMENT='' ENGINE='InnoDB' COLLATE 'utf8_unicode_ci';

CREATE TABLE `team` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `city` varchar(255) NOT NULL,
  `mascot` varchar(255) NOT NULL,
  `alt_city` varchar(255) NOT NULL,
  `divisionid` int NOT NULL
) COMMENT='' ENGINE='InnoDB' COLLATE 'utf8_unicode_ci';

CREATE TABLE `picks` (
  `week` int NOT NULL,
  `teamid` int NOT NULL
) COMMENT='' ENGINE='InnoDB' COLLATE 'utf8_unicode_ci';

CREATE TABLE `division` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL
) COMMENT='' ENGINE='InnoDB' COLLATE 'utf8_unicode_ci';

INSERT INTO `division` VALUES
  (1, 'AFC North'),
  (2, 'AFC South'),
  (3, 'AFC West'),
  (4, 'AFC East'),
  (5, 'NFC North'),
  (6, 'NFC South'),
  (7, 'NFC West'),
  (8, 'NFC East');

INSERT INTO `team` VALUES
  (1, 'Cincinnati', 'Bengals', 'Cincinnati', 1),
  (2, 'Cleveland', 'Browns', 'Cleveland', 1),
  (3, 'Pittsburgh', 'Steelers', 'Pittsburgh', 1),
  (4, 'Baltimore', 'Ravens', 'Baltimore', 1),
  (5, 'Indianapolis', 'Colts', 'Indianapolis', 2),
  (6, 'Houston', 'Texans', 'Houston', 2),
  (7, 'Jacksonville', 'Jaguars', 'Jacksonville', 2),
  (8, 'Tennessee', 'Titans', 'Tennessee', 2),
  (9, 'Denver', 'Broncos', 'Denver', 3),
  (10, 'San Diego', 'Chargers', 'San Diego', 3),
  (11, 'Kansas City', 'Chiefs', 'Kansas City', 3),
  (12, 'Oakland', 'Raiders', 'Oakland', 3),
  (13, 'New England', 'Patriots', 'New England', 4),
  (14, 'Miami', 'Dolphins', 'Miami', 4),
  (15, 'Buffalo', 'Bills', 'Buffalo', 4),
  (16, 'New York', 'Jets', 'NY Jets', 4),
  (17, 'Green Bay', 'Packers', 'Green Bay', 5),
  (18, 'Detroit', 'Lions', 'Detroit', 5),
  (19, 'Chicago', 'Bears', 'Chicago', 5),
  (20, 'Minnesota', 'Vikings', 'Minnesota', 5),
  (21, 'Atlanta', 'Falcons', 'Atlanta', 6),
  (22, 'New Orleans', 'Saints', 'New Orleans', 6),
  (23, 'Carolina', 'Panthers', 'Carolina', 6),
  (24, 'Tampa Bay', 'Buccaneers', 'Tampa Bay', 6),
  (25, 'Arizona', 'Cardinals', 'Arizona', 7),
  (26, 'Seattle', 'Seahawks', 'Seattle', 7),
  (27, 'San Francisco', '49ers', 'San Francisco', 7),
  (28, 'St. Louis', 'Rams', 'St Louis', 7),
  (29, 'Philadelphia', 'Eagles', 'Philadelphia', 8),
  (30, 'Dallas', 'Cowboys', 'Dallas', 8),
  (31, 'New York', 'Giants', 'NY Giants', 8),
  (32, 'Washington', 'Redskins', 'Washington', 8);