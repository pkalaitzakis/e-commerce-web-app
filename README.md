A distributed web app built using Docker, supporting functionality that resembles an e-commerce domain.

To run this application on your local machine, you must have Docker installed. Once docker is running,
open a terminal, navigate to the project's root folder where 'docker-compose.yml' is located, and run the following command:

docker-compose up -d

After the application has finished building, open up a browser and visit 'localhost/' to access it.

Anyone can sign up on the application by filling out the registration form under 'localhost/index.php'. Inside the form, along with some personal information, visiotrs will be asked to specify the role they wish to have in the app.

In order to successfully log in, account owners must provide a correct set of credentials (email & password), and also be confirmed (enabled) by an active administrator prior to the login action. 

You can use the following set of credentials to login to three distinct dummy accounts, each assigned a different role in the app:

-- admin@test.com
-- abc123456@

-- merchant@test.com
-- abc123456@

-- user@test.com
-- abc123456@

'Confirmed' users are the ones that have been granted the permissions pertaining to the role they requested upon registering. Administrators are responsible for confirming users through the administration.php page of the application.

# Dockerization
The architecture of the distributed application presented in this repo, is defined inside a docker-compose.yml file that resides in the project's root folder. This file contains all the nessecary instructions for docker to automatically pull the required images from dockerhub and build the containers using the specified architecture of the project. Due to this reason, the first time you run docker-compose it might take a while for the application to load. Here is a scheme of the architecture used:

![Application Architecture](https://github.com/pkalaitzakis/e-commerce-web-app/architecture.png)

# FIWARE's Keyrock Identity Management Service
User authentication and authorization is implemented by utilizing FIWARE's identity management service. In short, Keyrock is a FIWARE sevice that allows a Provider (super-admin) to register their domain as an Application, and define specific Roles with different Permissions regarding that application's resources and users. To this end, another docker container running MySQL is required for storing data detrimental to Keyrock's internal architecture. Each user's registration data is also stored inside the auto-generated idm database, under the 'description' column of the 'users' table, by seperating each field with a ', ' delimiter. If you plan on fiddling with the keyrock gui under localhost:3005, to see how everything is setup, be mindful that if the user description field is altered, the administration page of the front-end client might show unexpected behaviour. FIWARE provides a pre-built REST API that is utilized for communicating with Keyrock service while browsing the application, by forwarding HTTP requests to it with cURL in PHP.

Upon successfully logging in an OAuth2 token is generated by Keyrock and returned to the front-end client, where a new php session is initiallized, temporarily caching the token received for later usage.

# FIWARE'S Wilma PEP Proxy Service
Wilma PEP Proxy service acts as an intermediate protection layer between a client making a requests to other backend services, by challenging the requesting client's rights. For authorization to occur the header of the request needs to contain the OAuth2 token provided during login. The token is cross referenced with the Roles & Permissions configured using Keyrock, and based on those configurations Wilma decides whether the request should be forwarded to the target backend service.

# Data Storage (MongoDB REST-API)
Application data (with the exception of users' personal information, roles, permissions, etc., which resides in Keyrock) is being stored in a seperate MongoDB container and can be accessed by sending appropriate requests to this particular service. Data Storage is hosted by a standalone apache server that runs php code that converts HTTP requests (GET, POST, PATCH, DELETE) into appropriate queries to a NoSQL MongoDB database.
