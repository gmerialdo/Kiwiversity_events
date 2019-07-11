-- phpMyAdmin SQL Dump
-- version 4.8.4
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le :  jeu. 11 juil. 2019 à 12:01
-- Version du serveur :  5.7.24
-- Version de PHP :  7.2.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `ocr-projet5-db`
--

-- --------------------------------------------------------

--
-- Structure de la table `evt_accounts`
--

DROP TABLE IF EXISTS `evt_accounts`;
CREATE TABLE IF NOT EXISTS `evt_accounts` (
  `evt_account_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) DEFAULT NULL,
  `user_name` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `managing_rights` tinyint(1) DEFAULT '0',
  `active_account` tinyint(1) NOT NULL,
  `token` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`evt_account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `evt_accounts`
--

INSERT INTO `evt_accounts` (`evt_account_id`, `email`, `user_name`, `password`, `first_name`, `last_name`, `managing_rights`, `active_account`, `token`) VALUES
(1, 'merialdo.gaelle@gmail.com', 'gmerialdo', '30984044340d8f13e19345382c35011d82350adabd87bba1107f4435b47a6c32', 'Gaëlle', 'Merialdo Torrenti', 1, 1, '1562840850-5d270f125ff4e'),
(2, 'mathieu.torrenti@gmail.com', 'mathieu.torrenti@gmail.com', 'ed02457b5c41d964dbd2f2a609d63fe1bb7528dbe55e1abf5b52c249cd735797', 'Matthieu', 'TORRENTI', 0, 1, NULL),
(3, 'lalaland@gmail.com', 'lalaland@gmail.com', 'ed02457b5c41d964dbd2f2a609d63fe1bb7528dbe55e1abf5b52c249cd735797', 'Amélie', 'Gardello', 0, 1, NULL),
(4, 'facile@gmail.com', 'facile@gmail.com', '30984044340d8f13e19345382c35011d82350adabd87bba1107f4435b47a6c32', 'René', ' Brudeau', 0, 1, NULL),
(5, 'michael@gmail.com', 'michael@gmail.com', 'ed02457b5c41d964dbd2f2a609d63fe1bb7528dbe55e1abf5b52c249cd735797', 'Michael', 'Openclassrooms', 0, 0, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `evt_events`
--

DROP TABLE IF EXISTS `evt_events`;
CREATE TABLE IF NOT EXISTS `evt_events` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `location_id` int(11) NOT NULL,
  `image_id` int(11) NOT NULL DEFAULT '1',
  `category` varchar(100) DEFAULT NULL,
  `active_event` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 inactive (draft), 1 active, 2 deleted (trash)',
  `start_datetime` datetime NOT NULL,
  `finish_datetime` datetime NOT NULL,
  `max_tickets` int(11) DEFAULT NULL,
  `type_tickets` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0 no booking, 1 free, 2 paid, 3 donation',
  `public` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1 all, 2 adults only, 3 children only',
  `members_only` tinyint(1) NOT NULL DEFAULT '0',
  `price_adult_mb` decimal(10,2) DEFAULT NULL,
  `price_adult` decimal(10,2) DEFAULT NULL,
  `price_child_mb` decimal(10,2) DEFAULT NULL,
  `price_child` decimal(10,2) DEFAULT NULL,
  `enable_booking` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`event_id`),
  KEY `location_id` (`location_id`),
  KEY `image_id` (`image_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `evt_events`
--

INSERT INTO `evt_events` (`event_id`, `name`, `description`, `location_id`, `image_id`, `category`, `active_event`, `start_datetime`, `finish_datetime`, `max_tickets`, `type_tickets`, `public`, `members_only`, `price_adult_mb`, `price_adult`, `price_child_mb`, `price_child`, `enable_booking`) VALUES
(1, 'Fête de fin d\'année!', 'Venez célébrer avec nous la fin de l\'année, autour d\'un bon repas partagé. \r\nNous nous occupons du dessert, chacun peut apporter un plat et une boisson pour le buffet. \r\nMerci de bien vouloir réserver pour pouvoir organiser au mieux. \r\nA bientôt!', 1, 2, 'social', 1, '2019-05-05 18:00:00', '2019-05-05 22:00:00', NULL, 1, 1, 0, NULL, NULL, NULL, NULL, 0),
(2, 'Concert des petits loups', 'Venez assister au concert de l&#39;été de notre chorale des petits loups!Réservation obligatoire. ', 1, 8, 'children', 1, '2019-07-20 17:00:00', '2019-07-20 18:30:00', 100, 3, 1, 0, '5.00', NULL, NULL, NULL, 1),
(3, 'Book club', 'Le cercle littéraire se réunit une fois par mois pour discuter ensemble sur un livre lu chez soi. Pour la prochaine rencontre, nous vous invitons à lire &#34;Petit Pays&#34;, de Gaël Fayé. Réservation obligatoire. ', 1, 6, 'culture', 1, '2019-07-18 16:00:00', '2019-07-18 18:00:00', 20, 2, 2, 1, '6.00', NULL, NULL, NULL, 1),
(4, 'Day in immersion', 'Spend a whole day learning and practicing French, in real life situations. Please register online or at the office. ', 1, 4, 'workshop', 1, '2019-07-29 09:00:00', '2019-07-29 17:00:00', 12, 2, 2, 0, '50.00', '60.00', NULL, NULL, 1),
(5, 'Test', 'Pour supprimer ensuite', 1, 1, NULL, 1, '2019-07-18 06:00:00', '2019-07-18 08:00:00', NULL, 0, 1, 1, NULL, NULL, NULL, NULL, 0),
(6, 'Ateliers nocturnes pendant l\'été', 'Venez perfectionner votre français avec nos ateliers nocturnes. Ils auront lieu tout l\'été dans nos locaux. Tous les jeudis soirs. Sur inscription uniquement. ', 1, 4, 'workshop', 1, '2019-07-04 06:00:00', '2019-07-04 08:00:00', 12, 2, 2, 0, '17.50', '25.00', NULL, NULL, 0),
(7, 'Ateliers nocturnes pendant l\'été', 'Venez perfectionner votre français avec nos ateliers nocturnes. Ils auront lieu tout l\'été dans nos locaux. Tous les jeudis soirs. Sur inscription uniquement. ', 3, 4, 'workshop', 1, '2019-07-11 18:00:00', '2019-07-11 20:00:00', 12, 2, 2, 0, '15.00', '20.00', NULL, NULL, 1),
(8, 'Soirée cinéma', 'Come and enjoy a French movie with some wine and cheese!', 1, 9, 'cinema', 1, '2019-07-12 08:00:00', '2019-07-12 10:30:00', 30, 2, 2, 0, '3.50', '5.00', NULL, NULL, 1),
(9, 'Dégustation vin fromage', 'Réservation obligatoire', 1, 3, 'social', 2, '2019-06-28 18:00:00', '2019-06-28 20:00:00', 20, 2, 2, 0, '20.00', '30.00', NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `evt_images`
--

DROP TABLE IF EXISTS `evt_images`;
CREATE TABLE IF NOT EXISTS `evt_images` (
  `image_id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `src` varchar(100) NOT NULL,
  `alt` varchar(50) NOT NULL,
  PRIMARY KEY (`image_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `evt_images`
--

INSERT INTO `evt_images` (`image_id`, `active`, `src`, `alt`) VALUES
(1, 1, 'layout/images/all_default.png', 'Event'),
(2, 1, 'layout/images/all_party.jpg', 'Party'),
(3, 1, 'layout/images/all_social.jpg', 'social'),
(4, 1, 'layout/images/all_workshop.jpg', 'workshop'),
(5, 1, 'layout/images/all_balloons.png', 'balloons'),
(6, 1, 'layout/images/all_litterature.jpg', 'litterature'),
(7, 1, 'layout/images/all_paris.jpg', 'Paris'),
(8, 1, 'layout/images/all_chorus.png', 'chorus'),
(9, 1, 'layout/images/all_cinema.jpg', 'Cinema');

-- --------------------------------------------------------

--
-- Structure de la table `evt_locations`
--

DROP TABLE IF EXISTS `evt_locations`;
CREATE TABLE IF NOT EXISTS `evt_locations` (
  `location_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `zipcode` varchar(50) NOT NULL,
  `state` varchar(50) NOT NULL,
  `country` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `max_occupancy` int(11) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`location_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `evt_locations`
--

INSERT INTO `evt_locations` (`location_id`, `name`, `address`, `city`, `zipcode`, `state`, `country`, `phone`, `max_occupancy`, `active`) VALUES
(1, 'Alliance Française de Tucson', '2099E River Rd', 'Tucson', '85718', 'AZ', 'USA', '+15208819159', NULL, 1),
(2, 'pour voir', 'test', 'Test', 'test', 'AL', 'Test', 'test', 5, 0),
(3, 'Cultural Center', '5655N Camino de las Estrellas', 'Tucson', '85718', 'AZ', 'USA', '+1 5208486538', 50, 1);

-- --------------------------------------------------------

--
-- Structure de la table `evt_sessions`
--

DROP TABLE IF EXISTS `evt_sessions`;
CREATE TABLE IF NOT EXISTS `evt_sessions` (
  `session_id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(23) NOT NULL,
  `data` json DEFAULT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `evt_sessions`
--

INSERT INTO `evt_sessions` (`session_id`, `uuid`, `data`) VALUES
(1, '19168734785d09488657b76', '{\"data\": \"1\", \"succeed\": true, \"last_name\": \"Merialdo Torrenti\", \"user_name\": \"gmerialdo\", \"admin_mode\": false, \"first_name\": \"Gaelle\", \"evt_account_id\": \"1\", \"evt_managing_rights\": \"1\"}'),
(2, '19170297465d0b40b2cda1f', '{\"data\": \"2\", \"succeed\": true, \"last_name\": \"TORRENTI\", \"user_name\": \"mathieu.torrenti@gmail.com\", \"admin_mode\": false, \"first_name\": \"Matthieu\", \"evt_account_id\": \"2\", \"evt_managing_rights\": \"0\"}'),
(3, '19170448675d0b7bc328d69', '{\"data\": \"3\", \"succeed\": true, \"last_name\": \"Merialdo Torrenti\", \"user_name\": \"gmerialdo\", \"admin_mode\": true, \"first_name\": \"Gaelle\", \"evt_account_id\": \"1\", \"evt_managing_rights\": \"1\"}'),
(4, '19171273495d0c88d557677', '{\"data\": \"4\", \"succeed\": true, \"last_name\": \"Merialdo Torrenti\", \"user_name\": \"gmerialdo\", \"admin_mode\": true, \"first_name\": \"Gaelle\", \"evt_account_id\": \"1\", \"evt_managing_rights\": \"1\"}'),
(5, '19175251475d11c63b38fa6', '{\"data\": \"5\", \"succeed\": true, \"last_name\": \"Openclassrooms\", \"user_name\": \"lionel@gmail.com\", \"admin_mode\": false, \"first_name\": \"Lionel\", \"evt_account_id\": \"5\", \"evt_managing_rights\": \"0\"}'),
(6, '19175271445d11ce083cccb', '{\"data\": \"6\", \"succeed\": true, \"last_name\": \"Merialdo Torrenti\", \"user_name\": \"gmerialdo\", \"admin_mode\": false, \"first_name\": \"Gaelle\", \"evt_account_id\": \"1\", \"evt_managing_rights\": \"1\"}'),
(7, '19175304965d11db20b8c1c', '{\"data\": \"7\", \"succeed\": true, \"last_name\": \"Gaëlle\", \"user_name\": \"cerise@gmail.com\", \"admin_mode\": false, \"first_name\": \"Cerise\", \"evt_account_id\": \"6\", \"evt_managing_rights\": \"0\"}'),
(8, '19175314465d11ded6bbf71', '{\"data\": \"8\", \"succeed\": true, \"admin_mode\": false}'),
(9, '19175335205d11e6f0a342a', '{\"data\": \"9\", \"succeed\": true, \"admin_mode\": false}'),
(10, '19177256095d146b0965264', '{\"data\": \"10\", \"succeed\": true, \"last_name\": \"Aa\", \"user_name\": \"patricifqa@gmail.com\", \"admin_mode\": false, \"first_name\": \"Pp\", \"evt_account_id\": \"5\", \"evt_managing_rights\": \"0\"}'),
(11, '19177265755d146ecf730d6', '{\"data\": \"11\", \"succeed\": true, \"last_name\": \"Gg\", \"user_name\": \"pat@gmail.com\", \"admin_mode\": false, \"first_name\": \"Gg\", \"evt_account_id\": \"6\", \"evt_managing_rights\": \"0\"}'),
(12, '19177267095d146f5590ef1', '{\"data\": \"12\", \"succeed\": true, \"last_name\": \"Merialdo Torrenti\", \"user_name\": \"gmerialdo\", \"admin_mode\": false, \"first_name\": \"Gaelle\", \"evt_account_id\": \"1\", \"evt_managing_rights\": \"1\"}'),
(13, '19177474365d14c04cc1915', '{\"data\": \"13\", \"succeed\": true, \"last_name\": \"Openclassrooms\", \"user_name\": \"michael@gmail.com\", \"admin_mode\": false, \"first_name\": \"Michael\", \"evt_account_id\": \"5\", \"evt_managing_rights\": \"0\"}'),
(14, '19177517475d14d12356f05', '{\"data\": \"14\", \"succeed\": true, \"last_name\": \"Merialdo Torrenti\", \"user_name\": \"gmerialdo\", \"admin_mode\": true, \"first_name\": \"Gaelle\", \"evt_account_id\": \"1\", \"evt_managing_rights\": \"1\"}'),
(15, '19177604095d14f2f906175', '{\"data\": \"15\", \"succeed\": true, \"admin_mode\": false}'),
(16, '19178165315d15991378fd1', '{\"data\": \"16\", \"succeed\": true, \"admin_mode\": false}'),
(17, '19178266205d15c07c297ac', '{\"data\": \"17\", \"succeed\": true, \"admin_mode\": false}'),
(18, '19181435705d19f7327b595', '{\"data\": \"18\", \"succeed\": true, \"last_name\": \"Merialdo Torrenti\", \"user_name\": \"gmerialdo\", \"admin_mode\": true, \"first_name\": \"Gaelle\", \"evt_account_id\": \"1\", \"evt_managing_rights\": \"1\"}'),
(19, '19184301465d1db7424dc82', '{\"data\": \"19\", \"succeed\": true, \"last_name\": \"Merialdo Torrenti\", \"user_name\": \"gmerialdo\", \"admin_mode\": true, \"first_name\": \"Gaëlle\", \"evt_account_id\": \"1\", \"evt_managing_rights\": \"1\"}'),
(20, '19185237595d1eefcf2df2e', '{\"data\": \"20\", \"succeed\": true, \"last_name\": \"Merialdo Torrenti\", \"user_name\": \"gmerialdo\", \"admin_mode\": true, \"first_name\": \"Gaëlle\", \"evt_account_id\": \"1\", \"evt_managing_rights\": \"1\"}'),
(21, '19188291755d22f9775eb73', '{\"data\": \"21\", \"succeed\": true, \"admin_mode\": false}'),
(22, '19188291885d22f984a8de8', '{\"data\": \"22\", \"succeed\": true, \"last_name\": \"Merialdo Torrenti\", \"user_name\": \"gmerialdo\", \"admin_mode\": false, \"first_name\": \"Gaëlle\", \"evt_account_id\": \"1\", \"evt_managing_rights\": \"1\"}'),
(23, '19188329865d23085a734e8', '{\"data\": \"23\", \"succeed\": true, \"last_name\": \"Lala\", \"user_name\": \"ge@gmail.com\", \"admin_mode\": false, \"first_name\": \"Gege\", \"evt_account_id\": \"6\", \"evt_managing_rights\": \"0\"}'),
(24, '19188330125d230874408a9', '{\"data\": \"24\", \"succeed\": true, \"last_name\": \"Merialdo Torrenti\", \"user_name\": \"gmerialdo\", \"admin_mode\": false, \"first_name\": \"Gaëlle\", \"evt_account_id\": \"1\", \"evt_managing_rights\": \"1\"}'),
(25, '19188550975d235eb9e3a44', '{\"data\": \"25\", \"succeed\": true, \"admin_mode\": false}'),
(26, '19189301985d244ef628c23', '{\"data\": \"26\", \"succeed\": true, \"admin_mode\": false}'),
(27, '19189310325d2452388156d', '{\"data\": \"27\", \"succeed\": true, \"last_name\": \"Merialdo Torrenti\", \"user_name\": \"gmerialdo\", \"admin_mode\": false, \"first_name\": \"Gaëlle\", \"evt_account_id\": \"1\", \"evt_managing_rights\": \"1\"}'),
(28, '19189428115d24803bcf281', '{\"data\": \"28\", \"succeed\": true, \"last_name\": \"Merialdo Torrenti\", \"user_name\": \"gmerialdo\", \"admin_mode\": false, \"first_name\": \"Gaëlle\", \"evt_account_id\": \"1\", \"evt_managing_rights\": \"1\"}'),
(29, '19189444105d24867aedd70', '{\"data\": \"29\", \"succeed\": true, \"last_name\": \"Merialdo Torrenti\", \"user_name\": \"gmerialdo\", \"admin_mode\": false, \"first_name\": \"Gaëlle\", \"evt_account_id\": \"1\", \"evt_managing_rights\": \"1\"}'),
(30, '19189449255d24887d3ff35', '{\"data\": \"30\", \"succeed\": true, \"admin_mode\": false}'),
(31, '19189553935d24b161e8b48', '{\"data\": \"31\", \"succeed\": true, \"admin_mode\": false}'),
(32, '19189562785d24b4d68aa6e', '{\"data\": \"32\", \"succeed\": true, \"admin_mode\": false}'),
(33, '19191265595d26e3bf2d174', '{\"data\": \"33\", \"succeed\": true, \"last_name\": \"Hh\", \"user_name\": \"gg@hk.com\", \"admin_mode\": false, \"first_name\": \"Hh\", \"evt_account_id\": \"7\", \"evt_managing_rights\": \"0\"}'),
(34, '19191277365d26e85835ef6', '{\"data\": \"34\", \"succeed\": true, \"admin_mode\": false}'),
(35, '19191374305d270e3621e5a', '{\"data\": \"35\", \"succeed\": true, \"last_name\": \"Kk\", \"user_name\": \"ff@ml.com\", \"admin_mode\": false, \"first_name\": \"Gg\", \"evt_account_id\": \"8\", \"evt_managing_rights\": \"0\"}'),
(36, '19191374935d270e7574b0c', '{\"data\": \"36\", \"succeed\": true, \"last_name\": \"Hh\", \"user_name\": \"fffffff@hm.com\", \"admin_mode\": false, \"first_name\": \"Hh\", \"evt_account_id\": \"9\", \"evt_managing_rights\": \"0\"}'),
(37, '19191375575d270eb5c0246', '{\"data\": \"37\", \"succeed\": true, \"last_name\": \"Gg\", \"user_name\": \"gg@gg.gg\", \"admin_mode\": false, \"first_name\": \"Gg\", \"evt_account_id\": \"10\", \"evt_managing_rights\": \"0\"}'),
(38, '19191379885d2710647e965', '{\"data\": \"38\", \"succeed\": true, \"last_name\": \"Gg\", \"user_name\": \"fd@g.com\", \"admin_mode\": false, \"first_name\": \"Ggg\", \"evt_account_id\": \"11\", \"evt_managing_rights\": \"0\"}'),
(39, '19191381285d2710f0cd9c9', '{\"data\": \"39\", \"succeed\": true, \"last_name\": \"Ggg\", \"user_name\": \"sss@gl.com\", \"admin_mode\": false, \"first_name\": \"Ggggg\", \"evt_account_id\": \"12\", \"evt_managing_rights\": \"0\"}'),
(40, '19191382255d271151cc84a', '{\"data\": \"40\", \"succeed\": true, \"last_name\": \"Ff\", \"user_name\": \"fff@gh.zil\", \"admin_mode\": false, \"first_name\": \"Ff\", \"evt_account_id\": \"13\", \"evt_managing_rights\": \"0\"}'),
(41, '19191384765d27124cafe15', '{\"data\": \"41\", \"succeed\": true, \"last_name\": \"Hh\", \"user_name\": \"ss@guhuah.cih\", \"admin_mode\": false, \"first_name\": \"Hh\", \"evt_account_id\": \"14\", \"evt_managing_rights\": \"0\"}'),
(42, '19191391655d2714fda9db3', '{\"data\": \"42\", \"succeed\": true, \"last_name\": \"Merialdo Torrenti\", \"user_name\": \"gmerialdo\", \"admin_mode\": false, \"first_name\": \"Gaëlle\", \"evt_account_id\": \"1\", \"evt_managing_rights\": \"1\"}');

-- --------------------------------------------------------

--
-- Structure de la table `evt_tickets`
--

DROP TABLE IF EXISTS `evt_tickets`;
CREATE TABLE IF NOT EXISTS `evt_tickets` (
  `ticket_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `evt_account_id` int(11) NOT NULL,
  `time_booked` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `nb_tickets_adult_mb` smallint(6) DEFAULT NULL,
  `nb_tickets_adult` smallint(6) DEFAULT NULL,
  `nb_tickets_child_mb` smallint(6) DEFAULT NULL,
  `nb_tickets_child` smallint(6) DEFAULT NULL,
  `nb_tickets_all` smallint(6) DEFAULT NULL,
  `price_adult_mb_booked` decimal(10,0) DEFAULT NULL,
  `price_adult_booked` decimal(10,0) DEFAULT NULL,
  `price_child_mb_booked` decimal(10,0) DEFAULT NULL,
  `price_child_booked` decimal(10,0) DEFAULT NULL,
  `donation` decimal(10,0) NOT NULL DEFAULT '0',
  `total_to_pay` decimal(10,0) DEFAULT NULL,
  `payment_datetime` datetime DEFAULT NULL,
  `total_paid` decimal(10,2) DEFAULT NULL,
  `cancelled_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`ticket_id`),
  KEY `event_id` (`event_id`),
  KEY `evt_account_id` (`evt_account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `evt_tickets`
--

INSERT INTO `evt_tickets` (`ticket_id`, `event_id`, `evt_account_id`, `time_booked`, `nb_tickets_adult_mb`, `nb_tickets_adult`, `nb_tickets_child_mb`, `nb_tickets_child`, `nb_tickets_all`, `price_adult_mb_booked`, `price_adult_booked`, `price_child_mb_booked`, `price_child_booked`, `donation`, `total_to_pay`, `payment_datetime`, `total_paid`, `cancelled_time`) VALUES
(1, 1, 1, '2019-06-04 19:29:49', NULL, NULL, NULL, NULL, 4, NULL, NULL, NULL, NULL, '0', '0', NULL, '0.00', NULL),
(2, 4, 1, '2019-06-18 15:33:30', NULL, NULL, NULL, NULL, 2, NULL, NULL, NULL, NULL, '10', '10', '2019-06-04 00:00:00', '10.00', NULL),
(3, 7, 4, '2019-06-18 15:34:05', 2, 4, NULL, NULL, NULL, '15', '20', NULL, NULL, '0', '110', NULL, NULL, NULL),
(4, 3, 1, '2019-06-18 11:26:12', 4, NULL, NULL, NULL, NULL, '6', NULL, NULL, NULL, '0', '24', NULL, NULL, '2019-06-18 09:26:12'),
(5, 3, 3, '2019-06-18 10:43:03', 3, NULL, NULL, NULL, NULL, '6', NULL, NULL, NULL, '0', '18', '2019-06-10 00:00:00', '18.00', NULL),
(6, 2, 3, '2019-06-17 13:26:34', NULL, NULL, NULL, NULL, 4, NULL, NULL, NULL, NULL, '10', '10', NULL, NULL, NULL),
(7, 6, 1, '2019-06-27 14:32:18', 1, 1, NULL, NULL, NULL, '18', '25', NULL, NULL, '0', '43', NULL, NULL, '2019-06-27 12:32:18'),
(9, 7, 5, '2019-06-27 14:21:48', 2, NULL, NULL, NULL, NULL, '15', '20', NULL, NULL, '0', '30', NULL, NULL, NULL),
(10, 8, 1, '2019-07-08 10:50:30', 2, NULL, NULL, NULL, NULL, '4', '5', NULL, NULL, '0', '7', NULL, NULL, NULL),
(16, 2, 1, '2019-07-08 15:08:14', NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, '0', '0', NULL, NULL, NULL),
(17, 3, 1, '2019-07-09 10:08:02', 2, NULL, NULL, NULL, NULL, '6', NULL, NULL, NULL, '0', '12', NULL, NULL, NULL);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `evt_events`
--
ALTER TABLE `evt_events`
  ADD CONSTRAINT `image_id` FOREIGN KEY (`image_id`) REFERENCES `evt_images` (`image_id`),
  ADD CONSTRAINT `location_id` FOREIGN KEY (`location_id`) REFERENCES `evt_locations` (`location_id`);

--
-- Contraintes pour la table `evt_tickets`
--
ALTER TABLE `evt_tickets`
  ADD CONSTRAINT `event_id` FOREIGN KEY (`event_id`) REFERENCES `evt_events` (`event_id`),
  ADD CONSTRAINT `evt_account_id` FOREIGN KEY (`evt_account_id`) REFERENCES `evt_accounts` (`evt_account_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
