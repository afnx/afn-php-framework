# afn-php-framework
AFN Custom PHP Framework

You need to install docker to run project.


## INSTALL

Install docker and docker-compose.

Start docker.

Create a folder named Sites inside user folder.

Create another folder named task inside Sites.

Decompress project_task.zip inside task.

Go to project folder with Terminal:.

```
$ cd Users/you/Sites/task
```
Enter this command:
```
$ docker-compose up
```


## RE-INSTALL AFTER EDITING

Go to project folder:
$ cd Users/you/Projects/dev/afn
Enter this command:
```
$ docker-compose up -d --build
```


## HINTS

- To access web site with browser, go to http://localhost or http://localhost:[port].

- Change port for different projects in docker-compose.yml as the following:
    ports:
```
- "9142:80"
- "4254:443"
```

- To run project:
```
$ cd User/you/Projects/dev/afn
$ docker-compose up -d
```

- To access web server:
```
$ cd Users/you/Projects/dev/afn/www
```

- To access MySQL database:
```
Host: 127.0.0.1:3306 (127.0.0.1 for MySQL Workbench)
Username: root
Password: test
```
To access MySQL db from localhost, enter docker inspect ```task_db_1``` on Terminal and find IPAddress and then change 127.0.0.1 with it

- To get container ip:
```
$ docker inspect <container-id>
```

- To add a php extension or an apache module or others to project, edit Dockerfile inside 
```Users/you/Sites/task/server```.

- To access server command line(change <container-id> into id of the container which contain centos 7):
```
$ docker exec -it <container-id> /bin/bash
```

- To view all containers:
```
$ docker-compose ps
```
