## CMS with project files (recommended for most non-trivial projects)

Use this guide to set up the CMS as part of a project. This is the recommended setup for most projects.

### Useful commands

#### Load/unload dev env
- `php bin/console kikcms:app:up`
- `php bin/console kikcms:app:down`

#### Do a composer update while having a linked CMS

1. Run `rm -rf vendor/kiksaus && composer update && rm -rf vendor/kiksaus && ln -s ../../KikCMS vendor/kiksaus`

## CMS standalone setup

Use this guide to set up the CMS as standalone. This can be useful for development on the CMS itself without needing to
set up a full project.

### Set up CMS

1. Clone this repo
2. Run `composer install`
3. Run `php bin/console kikcms:cms:up`

### Useful commands

#### Load/unload dev env
- `php bin/console kikcms:cms:up`
- `php bin/console kikcms:cms:down`

#### Update DB schema
- Check: `php bin/console doctrine:schema:update --dump-sql`
- Update: `php bin/console doctrine:schema:update --force`

#### Enter container:
`php bin/console kikcms:cms:attach`

#### Update admin JS/CSS to the latest:
`php bin/console kikcms:cms:update-admin`

#### List all kikcms commands:
`php bin/console kikcms list`