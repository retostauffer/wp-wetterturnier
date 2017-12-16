CREATE TABLE %table% (
    ID SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    cityID SMALLINT UNSIGNED NOT NULL,
    wmo SMALLINT UNSIGNED NOT NULL,
    name VARCHAR(20) NOT NULL,
    changed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (ID),
    UNIQUE KEY (wmo)
);

INSERT INTO %table% (`cityID`,`wmo`,`name`) VALUES
(1,10382,'Tegel'),
(1,10385,'Schoenefeld'),
(2,11035,'Hohe Warte'),
(2,11036,'Schwechat'),
(3,6680,'Fluntern'),
(3,6670,'Kloten'),
(4,11120,'Flughafen'),
(4,11320,'Universitaet'),
(5,10469,'Schkeuditz'),
(5,10471,'Holzhausen')
