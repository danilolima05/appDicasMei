appDicasMei
===========

A Symfony project created on May 14, 2020, 6:18 pm.

To install the project just need to do a clone and follow the steps:

1 - Make sure there is no services using the port 80

2 - Go to the project folder and build the docker

    `docker-compose up --build -d`

3 - Enter in the php container using the apache user 

    `docker exec -it --user=www-data dev-php bash`

4 - Go to inside the project (cd appDicasMei) and run the follows commands: 

    `composer install`

5 - In case that you ran the composer as root, please execute the follow command:     

     `chown -R $LOCAL_UID:$LOCAL_GID /var/www/html/appDicasMei/var/ -R`

6 - Then run the follow commands to create the schema and populate the predefined data

     `bin/console doctrine:database:create && bin/console doctrine:schema:update -f`
     `bin/console doctrine:fixtures:load --append --fixtures=src/ApiBundle/DataFixtures`
    
