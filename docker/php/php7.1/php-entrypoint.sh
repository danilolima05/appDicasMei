#!/bin/sh
set -e

# Change www-data`s UID inside a Dockerfile
usermod -u $LOCAL_UID www-data
echo "Changing www-data UID to ${LOCAL_UID}."
# chown -R $LOCAL_UID:$LOCAL_GID /var/www/html
# echo "Changed /var/www/html UID and GID to $LOCAL_UID and $LOCAL_GID."
if [ -d '/var/www/.composer' ]; then
    chown -R $LOCAL_UID:$LOCAL_GID /var/www/.composer
fi

# Adjusts SSH key to both users
cp -R /tmp/.ssh /var/www
cp -R /tmp/.ssh /root
chown -R $LOCAL_UID:$LOCAL_GID /var/www/.ssh
chmod -R 600 /var/www/.ssh/*
chown -R root:root /root/.ssh
chmod -R 600 /root/.ssh/*


# Apache2 foreground
/usr/sbin/apache2ctl -D FOREGROUND
exec "$@"
