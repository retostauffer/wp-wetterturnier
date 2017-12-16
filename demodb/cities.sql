CREATE TABLE %table% (
    ID SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(20) NOT NULL,
    hash VARCHAR(20) NOT NULL,
    sort TINYINT,
    since TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    until TIMESTAMP,
    active TINYINT NOT NULL DEFAULT 1,
    PRIMARY KEY (ID,name)
);
INSERT INTO %table% (`name`,`hash`,`wmo1`,`wmo2`,`sort`) VALUES
('Berlin',   'BER', 1),
('Wien',     'VIE', 2),
('Zuerich',  'ZUR', 3),
('Innsbruck','IBK', 4),
('Leipzig',  'LEI', 5);
