<?php

namespace KikCMS\Domain\FrontendForm;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;

readonly class FrontendFormFormatService
{
    public function format(FormInterface $form): array
    {
        $formattedData = [];

        foreach ($form->all() as $name => $child) {
            // Skip submit buttons and other non-relevant fields
            if ($child->getConfig()->getType()->getInnerType() instanceof SubmitType) {
                continue;
            }

            $config = $child->getConfig();
            $data   = $child->getData();

            // 1. Determine the label (priority: actual label -> placeholder -> field name)
            $label = $config->getOption('label');
            if ( ! $label) {
                $attr  = $config->getOption('attr');
                $label = $attr['placeholder'] ?? ucfirst($name);
            }

            // 2. Format the value based on the field type (such as ChoiceType)
            $formattedValue = $data;

            if ($data !== null && $config->hasOption('choices')) {
                $choices = $config->getOption('choices');
                // Symfony choices can be 'label => value' pairs
                // We want to retrieve the key (the label) based on the value(s)
                $flippedChoices = array_flip($choices);

                if (is_array($data)) {
                    // Multiple choice (e.g., expanded/multiple checkbox)
                    $labels = [];
                    foreach ($data as $val) {
                        if (isset($flippedChoices[$val])) {
                            $labels[] = $flippedChoices[$val];
                        }
                    }
                    $formattedValue = implode(', ', $labels);
                } else {
                    // Single choice (e.g., ChoiceType dropdown)
                    if (isset($flippedChoices[$data])) {
                        $formattedValue = $flippedChoices[$data];
                    }
                }
            }

            // If it's an array (and not already converted by the choice logic), convert it to a string
            if (is_array($formattedValue)) {
                $formattedValue = implode(', ', $formattedValue);
            }

            $formattedData[$label] = $formattedValue;
        }

        return $formattedData;
    }
}