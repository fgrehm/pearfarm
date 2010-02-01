INSTALATION
=======
 $ git clone git://github.com/fgrehm/pearfarm.git

 $ cd pearfarm

 $ pear channel-discover pearfarm.pearfarm.org

 $ php pearfarm build

 $ pear install pearfarm-*.tgz

OR

 $ pear install pearfarm.pearfarm.org/pearfarm

DEPENDENCIES
=======
1. PHP >= 5

2. cURL support enabled  
    **Ubuntu**: _sudo apt-get install php5-curl_  
    **Mac ports**: _sudo port install php5-curl_

EXISTING PROJECTS
=======

1. cd to/project/root

2. pearfarm init

3. update generated pearfarm.spec file

4. run 'pearfarm build' to build package .tgz


Enjoy!