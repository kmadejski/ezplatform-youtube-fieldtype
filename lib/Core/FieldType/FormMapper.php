<?php

namespace EzSystems\YouTubeFieldType\Core\FieldType;

use EzSystems\RepositoryForms\Data\FieldDefinitionData;
use EzSystems\RepositoryForms\FieldType\FieldDefinitionFormMapperInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class FormMapper implements FieldDefinitionFormMapperInterface
{
    /**
     * Enriches FieldDefinition with additional settings.
     *
     * @param FormInterface       $fieldDefinitionForm form for current FieldDefinition
     * @param FieldDefinitionData $data                underlying data for current FieldDefinition form
     */
    public function mapFieldDefinitionForm(FormInterface $fieldDefinitionForm, FieldDefinitionData $data)
    {
        $fieldDefinitionForm->add(
            'defaultWidth', IntegerType::class, array(
                'required' => false,
                'label' => 'ezyt.default_embed_width',
                'translation_domain' => 'forms',
                'property_path' => 'fieldSettings[defaultWidth]',
            )
        )->add(
            'defaultHeight', IntegerType::class, array(
                'required' => false,
                'label' => 'ezyt.default_embed_height',
                'translation_domain' => 'forms',
                'property_path' => 'fieldSettings[defaultHeight]',
            )
        );
    }
}
