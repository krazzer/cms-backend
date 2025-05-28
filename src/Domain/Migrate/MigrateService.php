<?php

namespace App\Domain\Migrate;

use Doctrine\DBAL\Connection;
use Exception;
use PDO;

class MigrateService
{
    /** @var Connection */
    private Connection $conn;

    /**
     * @param Connection $conn
     */
    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function migratePages(): void
    {
        $rows = $this->conn->fetchAllAssociative("
            SELECT 
                id, parent_id, alias, template, display_order,
                type, link, menu_max_level, created_at, updated_at
            FROM cms_page_old
        ");

        // Bouw een map van alle items
        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['id']] = $row;
        }

        $inserted = 0;

        foreach ($map as $id => $row) {
            $parents = [];

            $current = $id;
            while (isset($map[$current]) && $map[$current]['parent_id']) {
                $parent = (int) $map[$current]['parent_id'];
                array_unshift($parents, $parent);
                $current = $parent;
            }

            $jsonParents = empty($parents) ? null : json_encode($parents);

            // Invoegen in nieuwe tabel
            $this->conn->insert('cms_page', [
                'id'             => $id,
                'alias'          => $row['alias'],
                'template'       => $row['template'],
                'display_order'  => $row['display_order'],
                'type'           => $row['type'],
                'link'           => $row['link'],
                'menu_max_level' => $row['menu_max_level'],
                'created_at'     => $row['created_at'],
                'updated_at'     => $row['updated_at'],
                'parents'        => $jsonParents,
            ], [
                'id'             => PDO::PARAM_INT,
                'alias'          => PDO::PARAM_STR,
                'template'       => PDO::PARAM_STR,
                'display_order'  => PDO::PARAM_INT,
                'type'           => PDO::PARAM_STR,
                'link'           => PDO::PARAM_STR,
                'menu_max_level' => PDO::PARAM_INT,
                'created_at'     => PDO::PARAM_STR,
                'updated_at'     => PDO::PARAM_STR,
                'parents'        => $jsonParents === null ? PDO::PARAM_NULL : PDO::PARAM_STR,
            ]);

            $inserted++;
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function migratePageLanguage(): void
    {
        $rows = $this->conn->fetchAllAssociative("SELECT * FROM cms_page_language");

        $updates = [];

        foreach ($rows as $row) {
            $pageId   = $row['page_id'];
            $langCode = $row['language_code'];

            if ( ! array_key_exists($pageId, $updates)) {
                $updates[$pageId] = [
                    'active' => [],
                    'name'   => [],
                    'slug'   => [],
                    'seo'    => [],
                ];
            }

            $seo = [
                'title'       => $row['seo_title'],
                'keywords'    => $row['seo_keywords'],
                'description' => $row['seo_description'],
            ];

            $updates[$pageId]['active'][$langCode] = $row['active'];
            $updates[$pageId]['name'][$langCode]   = $row['name'];
            $updates[$pageId]['slug'][$langCode]   = $row['slug'];
            $updates[$pageId]['seo'][$langCode]    = $seo;
        }

        foreach ($updates as $id => $update) {
            $update['active'] = json_encode($update['active']);
            $update['name']   = json_encode($update['name']);
            $update['slug']   = json_encode($update['slug']);
            $update['seo']    = json_encode($update['seo']);

            $this->conn->update('cms_page', $update, ['id' => $id]);
        }
    }
}