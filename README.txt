ABOUT
=============

Moodle is an open-source e-Learning Content Management System that helps build online courses.

This project involves development of some custom plugins, like:

1. Announcement Plugin (in mod/announcementplugin folder)

It helps in posting of announcements by site administrator or the 
course faculty for the recepients to see.

2. News Plugin (in mod/newsplugin folder)

It helps in posting of news by site administrator or the 
course faculty for the recepients to see.

3. Subject Plugin (in mod/subjectplugin folder)

Allows creation of different subjects, inside Moodle courses for 
maintaining proper hierarchy of topics.


QUICK INSTALL
=============

Here is a basic outline of the installation process,
which normally takes me only a few minutes:

1) Move the Moodle files into your web directory.

2) Create a single database for Moodle to store all
   it's tables in (or choose an existing database).

3) Visit your Moodle site with a browser, you should
   be taken to the install.php script, which will lead
   you through creating a config.php file and then
   setting up Moodle, creating an admin account etc.

4) Set up a cron task to call the file admin/cron.php
   every five minutes or so.


For more information, see the INSTALL DOCUMENTATION:

   http://docs.moodle.org/en/Installing_Moodle