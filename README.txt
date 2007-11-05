HOW TO PATCH THE DRUPAL CORE
============================

1. MAKE A BACKUP OF YOUR DRUPAL FILES!!!!

2. NO REALLY... BACK UP YOUR DRUPAL FILES!!!

3. PATCH THE BOOTSTRAP.INC FILE
cd drupal/includes
patch -p0 < ../modules/replication/bootstrap.inc.patch

4. PATCH THE CORRECT DATABASE FILE (mysql or mysqli)

patch -p0 < ../modules/replication/database.mysql.inc.patch
patch -p0 < ../modules/replication/database.mysqli.inc.patch

DONE.
