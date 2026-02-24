<?php

namespace KikCMS\Domain\Form\Source;

use KikCMS\Domain\App\Exception\StorageHttpException;
use KikCMS\Domain\Form\Field\FieldService;
use KikCMS\Domain\Form\Form;
use Psr\Cache\CacheItemPoolInterface;

readonly class SourceService
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

    public function getData(Form $form): array
    {
        $data = [];

        $fields = $this->fieldService->getObjectMapByForm($form);

        foreach ($fields as $key => $field) {
            if( ! $field->isStore()){
                continue;
            }

            $data[$key] = $this->keyValueStore->getItem($field->getField())->get();
        }

        return $data;
    }
}