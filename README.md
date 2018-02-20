# afn-php-framework
AFN Custom PHP Framework

You need to install docker to run project.


## INSTALL

Install docker and docker-compose.

Start docker.

Create a folder named ```Sites``` inside user folder.

Create another folder named ```framework``` inside ```Sites```.

Copy all files to ```framework```.

Go to project folder with Terminal:

```
$ cd Users/you/Sites/framework
```
Enter this command:
```
$ docker-compose up
```


## RE-INSTALL AFTER EDITING

Go to project folder:
```
$ cd Users/you/Sites/framework
```
Enter this command:
```
$ docker-compose up -d --build
```

## HOW TO CREATE NEW PAGE

Firstly call View class:
```
$view = new View();
```

After that, you should imply the path of view file(you don't need to enter full path. Ex: index Ex2: alerts/successful):
```
$view->view_file = "index";
```

If you want to add other template files to your actual page, you should push file paths into array object of add_files as the following:
```
$view->add_files = [
    'header' => 'header_fixed',
    'alert_box' => 'alert_box_error'
];
```

You can use new_entry function to add data. First parameter is type of your data which defines regex and second paramter is array of your data. Your data should be entered as a array value and it should have an unique identifier as a array key.
```
$view->new_entry(1, [
    'test' => 'This was a test data!',
    'test2' => 'Lorem ipsum dolor. Sit amet orci. In et molestie interdum vitae libero varius felis nunc pellentesque ut venenatis interdum volutpat vitae amet nec leo orci vulputate massa.',
    'test3' => 'Lorem ipsum dolor. Sit amet orci. In et molestie interdum vitae libero varius felis nunc pellentesque ut venenatis interdum volutpat vitae amet nec leo orci vulputate massa.',
]);
```

You can add loops to array of your data. It provides to push more than data. You have to give an unique name for each loop.
```
$view->new_entry(1, [
    'loop_1_1' => [
        'test' => 'I will',
        'test2' => 'love',
        'test3' => 'you',
    ],
    'loop_1_2' => [
        'test' => 'until',
        'test2' => 'I',
        'test3' => 'die',
    ],
]);
```

You can use new_entry function at choice, so you don't have to push all your data in one go.

Start view processes and echo view page:
```
echo $view->generate_markup();
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
$ cd User/you/Sites/framework
$ docker-compose up -d
```

- To access web directory:
```
$ cd Users/you/Sites/framework/www
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
```Users/you/Sites/framework/server```.

- To access server command line(change <container-id> into id of the container which contain centos 7):
```
$ docker exec -it <container-id> /bin/bash
```

- To view all containers:
```
$ docker-compose ps
```
