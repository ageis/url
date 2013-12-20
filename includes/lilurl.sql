CREATE TABLE IF NOT EXISTS `lil_urls` (
  `id` int(11) NOT NULL DEFAULT '0',
  `url` text,
  `date` timestamp(14) NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `hash` varchar(8) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `lil_urls` (`id`, `url`, `date`, `hash`) VALUES
(0, 'https://github.com/ageis/url', '2013-12-31 12:00:00', '00000');