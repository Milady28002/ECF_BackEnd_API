-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 27 avr. 2026 à 12:38
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `db_vite_gourmand`
--

-- --------------------------------------------------------

--
-- Structure de la table `allergene`
--

CREATE TABLE `allergene` (
  `allergene_id` int(11) NOT NULL,
  `libelle` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `allergene`
--

INSERT INTO `allergene` (`allergene_id`, `libelle`) VALUES
(1, 'Fruits à coque'),
(2, 'Arachide'),
(3, 'Crevettes'),
(4, 'Oeufs'),
(5, 'Graines de sésame');

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

CREATE TABLE `avis` (
  `avis_id` int(11) NOT NULL,
  `note` int(11) NOT NULL,
  `description` longtext NOT NULL,
  `statut` varchar(50) NOT NULL,
  `date_creation` datetime NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `commande_numero` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `avis`
--

INSERT INTO `avis` (`avis_id`, `note`, `description`, `statut`, `date_creation`, `utilisateur_id`, `commande_numero`) VALUES
(8, 5, 'Le menu était parfait et excellent!', 'valide', '2026-04-01 13:37:25', 22, 'CMDF90385'),
(9, 5, 'Je recommande le menu classique premium si vous voulez bien manger même seule 😉', 'valide', '2026-04-01 15:05:44', 22, 'CMD11E8F4'),
(10, 5, 'Je suis ravi du choix du menu réalisé par Julie et José, c\'était très savoureux et tellement bon. Merci.', 'valide', '2026-04-02 14:32:04', 21, 'CMD9F2753'),
(11, 5, 'Les plats étaient très savoureux 😊', 'en_attente', '2026-04-08 15:21:46', 27, 'CMD17AE0A');

-- --------------------------------------------------------

--
-- Structure de la table `commande`
--

CREATE TABLE `commande` (
  `numero_commande` varchar(50) NOT NULL,
  `date_commande` date NOT NULL,
  `date_prestation` date NOT NULL,
  `heure_livraison` varchar(50) NOT NULL,
  `adresse_livraison` varchar(255) NOT NULL,
  `prix_menu` double NOT NULL,
  `prix_livraison` double NOT NULL,
  `nombre_personnes` int(11) NOT NULL,
  `statut` varchar(50) NOT NULL,
  `motif_annulation` longtext DEFAULT NULL,
  `mode_contact_annulation` varchar(50) DEFAULT NULL,
  `pret_materiel` tinyint(4) NOT NULL,
  `restitution_materiel` tinyint(4) NOT NULL,
  `utilisateur_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commande`
--

INSERT INTO `commande` (`numero_commande`, `date_commande`, `date_prestation`, `heure_livraison`, `adresse_livraison`, `prix_menu`, `prix_livraison`, `nombre_personnes`, `statut`, `motif_annulation`, `mode_contact_annulation`, `pret_materiel`, `restitution_materiel`, `utilisateur_id`) VALUES
('CMD11E8F4', '2026-04-01', '2026-04-02', '20:00', '12 Route Zone 51, 33200 Bordeaux', 29.9, 0, 1, 'terminee', NULL, NULL, 0, 0, 22),
('CMD17AE0A', '2026-04-08', '2026-04-18', '20:00', '13 Allée du Moulin, 33800 Bordeaux', 134.46, 0, 6, 'terminee', NULL, NULL, 0, 0, 27),
('CMD2DFDCE', '2026-04-03', '2026-04-06', '19:00', '2 Route de l\'Avocat, 33600 Pessac', 24.9, 9.13, 1, 'en_attente', NULL, NULL, 0, 0, 24),
('CMD2E82FA', '2026-04-02', '2026-04-03', '20:00', '24 Impasse du Style, 33000 Bordeaux', 31.8, 0, 2, 'terminee', NULL, NULL, 0, 0, 23),
('CMD333B29', '2026-04-08', '2026-04-18', '20:00', '13 Allée du Moulin, 33800 Bordeaux', 134.46, 0, 6, 'en_attente', NULL, NULL, 0, 0, 27),
('CMD334F80', '2026-04-03', '2026-04-04', '19:00', '2 Route de l\'Avocat, 33600 Pessac', 18.9, 9.13, 1, 'livree', NULL, NULL, 0, 0, 24),
('CMD9F2753', '2026-04-02', '2026-04-18', '12:00', '13 Allée de la Source, 33210 Langon', 2020.5, 10.9, 50, 'terminee', NULL, NULL, 0, 0, 21),
('CMDA3545D', '2026-04-13', '2026-05-02', '12:00', '12 Route Zone 51, 33200 Bordeaux', 429, 0, 10, 'en_attente', NULL, NULL, 1, 0, 22),
('CMDAE294B', '2026-04-03', '2026-04-11', '12:30', '24 Impasse du Style, 33000 Bordeaux', 319.2, 0, 8, 'retour_materiel', NULL, NULL, 1, 0, 23),
('CMDC76A7D', '2026-04-01', '2026-04-02', '19:30', '12 Route Zone 51, 33200 Bordeaux', 224.1, 0, 10, 'annulee', NULL, NULL, 0, 0, 22),
('CMDE9D4E2', '2026-04-08', '2026-04-18', '20:00', '13 Allée du Moulin, 33800 Bordeaux', 134.46, 0, 6, 'annulee', NULL, NULL, 0, 0, 27),
('CMDF90385', '2026-04-01', '2026-04-04', '19:30', '12 Route Zone 51, 33200 Bordeaux', 102.06, 0, 6, 'terminee', NULL, NULL, 0, 0, 22);

-- --------------------------------------------------------

--
-- Structure de la table `commande_menu`
--

CREATE TABLE `commande_menu` (
  `commande_id` varchar(50) NOT NULL,
  `menu_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commande_menu`
--

INSERT INTO `commande_menu` (`commande_id`, `menu_id`) VALUES
('CMD11E8F4', 1),
('CMD17AE0A', 5),
('CMD2DFDCE', 5),
('CMD2E82FA', 10),
('CMD333B29', 5),
('CMD334F80', 4),
('CMD9F2753', 9),
('CMDA3545D', 7),
('CMDAE294B', 6),
('CMDC76A7D', 5),
('CMDE9D4E2', 5),
('CMDF90385', 4);

-- --------------------------------------------------------

--
-- Structure de la table `commande_statut_historique`
--

CREATE TABLE `commande_statut_historique` (
  `id` int(11) NOT NULL,
  `ancien_statut` varchar(50) NOT NULL,
  `nouveau_statut` varchar(50) NOT NULL,
  `date_changement` datetime NOT NULL,
  `commande_numero` varchar(50) NOT NULL,
  `utilisateur_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commande_statut_historique`
--

INSERT INTO `commande_statut_historique` (`id`, `ancien_statut`, `nouveau_statut`, `date_changement`, `commande_numero`, `utilisateur_id`) VALUES
(50, 'creation', 'en_attente', '2026-04-01 13:33:19', 'CMDF90385', 22),
(51, 'en_attente', 'acceptee', '2026-04-01 13:36:08', 'CMDF90385', 19),
(52, 'acceptee', 'en_preparation', '2026-04-01 13:36:18', 'CMDF90385', 19),
(53, 'en_preparation', 'en_livraison', '2026-04-01 13:36:28', 'CMDF90385', 19),
(54, 'en_livraison', 'livree', '2026-04-01 13:36:38', 'CMDF90385', 19),
(55, 'livree', 'terminee', '2026-04-01 13:36:49', 'CMDF90385', 19),
(56, 'creation', 'en_attente', '2026-04-01 14:56:33', 'CMD11E8F4', 22),
(57, 'en_attente', 'acceptee', '2026-04-01 14:58:20', 'CMD11E8F4', 19),
(58, 'acceptee', 'en_preparation', '2026-04-01 14:58:34', 'CMD11E8F4', 19),
(59, 'en_preparation', 'en_livraison', '2026-04-01 14:58:44', 'CMD11E8F4', 19),
(60, 'en_livraison', 'livree', '2026-04-01 14:58:55', 'CMD11E8F4', 19),
(61, 'livree', 'terminee', '2026-04-01 14:59:07', 'CMD11E8F4', 19),
(62, 'creation', 'en_attente', '2026-04-01 16:28:44', 'CMDC76A7D', 22),
(63, 'creation', 'en_attente', '2026-04-02 10:59:53', 'CMD9F2753', 21),
(64, 'en_attente', 'acceptee', '2026-04-02 13:59:07', 'CMD9F2753', 19),
(65, 'acceptee', 'en_preparation', '2026-04-02 13:59:18', 'CMD9F2753', 19),
(66, 'en_preparation', 'en_livraison', '2026-04-02 13:59:27', 'CMD9F2753', 19),
(67, 'en_livraison', 'livree', '2026-04-02 13:59:35', 'CMD9F2753', 19),
(68, 'livree', 'terminee', '2026-04-02 13:59:43', 'CMD9F2753', 19),
(69, 'creation', 'en_attente', '2026-04-02 16:21:22', 'CMD2E82FA', 23),
(70, 'en_attente', 'acceptee', '2026-04-02 16:22:01', 'CMD2E82FA', 19),
(71, 'acceptee', 'en_preparation', '2026-04-02 16:22:07', 'CMD2E82FA', 19),
(72, 'en_preparation', 'en_livraison', '2026-04-02 16:24:19', 'CMD2E82FA', 19),
(73, 'en_livraison', 'livree', '2026-04-02 16:35:18', 'CMD2E82FA', 19),
(74, 'livree', 'terminee', '2026-04-02 16:35:24', 'CMD2E82FA', 19),
(75, 'creation', 'en_attente', '2026-04-03 08:51:22', 'CMDAE294B', 23),
(76, 'en_attente', 'acceptee', '2026-04-03 09:45:16', 'CMDAE294B', 19),
(77, 'acceptee', 'en_preparation', '2026-04-03 10:25:55', 'CMDAE294B', 19),
(78, 'en_preparation', 'en_livraison', '2026-04-03 10:26:08', 'CMDAE294B', 19),
(79, 'en_livraison', 'livree', '2026-04-03 10:26:19', 'CMDAE294B', 19),
(80, 'livree', 'retour_materiel', '2026-04-03 10:26:31', 'CMDAE294B', 19),
(81, 'livree', 'retour_materiel', '2026-04-03 11:01:05', 'CMDAE294B', 20),
(82, 'creation', 'en_attente', '2026-04-03 11:13:55', 'CMD334F80', 24),
(83, 'en_attente', 'acceptee', '2026-04-03 11:19:36', 'CMD334F80', 19),
(84, 'acceptee', 'en_preparation', '2026-04-03 11:19:46', 'CMD334F80', 19),
(85, 'en_preparation', 'en_livraison', '2026-04-03 11:19:55', 'CMD334F80', 19),
(86, 'en_livraison', 'livree', '2026-04-03 11:20:06', 'CMD334F80', 19),
(87, 'creation', 'en_attente', '2026-04-03 11:43:46', 'CMD2DFDCE', 24),
(88, 'creation', 'en_attente', '2026-04-08 14:55:31', 'CMD333B29', 27),
(89, 'creation', 'en_attente', '2026-04-08 15:07:58', 'CMDE9D4E2', 27),
(90, 'creation', 'en_attente', '2026-04-08 15:09:53', 'CMD17AE0A', 27),
(91, 'en_attente', 'acceptee', '2026-04-08 15:12:21', 'CMD17AE0A', 19),
(92, 'acceptee', 'en_preparation', '2026-04-08 15:12:32', 'CMD17AE0A', 19),
(93, 'en_preparation', 'en_livraison', '2026-04-08 15:15:27', 'CMD17AE0A', 19),
(94, 'en_livraison', 'livree', '2026-04-08 15:15:32', 'CMD17AE0A', 19),
(95, 'livree', 'terminee', '2026-04-08 15:15:36', 'CMD17AE0A', 19),
(96, 'creation', 'en_attente', '2026-04-13 09:29:14', 'CMDA3545D', 22);

-- --------------------------------------------------------

--
-- Structure de la table `horaire`
--

CREATE TABLE `horaire` (
  `horaire_id` int(11) NOT NULL,
  `jour` varchar(50) NOT NULL,
  `heure_ouverture` varchar(50) NOT NULL,
  `heure_fermeture` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `horaire`
--

INSERT INTO `horaire` (`horaire_id`, `jour`, `heure_ouverture`, `heure_fermeture`) VALUES
(1, 'Lundi', '09:00', '18:00'),
(2, 'Mardi', '09:00', '18:00'),
(3, 'Mercredi', '09:00', '18:00'),
(4, 'Jeudi', '09:00', '18:00'),
(5, 'Vendredi', '09:00', '18:00'),
(6, 'Samedi', '09:00', '12:00'),
(7, 'Dimanche', 'fermé', 'fermé');

-- --------------------------------------------------------

--
-- Structure de la table `image_galerie`
--

CREATE TABLE `image_galerie` (
  `image_id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `categorie` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `image_galerie`
--

INSERT INTO `image_galerie` (`image_id`, `titre`, `url`, `categorie`, `created_at`) VALUES
(2, 'Asperges et canard', '../assets/asperges-galerie.jpg', 'sale', '2026-03-25 14:24:58'),
(3, 'Burgers maison', '../assets/burgers-galerie.jpg', 'sale', '2026-03-25 14:24:58'),
(4, 'Crevettes marinées', '../assets/crevettes-galerie.jpg', 'sale', '2026-03-25 14:24:58'),
(5, 'Pâtes thaïlandaises', '../assets/thai-galerie.jpg', 'sale', '2026-03-25 14:24:58'),
(6, 'Sushis', '../assets/sushi-galerie.jpg', 'cocktail', '2026-03-25 14:24:58'),
(7, 'Truites aux herbes', '../assets/poisson-galerie.png', 'sale', '2026-03-25 14:24:58'),
(8, 'Velouté de butternut', '../assets/veloute-galerie.jpg', 'sale', '2026-03-25 14:24:58'),
(9, 'Tacos au poulet', '../assets/tacos-galerie.jpg', 'sale', '2026-03-25 14:24:58'),
(10, 'Mousse à la framboise', '../assets/framboises-galerie.jpg', 'sucre', '2026-03-25 14:24:58'),
(11, 'Roulé à la fraise', '../assets/strawberry-roll-galerie.jpg', 'sucre', '2026-03-25 14:24:58'),
(12, 'Pancakes', '../assets/pancake-galerie.jpg', 'sucre', '2026-03-25 14:24:58'),
(13, 'Mousse à la mangue', '../assets/mousse-mangue-galerie.jpg', 'cocktail', '2026-03-25 14:24:58'),
(14, 'Agneau confit', '../assets/agneau.jpg', 'sale', '2026-03-25 14:24:58'),
(15, 'Fondant au chocolat', '../assets/images/menus/fondant-chocolat.jpg', 'sucre', '2026-03-25 14:24:58'),
(16, 'Gâteau mousse aux fruits', '../assets/images/menus/petits-gateaux.jpg', 'sucre', '2026-03-25 14:24:58'),
(17, 'Petits légumes mijotés', '../assets/images/menus/petits_legumes_mijotés.jpg', 'sale', '2026-03-25 14:24:58'),
(19, 'Tajine boeuf', '../assets/images/menus/tajine.jpg', 'cocktail', '2026-03-25 14:24:58'),
(20, 'Verrines crème et fruits', '../assets/images/menus/verrine-festive.jpg', 'cocktail', '2026-03-25 14:24:58'),
(21, 'Assiette garnie', '../assets/images/menus/assiette-garnie.jpg', 'cocktail', '2026-03-25 14:24:58'),
(22, 'Canapés variés', '../assets/images/menus/canapés.jpg', 'sale', '2026-03-25 14:33:17'),
(23, 'Gratin de courgettes', '../assets/images/menus/gratin_courgettes.jpg', 'sale', '2026-03-25 14:35:31'),
(24, 'Ordoeuvres surprises', '../assets/images/menus/ordoeuvres.jpg', 'cocktail', '2026-03-25 14:36:27'),
(25, 'Suprême de poulet', '../assets/images/menus/supreme.jpg', 'sale', '2026-03-25 14:37:30'),
(27, 'Super sandwich club', '../assets/images/menus/sandwiches.png', 'sale', '2026-03-25 15:31:56'),
(28, 'Spaghettis à l\'italienne', '../assets/spaghetti-menus.jpg', 'sale', '2026-03-25 15:33:37');

-- --------------------------------------------------------

--
-- Structure de la table `menu`
--

CREATE TABLE `menu` (
  `menu_id` int(11) NOT NULL,
  `titre` varchar(50) NOT NULL,
  `nombre_personne_minimum` int(11) NOT NULL,
  `prix_par_personne` double NOT NULL,
  `description` longtext NOT NULL,
  `quantite_restante` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `conditions_menu` longtext DEFAULT NULL,
  `regime_id` int(11) NOT NULL,
  `theme_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `menu`
--

INSERT INTO `menu` (`menu_id`, `titre`, `nombre_personne_minimum`, `prix_par_personne`, `description`, `quantite_restante`, `image`, `conditions_menu`, `regime_id`, `theme_id`) VALUES
(1, 'Menu Classique Premium', 1, 29.9, 'Menu complet convivial amélioré à savourer seul ou à plusieurs.', 14, '/assets/images/menus/menu-classique.jpg', 'Commande 24h avant ', 1, 1),
(2, 'Menu Noël Prestige', 8, 39.9, 'Menu festif gourmand et savoureux pour Noël et Pâques', 20, '/assets/images/menus/menu-noel.png', 'Commande 7 jours avant ', 1, 2),
(3, 'Menu Evénement', 20, 49.9, 'Menu authentique et convivial idéal pour anniversaires et réceptions.', 40, '/assets/images/menus/menu-evenement.jpg', 'Commande 7 jours avant ', 1, 3),
(4, 'Menu Classique Solo', 1, 18.9, 'Menu individuel simple et gourmand, idéal pour un déjeuner ou un dîner en toute simplicité.', 9, '/assets/images/menus/veloute-galerie.jpg', 'Commande 24h avant ', 2, 1),
(5, 'Menu Classique Gourmand', 1, 24.9, 'Menu classique plus généreux, parfait pour repas en solo ou en petit comité.', 7, '/assets/images/menus/thai-galerie.jpg', 'Commande 24h avant ', 1, 1),
(6, 'Menu Noël Authentique', 8, 39.9, 'Menu festif généreux pour les repas de Noël ou grandes occasions de fin d’année.', 19, '/assets/images/menus/asperges-galerie.jpg', 'Commande 7 jours avant ', 1, 2),
(7, 'Menu Pâques Gourmand', 8, 42.9, 'Menu printanier savoureux, pensé pour célébrer Pâques en famille ou entre amis.', 19, '/assets/images/menus/agneau.jpg', 'Commande 7 jours avant ', 1, 2),
(8, 'Menu Réception', 20, 49.9, 'Menu idéal pour anniversaires, réceptions et événements conviviaux.', 100, '/assets/images/menus/reception.jpg', 'Commande 7 jours avant ', 1, 3),
(9, 'Menu Cocktail Festif', 20, 44.9, 'Menu événementiel pensé pour cocktails, lancements et soirées professionnelles ou privées.', 99, '/assets/images/menus/sushi-galerie.jpg', 'Commande 7 jours avant ', 1, 3),
(10, 'Menu Classique Végétarien', 1, 15.9, 'Menu du jour totalement végétarien à savourer seul ou à plusieurs.', 9, '/assets/images/menus/houmous.jpg', 'Commande 24h avant ', 2, 1),
(11, 'Menu Noël Vegan', 10, 34.9, 'Menu végan authentique pour régaler et émerveiller vos papilles.', 20, '/assets/images/menus/petits_legumes_mijotés.jpg', 'Commande 7 jours avant ', 3, 2),
(12, 'Menu Festif Végétarien', 12, 29.9, 'Menu gourmand pour des repas conviviaux et de grandes occasions.', 40, '/assets/images/menus/gratin_courgettes.jpg', 'Commande 7 jours avant ', 2, 3),
(14, 'Menu spécial Noël', 6, 49.9, 'Menu aux multiples saveurs authentiques qui va vous régaler les papilles.', 20, '/assets/images/menus/noel2.png', 'Commande 7 jours avant ', 1, 2);

-- --------------------------------------------------------

--
-- Structure de la table `menu_plat`
--

CREATE TABLE `menu_plat` (
  `menu_id` int(11) NOT NULL,
  `plat_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `menu_plat`
--

INSERT INTO `menu_plat` (`menu_id`, `plat_id`) VALUES
(1, 3),
(1, 9),
(1, 27),
(2, 7),
(2, 22),
(2, 23),
(3, 12),
(3, 21),
(3, 22),
(4, 9),
(4, 27),
(4, 29),
(5, 5),
(5, 12),
(5, 27),
(6, 2),
(6, 4),
(6, 23),
(7, 12),
(7, 13),
(7, 15),
(8, 10),
(8, 16),
(8, 22),
(9, 4),
(9, 6),
(9, 10),
(10, 12),
(10, 13),
(10, 19),
(11, 17),
(11, 18),
(11, 23),
(12, 10),
(12, 13),
(12, 19),
(14, 24),
(14, 25),
(14, 26);

-- --------------------------------------------------------

--
-- Structure de la table `plat`
--

CREATE TABLE `plat` (
  `plat_id` int(11) NOT NULL,
  `titre_plat` varchar(50) NOT NULL,
  `type_plat` varchar(20) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `plat`
--

INSERT INTO `plat` (`plat_id`, `titre_plat`, `type_plat`, `image_url`) VALUES
(2, 'Asperges et canard', '2', '/assets/images/menus/asperges-galerie.jpg'),
(3, 'Burgers maison', '2', '/assets/images/menus/burgers-galerie.jpg'),
(4, 'Crevettes marinées', '1', '/assets/images/menus/crevettes-galerie.jpg'),
(5, 'Pâtes thaïlandaises', '2', '/assets/images/menus/thai-galerie.jpg'),
(6, 'Sushis', '2', '/assets/images/menus/sushi-galerie.jpg'),
(7, 'Truite aux herbes', '2', '/assets/images/menus/truite-galerie.png'),
(8, 'Tacos au poulet', '2', '/assets/images/menus/tacos-galerie.jpg'),
(9, 'Mousse à la framboise', '3', '/assets/images/menus/framboises-galerie.jpg'),
(10, 'Roulé à la fraise', '3', '/assets/images/menus/strawberry-roll-galerie.jpg'),
(11, 'Pancakes', '3', '/assets/images/menus/pancake-galerie.jpg'),
(12, 'Mousse à la mangue', '3', '/assets/images/menus/mousse-mangue-galerie.jpg'),
(13, 'Houmous', '1', '/assets/images/menus/houmous.jpg'),
(14, 'Crème de potimarron', '1', '/assets/images/menus/potimarron.jpg'),
(15, 'Agneau confit', '2', '/assets/images/menus/agneau.jpg'),
(16, 'Tajin boeuf/canelle', '2', '/assets/images/menus/tajine.jpg'),
(17, 'Salade de poivrons', '1', '/assets/images/menus/salade_poivrons.jpg'),
(18, 'Petits légumes mijotés', '2', '/assets/images/menus/petits_legumes_mijotés.jpg'),
(19, 'Gratin de courgettes', '2', '/assets/images/menus/gratin_courgettes.jpg'),
(20, 'Assiette garnie', '2', '/assets/images/menus/assiette-garnie.jpg'),
(21, 'Ordoeuvres variées', '2', 'assets/images/menus/ordoeuvres.jpg'),
(22, 'Canapés variés', '1', 'assets/images/menus/canapés.jpg'),
(23, 'Fondant au chocolat', '3', 'assets/images/menus/fondant-chocolat.jpg'),
(24, 'Tartare de Saumon', '1', '/assets/images/menus/tartare.jpg'),
(25, 'Suprême de volaille', '2', '/assets/images/menus/supreme.jpg'),
(26, 'Verrine gourmande', '3', '/assets/images/menus/verrine-festive.jpg'),
(27, 'Velouté de légumes', '1', '/assets/images/menus/veloute-galerie.jpg'),
(28, 'Noël', '3', '/assets/images/menus/noel2.png'),
(29, 'Super sandwich club', '2', '/assets/images/menus/sandwiches.png'),
(40, 'Petits gâteaux mousse aux fruits', '3', '/assets/images/menus/petits-gateaux.jpg');

-- --------------------------------------------------------

--
-- Structure de la table `plat_allergene`
--

CREATE TABLE `plat_allergene` (
  `plat_id` int(11) NOT NULL,
  `allergene_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `plat_allergene`
--

INSERT INTO `plat_allergene` (`plat_id`, `allergene_id`) VALUES
(4, 3),
(5, 2),
(8, 4),
(9, 4),
(10, 4),
(11, 4),
(12, 4),
(13, 2),
(16, 1),
(18, 5),
(26, 1),
(40, 4);

-- --------------------------------------------------------

--
-- Structure de la table `regime`
--

CREATE TABLE `regime` (
  `regime_id` int(11) NOT NULL,
  `libelle` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `regime`
--

INSERT INTO `regime` (`regime_id`, `libelle`) VALUES
(1, 'Standard'),
(2, 'Végétarien'),
(3, 'Vegan');

-- --------------------------------------------------------

--
-- Structure de la table `role`
--

CREATE TABLE `role` (
  `role_id` int(11) NOT NULL,
  `libelle` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `role`
--

INSERT INTO `role` (`role_id`, `libelle`) VALUES
(1, 'ROLE_USER'),
(2, 'ROLE_EMPLOYE'),
(3, 'ROLE_ADMIN');

-- --------------------------------------------------------

--
-- Structure de la table `theme`
--

CREATE TABLE `theme` (
  `theme_id` int(11) NOT NULL,
  `libelle` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `theme`
--

INSERT INTO `theme` (`theme_id`, `libelle`) VALUES
(1, 'Classique'),
(2, 'Noel / Paques'),
(3, 'Evenement');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `utilisateur_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  `api_token` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL,
  `telephone` varchar(50) NOT NULL,
  `ville` varchar(50) DEFAULT NULL,
  `pays` varchar(50) DEFAULT NULL,
  `adresse_postale` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`utilisateur_id`, `name`, `firstname`, `email`, `password`, `is_active`, `api_token`, `reset_token`, `reset_token_expires_at`, `telephone`, `ville`, `pays`, `adresse_postale`) VALUES
(19, 'Admin', 'Super', 'admin@vitegourmand.fr', '$2y$10$Zaoa4ax50KGWYWjbiA4KuexzFNqXKRA3Idsfz7wMUTmgBAbcpa8ri', 1, '7e64ed0ff9aeb78cb5f997beeb34fdb2fc4863518ad79ae1672ee7fef0dbf0d0', NULL, NULL, '0600000000', 'Bordeaux', 'France', '1 rue de l Admin'),
(20, 'Employe', 'Gaby', 'gaby.e@vitegourmand.fr', '$2y$13$BqyPLsIO19dGQUO6Z1oFr.xVILa2WohQF5KSi5Wjsx1jNdM7EJ1/.', 1, '046fcb15a00dfec62c5d25ebf10df88db9c7d182d92958eb2e017efd62172035', NULL, NULL, 'Non renseigne', 'Non renseignee', 'France', 'Non renseignee'),
(21, 'Mulder', 'Fox', 'fox.mulder@user.com', '$2y$13$f0PjuWr7S55MCfSQRdleuu57A0.nMSPRBZj7BFfHdVs9hTUhF7g3C', 1, '53bb475537cb36d6fdc04b91c51f19eff5cde0df89d1d2b8544d2288e598c0d4', NULL, NULL, '0612061207', 'Pessac', 'France', '12 rue de Paradis'),
(22, 'Scully', 'Dana', 'dana.scully@user.com', '$2y$13$wscUQYpd1zHr2fdccFnMTOFW9j5QS1xQamPNuIHxiEioocTwuWOk2', 1, 'aba509d95e8ea24243399c20af5136eb327063f6ac524eff5576006f495d859b', NULL, NULL, '0651135113', NULL, NULL, '12 Route Zone 51, 33200 Bordeaux'),
(23, 'Fisher', 'Phryne', 'phryne.fisher@user.com', '$2y$13$zQW9S8EA3cYQCgI3b6Zkdu5TpqqSBc1UFH7XIa1nc5aSCP0EAwELO', 1, 'bdead0c7911f02a70ef26d09332c39c81bcbf4af50cac7aaa132abb60714c12e', NULL, NULL, '0698876554', NULL, NULL, '24 Impasse du Style, 33000 Bordeaux'),
(24, 'Wexler', 'Kim', 'kim.wexler@user.com', '$2y$13$Bf4MQ6BYBmTCL0HmFXON2eTwAsbMNRhZpuB1ko1ZExWlbjwJh6SIu', 1, 'ea60efff66339af6bf35dc019faf44d5623bd0b88167e9975e8eb37bc2736647', NULL, NULL, '0655445544', NULL, NULL, '2 Route de l\'Avocat, 33600 Pessac'),
(25, 'Del Rey', 'Lana', 'lana.e@vitegourmand.fr', '$2y$13$y3lQ0LcR6kXIdY5JnFzy1.4JwhEL0iySzezL2vlvdlOHqSEffXeh.', 1, '8a4fc8ca6407f6145f91115d48171b8415ca189c72fb3a42c8826a2605567220', NULL, NULL, 'Non renseigne', 'Non renseignee', 'France', 'Non renseignee'),
(26, 'Employe', 'Test', 'employe@vitegourmand.fr', '$2y$13$mJOAqeaYjX.ANj1nL9SnzOdIfyhBps1MUj/Q5XdkcXctUcgBegye.', 1, '065e027c8a7f435e9249ea26d211f433421b0794b2c7afbd5216b0cee97261d4', NULL, NULL, 'Non renseigne', 'Non renseignee', 'France', 'Non renseignee'),
(27, 'Meaulnes', 'Augustin', 'augustin.meaulnes@user.com', '$2y$13$63xp09B7.AkkGV8nUT/Jvu9ABv3FLl4cDQ/E/XK5izzZlLhVbFhqy', 1, '9e8b3a197ebb3e4a7e53fc4e0e23fc2ca67936999d04dc09a2cc786662bf5df9', NULL, NULL, '0619131913', NULL, NULL, '13 Allée de Sainte-Agathe, 33200 Bordeaux');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur_role`
--

CREATE TABLE `utilisateur_role` (
  `utilisateur_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur_role`
--

INSERT INTO `utilisateur_role` (`utilisateur_id`, `role_id`) VALUES
(19, 3),
(20, 2),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 2),
(26, 2),
(27, 1);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `allergene`
--
ALTER TABLE `allergene`
  ADD PRIMARY KEY (`allergene_id`);

--
-- Index pour la table `avis`
--
ALTER TABLE `avis`
  ADD PRIMARY KEY (`avis_id`),
  ADD KEY `FK_8F91ABF0FB88E14F` (`utilisateur_id`),
  ADD KEY `FK_8F91ABF0F70E1A4B` (`commande_numero`);

--
-- Index pour la table `commande`
--
ALTER TABLE `commande`
  ADD PRIMARY KEY (`numero_commande`),
  ADD KEY `FK_6EEAA67DFB88E14F` (`utilisateur_id`);

--
-- Index pour la table `commande_menu`
--
ALTER TABLE `commande_menu`
  ADD PRIMARY KEY (`commande_id`,`menu_id`),
  ADD KEY `FK_16693B70CCD7E912` (`menu_id`);

--
-- Index pour la table `commande_statut_historique`
--
ALTER TABLE `commande_statut_historique`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_634BFC75F70E1A4B` (`commande_numero`),
  ADD KEY `FK_634BFC75FB88E14F` (`utilisateur_id`);

--
-- Index pour la table `horaire`
--
ALTER TABLE `horaire`
  ADD PRIMARY KEY (`horaire_id`);

--
-- Index pour la table `image_galerie`
--
ALTER TABLE `image_galerie`
  ADD PRIMARY KEY (`image_id`);

--
-- Index pour la table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`menu_id`),
  ADD KEY `FK_7D053A9335E7D534` (`regime_id`),
  ADD KEY `FK_7D053A9359027487` (`theme_id`);

--
-- Index pour la table `menu_plat`
--
ALTER TABLE `menu_plat`
  ADD PRIMARY KEY (`menu_id`,`plat_id`),
  ADD KEY `FK_E8775249D73DB560` (`plat_id`);

--
-- Index pour la table `plat`
--
ALTER TABLE `plat`
  ADD PRIMARY KEY (`plat_id`);

--
-- Index pour la table `plat_allergene`
--
ALTER TABLE `plat_allergene`
  ADD PRIMARY KEY (`plat_id`,`allergene_id`),
  ADD KEY `FK_6FA44BBF4646AB2` (`allergene_id`);

--
-- Index pour la table `regime`
--
ALTER TABLE `regime`
  ADD PRIMARY KEY (`regime_id`);

--
-- Index pour la table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`role_id`);

--
-- Index pour la table `theme`
--
ALTER TABLE `theme`
  ADD PRIMARY KEY (`theme_id`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`utilisateur_id`);

--
-- Index pour la table `utilisateur_role`
--
ALTER TABLE `utilisateur_role`
  ADD PRIMARY KEY (`utilisateur_id`,`role_id`),
  ADD KEY `FK_9EE8E650D60322AC` (`role_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `allergene`
--
ALTER TABLE `allergene`
  MODIFY `allergene_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `avis`
--
ALTER TABLE `avis`
  MODIFY `avis_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `commande_statut_historique`
--
ALTER TABLE `commande_statut_historique`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT pour la table `horaire`
--
ALTER TABLE `horaire`
  MODIFY `horaire_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `image_galerie`
--
ALTER TABLE `image_galerie`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT pour la table `menu`
--
ALTER TABLE `menu`
  MODIFY `menu_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `plat`
--
ALTER TABLE `plat`
  MODIFY `plat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT pour la table `regime`
--
ALTER TABLE `regime`
  MODIFY `regime_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `role`
--
ALTER TABLE `role`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `theme`
--
ALTER TABLE `theme`
  MODIFY `theme_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `utilisateur_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `avis`
--
ALTER TABLE `avis`
  ADD CONSTRAINT `FK_8F91ABF0F70E1A4B` FOREIGN KEY (`commande_numero`) REFERENCES `commande` (`numero_commande`),
  ADD CONSTRAINT `FK_8F91ABF0FB88E14F` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`utilisateur_id`);

--
-- Contraintes pour la table `commande`
--
ALTER TABLE `commande`
  ADD CONSTRAINT `FK_6EEAA67DFB88E14F` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`utilisateur_id`);

--
-- Contraintes pour la table `commande_menu`
--
ALTER TABLE `commande_menu`
  ADD CONSTRAINT `FK_16693B7082EA2E54` FOREIGN KEY (`commande_id`) REFERENCES `commande` (`numero_commande`),
  ADD CONSTRAINT `FK_16693B70CCD7E912` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`menu_id`);

--
-- Contraintes pour la table `commande_statut_historique`
--
ALTER TABLE `commande_statut_historique`
  ADD CONSTRAINT `FK_634BFC75F70E1A4B` FOREIGN KEY (`commande_numero`) REFERENCES `commande` (`numero_commande`),
  ADD CONSTRAINT `FK_634BFC75FB88E14F` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`utilisateur_id`);

--
-- Contraintes pour la table `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `FK_7D053A9335E7D534` FOREIGN KEY (`regime_id`) REFERENCES `regime` (`regime_id`),
  ADD CONSTRAINT `FK_7D053A9359027487` FOREIGN KEY (`theme_id`) REFERENCES `theme` (`theme_id`);

--
-- Contraintes pour la table `menu_plat`
--
ALTER TABLE `menu_plat`
  ADD CONSTRAINT `FK_E8775249CCD7E912` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`menu_id`),
  ADD CONSTRAINT `FK_E8775249D73DB560` FOREIGN KEY (`plat_id`) REFERENCES `plat` (`plat_id`);

--
-- Contraintes pour la table `plat_allergene`
--
ALTER TABLE `plat_allergene`
  ADD CONSTRAINT `FK_6FA44BBF4646AB2` FOREIGN KEY (`allergene_id`) REFERENCES `allergene` (`allergene_id`),
  ADD CONSTRAINT `FK_6FA44BBFD73DB560` FOREIGN KEY (`plat_id`) REFERENCES `plat` (`plat_id`);

--
-- Contraintes pour la table `utilisateur_role`
--
ALTER TABLE `utilisateur_role`
  ADD CONSTRAINT `FK_9EE8E650D60322AC` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`),
  ADD CONSTRAINT `FK_9EE8E650FB88E14F` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`utilisateur_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
