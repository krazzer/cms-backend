<?php

namespace KikCMS\Domain\Form\Storage;

use KikCMS\Domain\App\Exception\StorageHttpException;
use KikCMS\Domain\Form\Field\FieldService;
use KikCMS\Domain\Form\Form;
use Psr\Cache\CacheItemPoolInterface;

readonly class StorageService
{
    public function __construct(
        private FieldService $fieldService,
        private CacheItemPoolInterface $keyValueStore,
    ) {}

    public function store(Form $form, array $data): void
    {
        $fields = $this->fieldService->getObjectMapByForm($form);

        foreach ($fields as $key => $field) {
            if( ! $field->isStore()){
                continue;
            }

            if ( ! ($value = $data[$key] ?? null)) {
                continue;
            }

            $cacheKey = $field->getField();

            $item = $this->keyValueStore->getItem($cacheKey)->set($value);

            if( ! $this->keyValueStore->save($item)){
                throw new StorageHttpException("Could not save item $cacheKey");
            }
        }
    }
}