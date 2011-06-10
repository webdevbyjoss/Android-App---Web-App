# assign environment variable
APP_DIR=`readlink -f ./../../`
REMOTE_APP_DIR=nash:/home/nashmast/www/_proj/povidom-vladu.org.ua

# deploy public directory
rsync -avz $APP_DIR/public_html/js/* $REMOTE_APP_DIR/public_html/js/
rsync -avz $APP_DIR/public_html/css/* $REMOTE_APP_DIR/public_html/css/
rsync -avz $APP_DIR/public_html/index.php $REMOTE_APP_DIR/public_html/index.php
rsync -uv $APP_DIR/public_html/.htaccess $REMOTE_APP_DIR/public_html/.htaccess

