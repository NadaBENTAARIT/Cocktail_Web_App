#!/bin/bash

# Installer Symfony CLI


sudo docker exec -it backend_symfony bash 
curl -sS https://get.symfony.com/cli/installer | bash
mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Accéder au répertoire du backend
cd backend

# Donner les permissions nécessaires
chmod -R 777 ./*

# Démarrer le serveur Symfony
symfony server:start
