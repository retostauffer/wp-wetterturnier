CREATE TABLE %table% (
    ID INT UNSIGNED NOT NULL AUTO_INCREMENT,
    station SMALLINT UNSIGNED NOT NULL,
    paramID SMALLINT UNSIGNED NOT NULL,
    betdate SMALLINT UNSIGNED NOT NULL,
    placed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    value SMALLINT NOT NULL,
    PRIMARY KEY (ID),
    UNIQUE KEY (station, paramID, betdate)
);
CREATE INDEX %table%_idx_betdate ON %table% (betdate);
CREATE INDEX %table%_idx_station ON %table% (station);

