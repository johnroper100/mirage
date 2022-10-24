# Mirage

A new site platform designed specifically for single client websites with a set theme. Develop in plain HTML and CSS and then simply add a few lines of code to convert it to a fully functional website. Fully customize the internal editing display for each type of content so that you only see the inputs that you need.

## Download and Install

Mirage uses a number of git submodules. This means that if you would like to download it, you must either use a download from the `Releases` page on GitHub, or download the repository using Git.

If you use Git, make sure you run the command: `git submodule update --init --recursive`

## Access the Dashboard

Once you have downloaded and installed Mirage, go to `https://{your website url}/admin` to access the dashboard. You will be prompted for an email and password. During the setup process, the initial administrator account is created. Additional user accounts can be added from the dashboard.

## Theme Development Documentation

All documentation needed to develop themes is located on [our Wiki](https://github.com/johnroper100/mirage/wiki).

## Backing Up Your Site

Backups are easy with Mirage, simply save copies of the `database` directory and the `uploads` directory. As long as the theme stays the same, the content should be able to be transfered to a new server if needed simply by coping these folders over. This is also helpful for moving a development website into production.

## A Note About Security

Mirage uses a file-based datatbase to store its data. Make sure that the `database` folder is blocked from being read by people/scripts other than Mirage. The `.htaccess` file that is generated automatically by Mirage should do an adequite job of this, but make sure to double check for your install.

Passwords are stored using the PHP `password_hash` function which should be strong enough. Again, making sure that the `database` folder is private is an additional method of security.
