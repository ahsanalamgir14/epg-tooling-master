# How it Works

Docker builds an image containing the application in src/ and all of its dependencies by using the Dockerfile contained in this repository.

The Dockerfile tells docker to use the [official PHP Docker image](https://hub.docker.com/_/php/) as the parent image.

The PHP image then uses the [official Debian Jessie Docker image](https://hub.docker.com/_/debian/) as its parent image.

Debian then uses the [scratch image](https://hub.docker.com/_/scratch/) as its base image.

At this point, an image has been built which contains Apache, PHP and all of the OS dependencies and libraries required to serve a webpage written in PHP.

Finally, docker copies everything in src/ inside this repository to the /var/www/html folder inside the image. This is the Apache web root directory.

# Setup On Windows:
 
 - Ensure you have Docker installed and running
 - docker-compose build
 - docker-compose up

# Setup On Mac:

 - Ensure you have Docker installed and running
 - `sudo docker-compose build` 
 - `sudo docker-compose up`

# Possible Errors
 - If you received any error in build command regarding php, run the below command:
 - rm  ~/.docker/config.json 

# File sharing for mac

If you are using macOS add the project folder path in settings -> Resources -> File Sharing.

 - Click on plus button and add path e.g `/Applications/XAMPP/xamppfiles/htdocs epg-tooling-master`
 - Restart docker desktop so it can detect the new path and permissions. 

# What You Should See

You can access the application at: http://localhost:8080/
