<?php

namespace EzSystems\YouTubeFieldType\Core\FieldType;

use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use DOMDocument;

class LegacyConverter implements Converter
{
    /**
     * @return LegacyConverter
     */
    public static function create()
    {
        return new self();
    }

    /**
     * Converts data from $value to $storageFieldValue.
     *
     * Note: You should not throw on validation here, as it is implicitly used by ContentService->createContentDraft().
     *       Rather allow invalid value or omit it to let validation layer in FieldType handle issues when user tried
     *       to publish the given draft.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue                $value
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $storageFieldValue
     */
    public function toStorageValue(FieldValue $value, StorageFieldValue $storageFieldValue)
    {
        $storageFieldValue->dataText = $this->generateXmlString($value->data);
        $storageFieldValue->sortKeyString = $value->sortKey;
    }

    /**
     * Converts data from $value to $fieldValue.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue                $fieldValue
     */
    public function toFieldValue(StorageFieldValue $value, FieldValue $fieldValue)
    {
        $fieldValue->sortKey = $value->sortKeyString;
        $fieldValue->data = $this->restoreValueFromXmlString($value->dataText);
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition           $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition(FieldDefinition $fieldDef, StorageFieldDefinition $storageDef)
    {
        $storageDef->dataInt1 = isset($fieldDef->fieldTypeConstraints->fieldSettings['defaultWidth']) ?
            (int) $fieldDef->fieldTypeConstraints->fieldSettings['defaultWidth'] :
            Type::WIDTH_DEFAULT_VALUE;
        $storageDef->dataInt2 = isset($fieldDef->fieldTypeConstraints->fieldSettings['defaultHeight']) ?
            (int) $fieldDef->fieldTypeConstraints->fieldSettings['defaultHeight'] :
            Type::HEIGHT_DEFAULT_VALUE;
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition           $fieldDef
     */
    public function toFieldDefinition(StorageFieldDefinition $storageDef, FieldDefinition $fieldDef)
    {
        $fieldDef->fieldTypeConstraints->fieldSettings = new FieldSettings(
            array(
                'defaultWidth' => (int) $storageDef->dataInt1,
                'defaultHeight' => (int) $storageDef->dataInt2,
            ),
            FieldSettings::ARRAY_AS_PROPS
        );
    }

    /**
     * Returns the name of the index column in the attribute table.
     *
     * Returns the name of the index column the datatype uses, which is either
     * "sort_key_int" or "sort_key_string". This column is then used for
     * filtering and sorting for this type.
     *
     * If the indexing is not supported, this method must return false.
     *
     * @return string|false
     */
    public function getIndexColumn()
    {
        return 'sort_key_string';
    }

    /**
     * Returns XML representation of Value object.
     *
     * @param array|null $fieldData
     *
     * @return string
     */
    private function generateXmlString(array $fieldData = null)
    {
        if ($fieldData === null) {
            $fieldData = $this->createEmptyRawValue();
        }

        $doc = new DOMDocument('1.0', 'UTF-8');

        $root = $doc->createElement('ezyt');
        foreach ($fieldData as $fieldName => $fieldValue) {
            $root->setAttribute($fieldName, $fieldValue);
        }
        $doc->appendChild($root);

        return $doc->saveXML();
    }

    /**
     * Restores raw value representation from XML.
     *
     * @param $xmlString
     *
     * @return array
     */
    private function restoreValueFromXmlString($xmlString)
    {
        $dom = new DOMDocument('1.0', 'UTF-8');

        if (!empty($xmlString) && $dom->loadXML($xmlString) === true) {
            $ezyt = $dom->getElementsByTagName('ezyt')->item(0);
            $field = array(
                'videoId' => $ezyt->getAttribute('videoId'),
                'title' => $ezyt->getAttribute('title'),
                'width' => $ezyt->getAttribute('width'),
                'height' => $ezyt->getAttribute('height'),
                'autoplay' => $ezyt->getAttribute('autoplay'),
            );

            return $field;
        }

        return $this->createEmptyRawValue();
    }

    /**
     * Returns empty array with keys expected by
     * \KMadejski\YTFieldTypeBundle\Core\FieldType\Value.
     *
     * @return array
     */
    private function createEmptyRawValue()
    {
        return array(
            'videoId' => '',
            'title' => '',
            'width' => 0,
            'height' => 0,
            'autoplay' => false,
        );
    }
}
