# Contacto - A web app to manage contacts

## Screenshots

## Features
- RBAC
- User management
- Manage Contacts and Groups
- Send SMSes and Emails to Contacts and Groups
- Export of data to PDF
- Bulk creation of contacts by uploading CSV or Excel files
- Admin interface with a simple dashboard

## Languages
- PHP (OOP / MVC)
- MySQL database
- Composer
- HTML / CSS / JS

## Setup
- Clone repository to your computer
- Install MySQL server with PhpMyAdmin (optional)
- Create a database and import the SQL file located at config/contacts.sql
- Update or overwrite the DB configs located at config/base/db.config.php
- Open up the public folder of the project in a terminal
- Start the PHP server (php -S 127.0.0.1:4000)
- Open your browser to the URL and port
- Login using the accounts listed at public/index.php
- Depending on the account you login with, you may find different interface and functionality. The superadmin account is root. The admin account can manage contacts and groups only. The auditor account can see everything but not edit or delete. The user account has no rights.