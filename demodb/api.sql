CREATE TABLE %table% (
   ID SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
   APIKEY VARCHAR(20) NOT NULL,
   APITYPE ENUM('obslive','obsarchive','bets') NOT NULL,
   APICONFIG VARCHAR(100) NOT NULL,
   ISPUBLIC BOOL NOT NULL DEFAULT 0,
   name VARCHAR(50) NOT NULL,
   description VARCHAR(200) NOT NULL,
   since TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   until INTEGER,
   active TINYINT DEFAULT 1,
   PRIMARY KEY (ID),
   UNIQUE(APIKEY)
);
INSERT INTO %table% (`APIKEY`,`APITYPE`,`ISPUBLIC`,`APICONFIG`,`name`,`description`) VALUES
("1234","obslive",0,"statnr=11320","Live Obs Station UNI Innsbruck","Reto Test"),
("5678","obslive",1,"statnr=11320","Non-public test entry","Reto Test");
