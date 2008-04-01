HOW TO PATCH THE DRUPAL CORE
============================

1. MAKE A BACKUP OF YOUR DRUPAL FILES!!!!

2. NO REALLY... BACK UP YOUR DRUPAL FILES!!!

3. PATCH THE DATABASE.INC !
cd drupal/includes
patch -p0 < ../modules/replication/database.inc.patch

4. UPDATE YOUR SETTINGS.PHP

Change:

$db_url = 'mysql://username:password@databasehost/databasename';

to:

$db_url['default'] = 'mysql://username:password@databasehost/databasename';
$db_url['master'] = 'mysql://username:password@databasehost/databasename';
 
Where the information for:
* 'master' is your MASTER mysql server.
* 'default' is your current host (slave)

This patch is NOT required for the master server.
