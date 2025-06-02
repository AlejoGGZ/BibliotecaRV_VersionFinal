SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Base de datos: `biblioteca`

CREATE TABLE `bibliotecat` (
  `ID` int(11) NOT NULL,
  `palabra_clave` varchar(150) NOT NULL,
  `fecha` date NOT NULL,
  `link` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


ALTER TABLE `bibliotecat`
  ADD PRIMARY KEY (`ID`);


ALTER TABLE `bibliotecat`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;
COMMIT;

CREATE TABLE diarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL UNIQUE
);

ALTER TABLE bibliotecat
  ADD COLUMN diario_id INT DEFAULT NULL,
  ADD CONSTRAINT fk_diario FOREIGN KEY (diario_id) REFERENCES diarios(id) ON DELETE SET NULL;

  CREATE TABLE contraseña (
  id INT AUTO_INCREMENT PRIMARY KEY,
  clave VARCHAR(100) NOT NULL
);

INSERT INTO contraseña (clave) VALUES ('BIBLIO25');