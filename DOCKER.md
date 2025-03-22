# LibreKB Docker Deployment

## Prerequisites
Ensure you have the following installed on your system:
- Docker
- Docker Compose

## Clone the Project
```sh
git clone https://github.com/michaelstaake/LibreKB.git
cd LibreKB
```

## Create the `.env` File
Create a `.env` file in the project root and add the following:
```sh
MYSQL_ROOT_PASSWORD=your_root_password
MYSQL_DATABASE=librekb
MYSQL_USER=librekb_user
MYSQL_PASSWORD=securepassword
```

## Configure the Application
1. Locate the `config.docker-example.php` file.
2. Rename it to `config.php`.

## Start the Containers
Run the following command to start the containers:
```sh
docker-compose up -d
```
Wait for a minute or two to allow the containers to initialize.

## Install LibreKB
1. Open your browser and go to:
   ```
   http://localhost/install.php
   ```
2. Follow the installation steps to create an admin user.

## Cleanup Installation Files
Once the admin user is created, return to the terminal and stop the containers:
```sh
docker-compose down
```
Remove installation files:
```sh
rm install.php update.php
```

## Restart the Containers
Rebuild and start the containers again:
```sh
docker-compose up -d --build
```

## Access the Admin Panel
Visit the following URL in your browser:
```
http://localhost/admin
```
You're now ready to start using LibreKB!

