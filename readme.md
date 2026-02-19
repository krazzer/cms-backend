# CMS standalone setup

Use this guide to set up the CMS as standalone. This can be useful for development on the CMS itself without needing to
set up a full project.

## Set up CMS

1. Clone this repo
2. Run `composer install`
3. Run `php bin/console kikcms:cms:up`

# Useful commands

### Set up site:
`ALIAS=[ALIAS] PORT=[PORT] docker compose -f vendor/kiksaus/cms-backend/docker/docker-compose-site.yml -p [KEY] up -d`

### Enable XDebug:
`docker exec -ti cms-php-1 sh -c "echo "zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20240924/xdebug.so" >> /usr/local/etc/php/php.ini && apachectl restart"`

### Disable XDebug:
`docker exec -ti cms-php-1 sh -c "sed -i \"/\b\(xdebug.so\)\b/d\" /usr/local/etc/php/php.ini && apachectl restart"`

### Enter container:
`docker exec -it <CONTAINER_NAME> /bin/bash`

### Edit CMS code within a project
Create symlink (replace `../../KikCMS` with where the CMS sits relative to the vendor dir) `rm -rf vendor/kiksaus && ln -s ../../KikCMS vendor/kiksaus`

### How to do a composer update with updated CMS code without a commit?
- Update composer.json with:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../KikCMS2/cms-backend",
            "options": {
                "symlink": true
            }
        }
    ]
}
```
- Remove existing symlink
- Run `composer update`