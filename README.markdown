NEW PROJECTS
=======

1. create a new package
     $ pfarm plant <package_name>

2. update '<package_name>/<package_name>.spec' file

3. sudo pear channel-discover <channel-location> (if you haven't done that yet)

4. build package
     $ pfarm collect

5. install
     $ pear install <package_name>-<version>.tgz


EXISTING PROJECTS
=======

1. create a '<package_name>.spec' file in your project's root directory

2. configure it (see the pearfarm.spec in this project for an example)

3. build package
     $ pfarm collect

4. install
     $ pear install <package_name>-<version>.tgz


Enjoy!
