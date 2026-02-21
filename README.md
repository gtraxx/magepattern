Magepattern
=============

![Image](https://github.com/user-attachments/assets/b3bc7d92-d157-4945-ac35-c33e62580928)

Presentation
------------
Le Toolkit PHP 8.2+ universel, modulaire et haute performance.
Magepattern 3 est une bibliothèque de composants indépendants conçue pour offrir une base technique solide, sécurisée et évolutive aux applications PHP modernes (notamment le futur Magix CMS 4).
Entièrement réécrite pour tirer parti de PHP 8.2+, cette version abandonne les structures de données rigides au profit d'une architecture orientée objet fluide, mettant l'accent sur la sécurité des données et la performance du cache.

## Points Forts
* Modernité & Typage : Architecture exploitant le typage strict de PHP 8.2+ pour une robustesse accrue.
* Query Collaboration : Un QueryBuilder fluide couplé à un QueryHelper unique permettant l'injection dynamique de briques SQL par des modules tiers (Plugin-Ready).
* Sécurité Native : Protection contre les injections SQL via PDO, hachage cryptographique SHA-256 pour le cache, et gestion des mots de passe conforme aux standards de sécurité actuels.
* Performance Optimisée : Système de cache intelligent avec invalidation par "Tags" pour un contrôle granulaire de la persistance.
* Zéro Dépendance : Conçu pour être agnostique et s'intégrer dans n'importe quel projet sans surcharge de dépendances externes.

Authors
-------

 * Gerits Aurelien (Author-Developer) contact[at]aurelien-gerits[dot]be

Requirements
------------

### Server
 * APACHE / IIS
 * PHP 8.x.x recommandé (Compatible uniquement à partir de cette version)
     * GD activé
     * SPL
     * SimpleXML et XML READER
     * PDO
     * CURL
 * MYSQL
 * MARIADB
 * Postgresql

## Licence

Ce projet est sous licence **GPLv3**. Voir le fichier [LICENSE](LICENSE) pour plus de détails.
Copyright (C) 2008 - 2026 Gerits Aurelien (Magix CMS)
Ce programme est un logiciel libre ; vous pouvez le redistribuer et/ou le modifier selon les termes de la Licence Publique Générale GNU telle que publiée par la Free Software Foundation ; soit la version 3 de la Licence, ou (à votre discrétion) toute version ultérieure.

---