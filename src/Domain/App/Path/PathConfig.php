<?php

namespace KikCMS\Domain\App\Path;

class PathConfig
{
    const string DIR_VENDOR_KIKSAUS = 'vendor/kiksaus';
    const string DIR_SRC            = 'src';

    const string DIR_CERTS   = 'var/certs';
    const string DIR_STORAGE = 'var/storage';

    const string DIR_CONFIG          = 'config';
    const string DIR_CONFIG_FORMS    = 'config/forms';
    const string DIR_CONFIG_THEME    = 'config/theme';
    const string DIR_CONFIG_PACKAGES = 'config/packages';

    const string DIR_PUBLIC = 'public_html';

    const string SUBDIR_ADMIN                = 'cms';
    const string SUBDIR_MEDIA                = 'media';
    const string SUBDIR_MEDIA_FILES          = 'media/files';
    const string SUBDIR_MEDIA_THUMBS         = 'media/thumbs';
    const string SUBDIR_MEDIA_THUMBS_DEFAULT = 'media/thumbs/default';

    const string DIR_ADMIN                = self::DIR_PUBLIC . '/' . self::SUBDIR_ADMIN;
    const string DIR_MEDIA                = self::DIR_PUBLIC . '/' . self::SUBDIR_MEDIA;
    const string DIR_MEDIA_FILES          = self::DIR_PUBLIC . '/' . self::SUBDIR_MEDIA_FILES;
    const string DIR_MEDIA_THUMBS         = self::DIR_PUBLIC . '/' . self::SUBDIR_MEDIA_THUMBS;
    const string DIR_MEDIA_THUMBS_DEFAULT = self::DIR_PUBLIC . '/' . self::SUBDIR_MEDIA_THUMBS_DEFAULT;

    const string FILE_DOCKER_COMPOSE_SERVICES = 'resources/docker/docker-compose-services.yml';
    const string FILE_DOCKER_COMPOSE_SITE     = 'resources/docker/docker-compose-site.yml';
    const string FILE_DOCKER_COMPOSE          = 'resources/docker/docker-compose.yml';

    const string FILE_CERT     = 'var/certs/cert.crt';
    const string FILE_CERT_KEY = 'var/certs/cert.key';

    const string FILE_SNAKE_CERT     = 'resources/certs/snakeoil.crt';
    const string FILE_SNAKE_CERT_KEY = 'resources/certs/snakeoil.key';
}