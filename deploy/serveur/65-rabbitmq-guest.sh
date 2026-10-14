#!/usr/bin/env bash
# Supprime le compte RabbitMQ `guest` (administrateur, mot de passe public `guest`), créé par le paquet.
#
# La boutique utilise son propre compte `madame-aiguille` sur le vhost `/madame-aiguille` (script 80). RabbitMQ
# étant partagé avec les autres services du VPS, le script s'arrête sans rien changer si une connexion ouverte
# utilise encore `guest`. Rejouable : sans compte `guest`, il ne fait rien. Usage :
#   ssh <vps> 'bash -s' < deploy/serveur/65-rabbitmq-guest.sh
set -euo pipefail
export LC_ALL=C.UTF-8

if ! sudo rabbitmqctl list_users -q | awk '{print $1}' | grep -qx guest; then
    echo "Compte guest absent : rien à faire."
    exit 0
fi

CONNEXIONS=$(sudo rabbitmqctl list_connections -q user | grep -cx guest || true)
if [ "$CONNEXIONS" -gt 0 ]; then
    echo "ARRÊT : $CONNEXIONS connexion(s) ouverte(s) avec guest. Basculer ce service sur un compte dédié d'abord."
    sudo rabbitmqctl list_connections -q user name client_properties | grep '^guest' || true
    exit 1
fi

sudo rabbitmqctl delete_user guest
echo "Comptes restants :"
sudo rabbitmqctl list_users -q
