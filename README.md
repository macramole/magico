# Mâgico

## Requirements

1. Apache + MySQL + PHP
2. Apache's mod_rewrite
3. GD imaging (php5-gd)
4. PHP Cli (php5-cli) for using Mâgico's CLI tools

## Installation

1. Clone or download Mâgico and extract it on your www/ folder
2. Download CodeIgniter 2.2.6 and extract it on your www/ folder
3. Make sure magico/ and CodeIgniter-2.2.6 has correct permissions (chmod o+r+x)
4. From a terminal cd to your www/ folder and execute ./magico/createMagicoProject.sh [newProjectName] . This will create a new folder [newProjectName] with all the necessary files. It will also create the database for you (make sure user and pass matches your configuration)
5. You'll probably have to set base_url in application/config/config.php
