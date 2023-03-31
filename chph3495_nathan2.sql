-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : ven. 31 mars 2023 à 12:06
-- Version du serveur : 10.6.12-MariaDB
-- Version de PHP : 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `chph3495_nathan2`
--

-- --------------------------------------------------------

--
-- Structure de la table `articles`
--

CREATE TABLE `articles` (
  `Id_articles` int(11) NOT NULL,
  `content` text DEFAULT NULL,
  `date_publi` datetime DEFAULT current_timestamp(),
  `Id_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `articles`
--

INSERT INTO `articles` (`Id_articles`, `content`, `date_publi`, `Id_user`) VALUES
(1, 'Premier article de Alice', '2023-03-01 00:00:00', 2),
(3, 'Deuxième article de Alice', '2023-03-03 00:00:00', 2),
(7, 'Premier article d\'hugo', '2023-03-29 11:33:34', 1),
(12, 'Deuxième article d\'Hugo', '2023-03-31 11:35:53', 1),
(13, 'Premier article d\'Auxence', '2023-03-31 11:36:25', 5),
(14, 'Troisième article d\'Alice', '2023-03-31 11:38:18', 2);

--
-- Déclencheurs `articles`
--
DELIMITER $$
CREATE TRIGGER `deleteVoteBeforeArticle` BEFORE DELETE ON `articles` FOR EACH ROW DELETE FROM Voter WHERE OLD.id_articles = Voter.Id_articles
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `Id_user` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(500) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`Id_user`, `username`, `password`, `role`) VALUES
(1, 'Hugo', '$2y$10$cnZcZrvuMNGDiUIwoaknHeDPjKsB6LK.LTugTNpphYhWrh8AkOeCy', 'publisher'),
(2, 'Alice', '$2y$10$cnZcZrvuMNGDiUIwoaknHeDPjKsB6LK.LTugTNpphYhWrh8AkOeCy', 'publisher'),
(3, 'Bob', '$2y$10$cnZcZrvuMNGDiUIwoaknHeDPjKsB6LK.LTugTNpphYhWrh8AkOeCy', 'publisher'),
(4, 'Charlie', '$2y$10$cnZcZrvuMNGDiUIwoaknHeDPjKsB6LK.LTugTNpphYhWrh8AkOeCy', 'moderator'),
(5, 'Auxence', '$2y$10$cnZcZrvuMNGDiUIwoaknHeDPjKsB6LK.LTugTNpphYhWrh8AkOeCy', 'publisher'),
(6, 'Flavien', '$2y$10$cnZcZrvuMNGDiUIwoaknHeDPjKsB6LK.LTugTNpphYhWrh8AkOeCy', 'moderator');

-- --------------------------------------------------------

--
-- Structure de la table `Voter`
--

CREATE TABLE `Voter` (
  `Id_user` int(11) NOT NULL,
  `Id_articles` int(11) NOT NULL,
  `vote` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `Voter`
--

INSERT INTO `Voter` (`Id_user`, `Id_articles`, `vote`) VALUES
(1, 1, 1),
(1, 3, -1),
(1, 14, 1),
(2, 13, 1),
(3, 14, 1),
(4, 3, -1),
(5, 13, 1),
(5, 14, -1);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`Id_articles`),
  ADD KEY `Id_user` (`Id_user`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`Id_user`);

--
-- Index pour la table `Voter`
--
ALTER TABLE `Voter`
  ADD PRIMARY KEY (`Id_user`,`Id_articles`),
  ADD KEY `Id_articles` (`Id_articles`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `articles`
--
ALTER TABLE `articles`
  MODIFY `Id_articles` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `Id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `Voter`
--
ALTER TABLE `Voter`
  ADD CONSTRAINT `Voter_ibfk_1` FOREIGN KEY (`Id_user`) REFERENCES `users` (`Id_user`),
  ADD CONSTRAINT `Voter_ibfk_2` FOREIGN KEY (`Id_articles`) REFERENCES `articles` (`Id_articles`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
