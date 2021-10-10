# OCI x Shopware 6

## Data transfer objects

We rely on data transfer objects, to keep our business functionality indepentent and many other reasons. The DTOs will be generated based on a defined xml schema file.
You should create only **one** xml schema file each plugin as you can store several DTOs in on file.

* The schema files are located in: ```shopware-app/shared/DataProvider/Schema```
* The actual generated DTOs are then located in: ```shopware-app/shared/DataProvider/Generated```
* The generate process can be triggered within the project-directory with the command: ```bin/console dataprovider:generate```

Checkout <https://github.com/xervice/data-provider> for further information like available data types, nested DTOs, etc.

## Analysis & Testing commands

To trigger the static code analysis or run php unit you can use the following commands within the root-directory:

* Psalm local: ```shopware-app/vendor/bin/psalm -c shopware-app/config/static-analysis/psalm.xml --no-cache```
    * or from outside the app-container: ```docker exec -it oci_app.sw6_1 shopware-app/vendor/bin/psalm -c shopware-app/config/static-analysis/psalm.xml --no-cache```

* PHPStan local: ```shopware-app/vendor/bin/phpstan analyse -c shopware-app/config/static-analysis/phpstan.neon```
    * or from outside the app-container: ```docker exec -it oci_app.sw6_1 shopware-app/vendor/bin/phpstan analyse -c shopware-app/config/static-analysis/phpstan.neon```

* Phpcs local: ```shopware-app/vendor/bin/phpcs --standard=shopware-app/config/static-analysis/ruleset.xml shopware-app/custom --no-cache --colors```
    * or from outside the app-container: ```docker exec -it oci_app.sw6_1 shopware-app/vendor/bin/phpcs --standard=shopware-app/config/static-analysis/ruleset.xml shopware-app/custom --no-cache --colors```

* deptrac local: ```shopware-app/vendor/bin/deptrac analyze shopware-app/config/static-analysis/depfile.yaml --no-cache```
    * or from outside the app-container: ```docker exec -it oci_app.sw6_1 shopware-app/vendor/bin/deptrac analyze shopware-app/config/static-analysis/depfile.yaml --no-cache```

* phpmd local (no php8 support yet!): ```shopware-app/vendor/bin/phpmd shopware-app/custom/plugins/ ansi shopware-app/config/static-analysis/phpmd-ruleset.xml```
    * or from outside the app-container: ```docker exec -it oci_app.sw6_1 shopware-app/vendor/bin/phpmd shopware-app/custom/plugins/ ansi shopware-app/config/static-analysis/phpmd-ruleset.xml```

Run PHPUnit from outside the app-container: ```docker exec -it oci_app.sw6_1 shopware-app/vendor/bin/phpunit -c shopware-app/phpunit.xml```

## Local environment (Docker)
The local dev environment is based on **docker** respectively **docker-compose**. The images are partially pre-built and can be fetched from a private registry.

You need to create a [personal access token](https://docs.gitlab.com/ee/user/profile/personal_access_tokens.html#creating-a-personal-access-token) in GitLab with **read_registry** permission and execute a [docker login](https://docs.gitlab.com/ee/user/packages/container_registry/#authenticate-with-the-container-registry) to get access.

Docker is slow per design on non linux based machines. That's why there are three possible ways to setup the environment in order to achieve the best experience for every OS.

### tl;dr | initial Setup
Just follow the steps:

1. Create your personal access token with **read_registry** permission and save it.
    1. https://gitlab.nxs360.com/-/profile/personal_access_tokens
2. Execute a docker login via command line
    1. ```docker login -u <your-gitlab-username> registry.gitlab.nxs360.com``` (**as windows user:** Use either powershell or change the command to like: ```winpty docker login -u <your-gitlab-username> registry.gitlab.nxs360.com```)
    2. type in your personal access token
3. Run the **initial** setup with one of the following possibilities
    1. **Based on native volumes (Recommended for Linux users):**
        1. ```.docker/setup-docker-native.sh```
    2. **Based on data-container with SFTP-upload (Recommended for Windows or MacOS users):**
        1. Mac: ```.docker/setup-docker-ssh.sh``` | Windows: ```.docker/setup-docker-ssh.bat```
    3. **Based on docker-sync (Recommended for MacOS users):**
        1. ```tbd```
4. Add ```oci.dev.nxs``` to your hosts-file for 127.0.0.1
5. That's it 🎉 You can reach the shop now at http://oci.dev.nxs/

You can access the app containers shell via: ```docker exec -it oci_app.sw6_1 bash```  
**as windows user:** Use either powershell or change the command to like: ```winpty docker exec -it oci_app.sw6_1 bash```

The apps base path is: **/var/www/oci/shopware-app**

***

#### Create PhpStorm SFTP auto deployment (for environment based on SFTP-upload only)

If you chose the setup based on **native volumes** or **docker-sync** you are ready to go and go to the next section.

1. Open remote hosts
    1. ```Tools -> Deployment -> Browse Remote Host```
2. Add new deployment
    1. Click the three dots-button and click on *add (SFTP)* on the opened dialog
    2. Enter a server name e.g. OCI SW6
    3. Add a new SSH connection by clicking the three dots next to *SSH configuration*
    4. Enter the SFTP-Credentials from the [Services and Connections section](#services-and-connections) and confirm
    5. Enter ```/app/syncdir/shopware``` as *Root path*
    6. Open the Mappings-tab and enter ```/``` as *Deployment path* and confirm everything
3. Activate automatic upload on file changes
    1. ```Tools -> Deployment -> Automatic upload```

Keep in mind that this SFTP workaround for non-unix machines does not work bidirectional. For everything generated on the app-container you need to download it explicit from there, like e.g. the **vendor** directory.

***

### Start and stop the docker environment

1. **If you chose the environment based on native volumes, use the following commands:**
* Start: ```docker-compose -p oci -f .docker/docker-compose.yml -f .docker/docker-compose.volumes.yml start```
* Stop: ```docker-compose -p oci -f .docker/docker-compose.yml -f .docker/docker-compose.volumes.yml stop```
* Remove everything: ```docker-compose -p oci -f .docker/docker-compose.yml -f .docker/docker-compose.volumes.yml down -v --rmi 'all'```

2. **If you chose the environment based on SFTP-upload, use the following commands:**
* Start: ```docker-compose -p oci -f .docker/docker-compose.yml -f .docker/docker-compose-ssh.yml start```
    * **Important:** If you are gonna use the **osTicket** environment as well please use ```up -d``` instead of ```start```, check the [Local environment for osTicket](#local-environment-for-osticket) for further information
* Stop: ```docker-compose -p oci -f .docker/docker-compose.yml -f .docker/docker-compose-ssh.yml stop```
* Remove everything: ```docker-compose -p oci -f .docker/docker-compose.yml -f .docker/docker-compose-ssh.yml down -v --rmi 'all'```

#### Services and Connections

| Service           |Host                       | user          | password  | port  |
| -------------     |-------------------------- |:-------------:| ---------:|------:|
| MySQL             | oci_db.mysql_1      | docker        | docker    | 3306  |
| SFTP              | 127.0.0.1                 | root          | nexus123  | 2222  |
| Redis             | oci_cache.redis_1   |               |           | 6379  |
| MySQL (osTicket)  | oci_db.mysql55_1    | docker        | docker    | 3307  |
