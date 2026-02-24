<?php

namespace KikCMS\Domain\Form\Source;

use KikCMS\Domain\App\Exception\StorageHttpException;
use KikCMS\Domain\App\KeyValue\KeyValueService;
use KikCMS\Domain\Form\Field\FieldService;
use KikCMS\Domain\Form\Form;

readonly class SourceService
{
    public function __construct(
        private FieldService $fieldService,
        private KeyValueService $keyValueService,
    ) {}

    public function store(Form $form, array $data): void
    {
        $fields = $this->fieldService->getObjectMapByForm($form);

        foreach ($fields as $key => $field) {
            if ( ! $field->isStore()) {
                continue;
            }

            if ( ! ($value = $data[$key] ?? null)) {
                continue;
            }

            $cacheKey = $field->getField();

            if ( ! $this->keyValueService->set($cacheKey, $value)) {
                throw new StorageHttpException("Could not save item $cacheKey");
            }
        }
    }

    public function getData(Form $form): array
    {
        $data = [];

        $fields = $this->fieldService->getObjectMapByForm($form);

        foreach ($fields as $key => $field) {
            if ( ! $field->isStore()) {
                continue;
            }

            $data[$key] = $this->keyValueService->get($field->getField());
        }

        return $data;
    }
}