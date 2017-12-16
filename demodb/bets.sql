CREATE TABLE %table% (
    ID INT UNSIGNED NOT NULL AUTO_INCREMENT,
    userID BIGINT UNSIGNED NOT NULL,
    cityID SMALLINT UNSIGNED NOT NULL,
    paramID SMALLINT UNSIGNED NOT NULL,
    tdate SMALLINT UNSIGNED NOT NULL,
    betdate SMALLINT UNSIGNED NOT NULL,
    placed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    value SMALLINT NOT NULL,
    PRIMARY KEY (ID),
    UNIQUE KEY (userID, cityID, paramID, tdate, betdate)
);
CREATE INDEX %table%_idx_tdate ON %table% (tdate);
CREATE INDEX %table%_idx_betdate ON %table% (betdate);
CREATE INDEX %table%_idx_cityID ON %table% (cityID);

