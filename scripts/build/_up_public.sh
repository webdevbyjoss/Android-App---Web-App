# assign environment variable
APP_DIR=`readlink -f ./../../`
REMOTE_APP_DIR=nash:/home/nashmast/www/_proj/povidom-vladu.org.ua

# deploy public directory
rsync -avz $APP_DIR/public_html/* $REMOTE_APP_DIR/public_html/
rsync -uv $APP_DIR/public_html/.htaccess $REMOTE_APP_DIR/public_html/.htaccess

