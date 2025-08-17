#Landbase System Installation Guide

####Note: do not replace image, just upload new image and edit image source on code

* inside page folder `cd page`
* run  `composer update`
* Create page/.env file. copy from page/.env.example
	* `cp .env.example .env`
	* Change Debug and APP_DEBUG to false
	* change APP_KEY
* create database, import page/database/empty-stepup.sql
* run `php artisan migrate`
	* seed database on testing site
* Create Resume
	* inside folder app/controllers/public/ `cd ../app/controllers/public/`
		* create a resume.php based on resume.example.php
		* `cp resume.example.php resume.php`
	* inside folder app/views/public/applicants `cd ../../views/public/applicants`
		* create pdf.php based on pdf.example.php
		* `cp pdf.example.php pdf.php`
* go to root `cd ../../../../`
* create deployer reports 
	* run : `cp app/views/admin/reports/applicants/deployed.example.php app/views/admin/reports/applicants/deployed.php`
* make sure files/applicant folder exist on root directory `sudo chmod 777 -R files`
* *LOGOUT SSH LOG FTP*
* configure
	* edit database, table settings
		* client = client
		* client_short = short desciprtion for applicant number
		* client_full = client full name
		* icon_link - link to logo
		* style = custom css for this client only, usualy used when hiding uneccesary part
* Add favicon on folders
	* assets/images/admin/favicon.png
	* assets/images/employer/favicon/favicon.png
* create Custom Fields on Review,
	* go to database, table CustomFields
	* Add name, location and description. for list of locations:
        * 'Preferred Designation'
        * 'Basic Information'
        * 'Incase of Emergency'
        * 'Passport Information'
        * 'Educational Background'
        * 'Other'
        * //Requirements
        * 'Examination Taken'
        * 'Medical Examination'
        * 'Aunthenticated Documents'
        * 'Other Requirements'
        * customCategory1
        * customCategory2
        * customCategory3
* Add to Database, User table, add user id: 9999, fullname: Applicant Uploaded# agency
