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

5. Update database.inc for your specific requirements (although defaults should be fine)


The array of all tables to be WRITTEN to locally.  (All other WRITES happen on the master)

$tables_local = array('history','cache','cache_filter','cache_menu','cache_page','cache_views','system','variable','watchdog','dbfm_data','dbfm_cronlist','sessions','ldapauth');


The array of all tables to be READ from the master.  (All other READS happen locally)

$tables_master = array('sequences');


********

This patch is NOT required for the master server.
