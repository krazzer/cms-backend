<?php

namespace App\Entity\Page;

class PageTreeService
{
    /**
     * Sorts a flat list of hierarchical items into a depth-first, display-order-aware structure.
     *
     * This method returns a flattened array of items in their correct hierarchical and display order.
     * It assumes each item contains at least the following keys:
     *
     * - Page::FIELD_ID (string|int): The unique identifier of the item.
     * - Page::FIELD_PARENTS (array): An ordered list of ancestor IDs (e.g., [1, 4, 6]).
     * - Page::FIELD_DISPLAY_ORDER (int): The local sort order among siblings.
     *
     * Items are grouped by their immediate parent (last entry in the 'parents' array), sorted by display_order
     * within each group, and then traversed recursively starting from the root level (null parent).
     *
     * @param array $data A flat array of items with hierarchical metadata.
     * @return array The same items, sorted in correct hierarchical and display order.
     */
    public function sort(array $data): array
    {
        $tree = $sorted = [];

        foreach ($data as $item) {
            $parentId = empty($item[Page::FIELD_PARENTS]) ? null : end($item[Page::FIELD_PARENTS]);

            $tree[$parentId][] = $item;
        }

        foreach ($tree as &$group) {
            usort($group, fn($a, $b) => $a[Page::FIELD_DISPLAY_ORDER] <=> $b[Page::FIELD_DISPLAY_ORDER]);
        }

        $walk = function ($parentId) use (&$walk, &$tree, &$sorted) {
            foreach ($tree[$parentId] ?? [] as $item) {
                $sorted[] = $item;
                $walk($item[Page::FIELD_ID]);
            }
        };

        $walk(null);

        return $sorted;
    }
}