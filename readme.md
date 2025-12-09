### Open:
https://localhost:9200/cms/

### Set up site:
`SITE_ALIAS=[ALIAS] SITE_PORT=[PORT] docker compose -f docker/docker-compose-site.yml -p [KEY] up -d`

### Enable XDebug:
`docker exec -ti cms-php-1 sh -c "echo "zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20230831/xdebug.so" >> /usr/local/etc/php/php.ini && apachectl restart"`

### Disable XDebug:
`docker exec -ti cms-php-1 sh -c "sed -i \"/\b\(xdebug.so\)\b/d\" /usr/local/etc/php/php.ini && apachectl restart"`

### Enter container:
`docker exec -it cms-php-1 /bin/bash`