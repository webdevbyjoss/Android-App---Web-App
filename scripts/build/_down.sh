# assign environment variable
APP_DIR=`readlink -f ./../../`
REMOTE_APP_DIR=nash:/home/nashmast/www/_proj/povidom-vladu.org.ua

# application resources sync
rsync -avz $REMOTE_APP_DIR/public_html/images/* $APP_DIR/public_html/images/
