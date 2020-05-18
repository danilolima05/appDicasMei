# appDicasMei
Test for the company Dicas Mei

### Usage
Download this repo and put it in edir's folder(root level), at the same level of the web folder. Goes to the folder and run:

Generate a .local.env file to make docker user match with your host's user
```shell
# on docker-dev folder
echo -e "LOCAL_UID=$(id -u $USERNAME)\nLOCAL_GID=$(id -g $USERNAME)" > .local.env
```

To run:
```shell
docker-compose up # you can use the flag "d" together. It hides logs message from up.
```

To run and force a Dockerfile build:
```shell
docker-compose up --build # you can use the flag "d" together. It hides logs message from up.
```

To assume docker's php with **www-data** user:
```shell
docker exec -it --user=www-data dev-php bash
```

If you want, after the installation you can Set your projects hosts on **/etc/hosts**
```
# /etc/hosts
127.0.0.1       testdicasmei.com
```

### Other commands

To stop:
```shell
docker-compose stop
```

To down:
```shell
docker-compose down
```