![Probance](https://scontent-cdg4-1.xx.fbcdn.net/v/t39.30808-6/301129152_482102583922818_7454434102963438205_n.jpg?stp=dst-jpg_s960x960&_nc_cat=102&ccb=1-7&_nc_sid=5f2048&_nc_ohc=xHgpvFexoI8Q7kNvgEL7-2I&_nc_ht=scontent-cdg4-1.xx&oh=00_AYD3tSlLhNbfUZHlnvjvQOtIuq9cmZFQqifhRezR3yXBFA&oe=6670E9E9)
# Connecteur Magento 2 - Probance

Ce module vous permet de connecter votre site e-commerce Magento 2 avec la solution de Marketing Automation [Probance](https://probance.com/fr/)

## Fonctionnalités
Les fonctionnalités du module sont :

- Export de flux sous forme de fichiers csv configurables entièrement par le Back-Office, envoyés par SFTP sur le serveur Probance
	- Clients / Inscriptions Newsletter
	- Paniers
	- Commandes
	- Catalogue produit
- Configuration des crons de lancement des exports (par heure, journalier ou les deux)
- Insertion d'un script JS pour le webtracking
- Logs accessibles via le Back-office
- Possibilité de lancer des exports pour test via le back-office
- Lignes de commandes pour 
	- exporter, 
	- inspecter les attributs des entités
	- renvoyer un fichier vers le SFTP

## Flux
***Aucun flux entrant*** vers Magento, les données sont envoyées par le site vers Probance.

## Performances
### Front office
Le seul impact sur le Front Office réside dans l'ajout d'un fichier .js pour permettre le tracking du client.
### Back office
Depuis la version 1.5, les performances d'export ont été nettement améliorées afin de garantir une utilisation raisonnée de la mémoire même dans le cas de gros volumes de données.
## Compatibilité
Le module a été testé sous PHP 7.2, 8.1, 8.2.
Il est compatible avec toutes les versions Magento 2 v2.3.x, v2.4.x
