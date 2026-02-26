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

#### Update admin JS/CSS to latest:
`php bin/console kikcms:cms:update-admin`

#### List all kikcms commands:
`php bin/console kikcms list`