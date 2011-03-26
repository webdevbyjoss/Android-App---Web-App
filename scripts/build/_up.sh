# assign environment variable
APP_DIR=`readlink -f ./../../`
REMOTE_APP_DIR=nash:/home/nashmast/www/_proj/povidom-vladu.org.ua

# cleanup session and cache data before deployment
./local-cleanup.sh

# application deployment
# NOTE: we skipping configuration directory
rsync -avz $APP_DIR/application/controllers/* $REMOTE_APP_DIR/application/controllers/
rsync -avz $APP_DIR/application/layouts/* $REMOTE_APP_DIR/application/layouts/
rsync -avz $APP_DIR/application/models/* $REMOTE_APP_DIR/application/models/
rsync -avz $APP_DIR/application/modules/* $REMOTE_APP_DIR/application/modules/
rsync -avz $APP_DIR/application/services/* $REMOTE_APP_DIR/application/services/
rsync -avz $APP_DIR/application/views/* $REMOTE_APP_DIR/application/views/
rsync -avz $APP_DIR/application/Bootstrap.php $REMOTE_APP_DIR/application/Bootstrap.php

# internal application resources deployment
rsync -avz $APP_DIR/data/* $REMOTE_APP_DIR/data/
./_up_public.sh

# code libraries
rsync -avz $APP_DIR/library/Custom/* $REMOTE_APP_DIR/library/Custom/
rsync -avz $APP_DIR/library/Joss/* $REMOTE_APP_DIR/library/Joss/
rsync -avz /var/www/zend/Zend/* $REMOTE_APP_DIR/library/Zend/
rsync -avz /var/www/zend/ZendX/* $REMOTE_APP_DIR/library/ZendX/
rsync -avz $APP_DIR/library/Report/* $REMOTE_APP_DIR/library/Report/

# scripts 
rsync -uv $APP_DIR/scripts/zf-cli.php $REMOTE_APP_DIR/scripts/zf-cli.php

# force cache rebuild on production after update
./remove-cache-on-production.sh
