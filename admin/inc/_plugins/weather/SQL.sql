CREATE TABLE IF NOT EXISTS `weather_cache` (
  `id` varchar(25) NOT NULL,
  `stored` text NOT NULL,
  `timestamp` int(11) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
