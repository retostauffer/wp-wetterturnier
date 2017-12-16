CREATE TABLE `%table` (
  `paramID` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `paramName` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `EN` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `DE` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `helpEN` text COLLATE utf8_unicode_ci NOT NULL,
  `helpDE` text COLLATE utf8_unicode_ci NOT NULL,
  `valformat` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vallength` smallint(6) DEFAULT '5',
  `valmin` smallint(6) NOT NULL,
  `valmax` smallint(6) NOT NULL,
  `format` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `decimals` tinyint(3) unsigned DEFAULT '1',
  `unit` varchar(5) COLLATE utf8_unicode_ci DEFAULT '',
  PRIMARY KEY (`paramID`,`paramName`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT INTO `%table` VALUES
(1,'TTm','Maximum temperature','Maximumtemperatur','This is the maximum temperature during the night between 18UTC and 06UTC. Please note: ........... blabla.','Maximumtemperatur während des Tages zwischen 06UTC und 18UTC. Bitte beachte, dass .... blablabla.','number',5,-500,500,NULL,1,1,''),
(2,'TTn','Minimum temperature 18-6 UTC','Minimumtemperature 18-6 UTC','','','number',5,-500,500,NULL,1,1,''),
(3,'N','Total cloud cover (eights)','Bedeckungsgrad (achtel)','Help in the night ...','Hilfe in der Nacht ..&lt;br&gt;wooohohoo&lt;/br&gt;','digits',1,0,80,NULL,1,0,'octa'),
(4,'Sd','Sunshine duration over the day','Sonnenscheindauer','','','digits',3,0,1000,NULL,1,1,''),
(5,'dd','Wind direction (degrees)','Windrichtung (Grad)','','','digits',3,0,9900,NULL,1,1,''),
(6,'ff','Wind speed (knots)','Windgeschwindigkeit','','','digits',3,0,10000,NULL,1,1,''),
(7,'fx','Gust speed (knots, only if gt 25','Boeen (Knoten, nur wenn  gt 25)','','','digits',3,0,3000,NULL,1,1,''),
(8,'Wv','Weather before midday 6-12 UTC','Wetter Vormittags 6-12 UTC','','','digits',1,0,90,NULL,1,1,''),
(9,'Wn','Weather after midday 12-18 UTC','Wetter Nachmittags 12-18 UTC','','','digits',1,0,90,NULL,1,1,''),
(10,'PPP','Reduced surface pressure 12 UTC','Reduzierter Luftdruck 12 UrC','','','number',6,9000,12000,NULL,1,1,''),
(11,'TTd','Dewpoint temperature 12 UTC','Taupunkt 12 UTC','','','number',4,-500,500,NULL,1,1,''),
(12,'RR','Amount of precipitation (24h, 18-18 UTC)','Niederschlagsmenge (24h, 18-18 UTC','','','number',4,-30,10000,NULL,1,1,'');
UNLOCK TABLES;
