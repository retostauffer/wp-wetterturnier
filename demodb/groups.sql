CREATE TABLE %table% (
    groupID SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    groupName VARCHAR(50) NOT NULL,
    groupDesc VARCHAR(100),
    since TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    until TIMESTAMP,
    active TINYINT DEFAULT 1,
    PRIMARY KEY (groupID)
);
INSERT INTO %table% (`groupName`,`groupDesc`) VALUES
('DWD','Deutscher Wetterdienst'),
('Meteogroup','Mitarbeiter der Meteogroup'),
('UBIMET','Mitarbeiter UBIMET'),
('universeLE','Whatever'),
('FU-Studenten','Studenten der FU'),
('DahlemOBser','Whatever Obser'),
('UNI Innsbruck','Mitarbeiter/Studierende der UNI Innsbruck'),
('WAV2','Teilnehmner der Lehrveranstaltung Wetterbesprechung 2, UNI Innsbruck'),
('MetVienna','Vienna Metirgendwas');
