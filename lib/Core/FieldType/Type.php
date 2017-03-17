<?php

namespace EzSystems\YouTubeFieldType\Core\FieldType;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\FieldType\FieldType as FieldType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;

class Type extends FieldType
{
    /**
     * Default embed width
     * This value is used if defaultWidth fieldSetting has not been set.
     */
    const WIDTH_DEFAULT_VALUE = 400;

    /**
     * Default embed height
     * This value is used if defaultWidth fieldSetting has not been set.
     */
    const HEIGHT_DEFAULT_VALUE = 300;

    /**
     * @var array
     */
    protected $validatorConfigurationSchema = array(
        'YTVideoIdValidator' => array(
            'minVideoIdLength' => array(
                'type' => 'int',
                'default' => 0,
            ),
            'maxVideoIdLength' => array(
                'type' => 'int',
                'default' => 255,
            ),
        ),
    );

    /**
     * @var array
     */
    protected $settingsSchema = array(
        'defaultWidth' => array(
            'type' => 'int',
            'default' => self::WIDTH_DEFAULT_VALUE,
        ),
        'defaultHeight' => array(
            'type' => 'int',
            'default' => self::HEIGHT_DEFAULT_VALUE,
        ),
    );

    /**
     * Validates the fieldSettings of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct.
     *
     * This method expects that given $fieldSettings are complete, for this purpose method
     * {@link self::applyDefaultSettings()} is provided.
     *
     * @param mixed $fieldSettings
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateFieldSettings($fieldSettings)
    {
        $validationErrors = array();

        if ( !is_array($fieldSettings)) {
            $validationErrors[] = new ValidationError('Field settings must be in form of an array');

            return $validationErrors;
        }

        foreach ($fieldSettings as $name => $value) {
            if ( !isset($this->settingsSchema[$name])) {
                $validationErrors[] = new ValidationError(
                    'Setting "%setting%" is unknown',
                    null,
                    array(
                        '%setting%' => $name,
                    ),
                    "[$name]"
                );
                continue;
            }

            switch ($name) {
                case "defaultWidth":
                    if ( !is_int($value)) {
                        $validationErrors[] = new ValidationError(
                            "Setting '%setting%' value must be of integer type",
                            null,
                            array(
                                '%setting%' => $name,
                            ),
                            "[$name]"
                        );
                    }
                    if ($value <= 0) {
                        $validationErrors[] = new ValidationError(
                            "Setting %setting% value must be greater than 0",
                            null,
                            array(
                                '%setting%' => $name
                            ),
                            "[$name]"
                        );
                    }
                    break;
                case "defaultHeight":
                    if ( !is_int($value)) {
                        $validationErrors[] = new ValidationError(
                            "Setting '%setting%' value must be of integer type",
                            null,
                            array(
                                '%setting%' => $name,
                            ),
                            "[$name]"
                        );
                    }
                    if ($value <= 0) {
                        $validationErrors[] = new ValidationError(
                            "Setting %setting% value must be greater than 0",
                            null,
                            array(
                                '%setting%' => $name,
                            ),
                            "[$name]"
                        );
                    }
                    break;
            }
        }

        return $validationErrors;
    }

    /**
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * If given $inputValue could not be converted or is already an instance of dedicate value object,
     * the method should simply return it.
     *
     * This is an operation method for {@see acceptValue()}.
     *
     * Example implementation:
     * <code>
     *  protected function createValueFromInput( $inputValue )
     *  {
     *      if ( is_array( $inputValue ) )
     *      {
     *          $inputValue = \eZ\Publish\Core\FieldType\CookieJar\Value( $inputValue );
     *      }
     *
     *      return $inputValue;
     *  }
     * </code>
     *
     * @param mixed $inputValue
     *
     * @return mixed the potentially converted input value
     */
    protected function createValueFromInput($inputValue)
    {
        if (is_array($inputValue)) {
            $inputValue = new Value($inputValue);
        }

        return $inputValue;
    }

    /**
     * Returns the field type identifier for this field type.
     *
     * This identifier should be globally unique and the implementer of a
     * FieldType must take care for the uniqueness. It is therefore recommended
     * to prefix the field-type identifier by a unique string that identifies
     * the implementer. A good identifier could for example take your companies main
     * domain name as a prefix in reverse order.
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'ezyt';
    }

    /**
     * Returns a human readable string representation from the given $value.
     *
     * @param \KMadejski\YTFieldType\Core\FieldType\Value $value
     *
     * @return string
     */
    public function getName(SPIValue $value)
    {
        return (string)$value->title;
    }

    /**
     * Returns the empty value for this field type.
     *
     * This value will be used, if no value was provided for a field of this
     * type and no default value was specified in the field definition. It is
     * also used to determine that a user intentionally (or unintentionally) did not
     * set a non-empty value.
     *
     * @return \KMadejski\YTFieldType\Core\FieldType\Value
     */
    public function getEmptyValue()
    {
        return new Value();
    }

    /**
     * Converts an $hash to the Value defined by the field type.
     *
     * This is the reverse operation to {@link toHash()}. At least the hash
     * format generated by {@link toHash()} must be converted in reverse.
     * Additional formats might be supported in the rare case that this is
     * necessary. See the class description for more details on a hash format.
     *
     * @param mixed $hash
     *
     * @return \KMadejski\YTFieldType\Core\FieldType\Value
     */
    public function fromHash($hash)
    {
        if ($hash === null) {
            return $this->getEmptyValue();
        }

        return new Value($hash);
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * Note that this does not include validation after the rules
     * from validators, but only plausibility checks for the general data
     * format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the value does not match the expected structure
     *
     * @param \KMadejski\YTFieldType\Core\FieldType\Value $value
     */
    protected function checkValueStructure(BaseValue $value)
    {
        if ( !is_string($value->videoId)) {
            throw new InvalidArgumentType(
                '$value->videoId',
                'string',
                $value->videoId
            );
        }
        if ( !is_string($value->title)) {
            throw new InvalidArgumentType(
                '$value->title',
                'string',
                $value->title
            );
        }
    }

    /**
     * Converts the given $value into a plain hash format.
     *
     * Converts the given $value into a plain hash format, which can be used to
     * transfer the value through plain text formats, e.g. XML, which do not
     * support complex structures like objects. See the class level doc block
     * for additional information. See the class description for more details on a hash format.
     *
     * @param \KMadejski\YTFieldType\Core\FieldType\Value $value
     *
     * @return mixed
     */
    public function toHash(SPIValue $value)
    {
        if ($this->isEmptyValue($value)) {
            return null;
        }

        return array(
            'videoId' => $value->videoId,
            'title' => $value->title,
            'autoplay' => $value->autoplay,
            'width' => $value->width,
            'height' => $value->height,
        );
    }

    /**
     * @param \KMadejski\YTFieldType\Core\FieldType\Value $value
     *
     * @return FieldValue
     */
    public function toPersistenceValue(SPIValue $value)
    {
        if ($value === null) {
            return new FieldValue(
                array(
                    'data' => array(),
                    'externalData' => null,
                    'sortKey' => null,
                )
            );
        }

        return new FieldValue(
            array(
                'data' => $this->toHash($value),
                'externalData' => null,
                'sortKey' => $this->getSortInfo($value),
            )
        );
    }

    /**
     * @param FieldValue $fieldValue
     *
     * @return \KMadejski\YTFieldType\Core\FieldType\Value
     */
    public function fromPersistenceValue(FieldValue $fieldValue)
    {
        if ($fieldValue->data === null) {
            return $this->getEmptyValue();
        }

        return new Value($fieldValue->data);
    }

    /**
     * @param FieldDefinition $fieldDefinition
     * @param Value $value
     *
     * @return array
     */
    public function validate(FieldDefinition $fieldDefinition, SPIValue $value)
    {
        $validationErrors = array();

        if ($this->isEmptyValue($value)) {
            return $validationErrors;
        }

        $validatorConfiguration = $fieldDefinition->getValidatorConfiguration();

        if (isset($validatorConfiguration['YTVideoIdValidator']['minVideoIdLength'])
            && $validatorConfiguration['YTVideoIdValidator']['minVideoIdLength']['default'] > 0
        ) {
            if ($value->videoId < $validatorConfiguration['YTVideoIdValidator']['minVideoIdLength']) {
                $validationErrors[] = new ValidationError(
                    'Given video ID is shorter than %limit%',
                    null,
                    array(
                        '%limit%' => $validatorConfiguration['YTVideoIdValidator']['minVideoIdLength'],
                    ),
                    'value'
                );
            }
        }

        if (isset($validatorConfiguration['YTVideoIdValidator']['maxVideoIdLength'])) {
            if ($value->videoId > $validatorConfiguration['YTVideoIdValidator']['maxVideoIdLength']['default']) {
                $validationErrors[] = new ValidationError(
                    'Given video ID is longer than %limit%',
                    null,
                    array(
                        '%limit%' => $validatorConfiguration['YTVideoIdValidator']['maxVideoIdLength'],
                    ),
                    'value'
                );
            }
        }

        if (empty($value->height)) {
            $value->height = isset($fieldDefinition->fieldSettings['defaultHeight'])
                ? (int)$fieldDefinition->fieldSettings['defaultHeight']
                : self::HEIGHT_DEFAULT_VALUE;
        }

        if (empty($value->width)) {
            $value->width = isset($fieldDefinition->fieldSettings['defaultWidth'])
                ? (int)$fieldDefinition->fieldSettings['defaultWidth']
                : self::WIDTH_DEFAULT_VALUE;
        }

        return $validationErrors;
    }

    /**
     * @param mixed $validatorConfiguration
     *
     * @return array
     */
    public function validateValidatorConfiguration($validatorConfiguration)
    {
        $validationErrors = array();

        if ( !is_array($validatorConfiguration)) {
            $validationErrors[] = new ValidationError('Validator configuration must be in form of an array');

            return $validationErrors;
        }

        foreach ($validatorConfiguration as $validatorIdentifier => $constraints) {
            if ($validatorIdentifier !== 'YTVideoIdValidator') {
                $validationErrors[] = new ValidationError(
                    "Validator '%validator%' is unknown",
                    null,
                    array(
                        '%validator%' => $validatorIdentifier,
                    ),
                    "[$validatorIdentifier]"
                );

                continue;
            }

            if ( !is_array($constraints)) {
                $validationErrors[] = new ValidationError('YTVideoIdValidator constraints must be in form of an array');

                return $validationErrors;
            }

            foreach ($constraints as $name => $value) {
                switch ($name) {
                    case 'minVideoIdLength':
                        if ( !is_numeric($value)) {
                            $validationErrors[] = new ValidationError(
                                "Validator parameter '%parameter%' value must be of numeric type",
                                null,
                                array(
                                    '%parameter%' => $name,
                                ),
                                "[$validatorIdentifier][$name]"
                            );
                        }
                        if ($value < 0) {
                            $validationErrors[] = new ValidationError(
                                "Validator parameter '%parameter%' value must be equal or larger than 0",
                                null,
                                array(
                                    '%parameter%' => $name,
                                ),
                                "[$validatorIdentifier][$name]"
                            );
                        }
                        break;
                    case 'maxVideoIdLength':
                        if ( !is_numeric($value)) {
                            $validationErrors[] = new ValidationError(
                                "Validator parameter '%parameter%' value must be of numeric type",
                                null,
                                array(
                                    '%parameter%' => $name,
                                ),
                                "[$validatorIdentifier][$name]"
                            );
                        }
                        if ($value > 255) {
                            $validationErrors[] = new ValidationError(
                                "Validator parameter '%parameter%' value must be smaller or equal than 255",
                                null,
                                array(
                                    '%parameter%' => $name,
                                ),
                                "[$validatorIdentifier][$name]"
                            );
                        }
                        break;
                    default:
                        $validationErrors[] = new ValidationError(
                            "Validator parameter '%parameter%' is unknown",
                            null,
                            array(
                                '%parameter%' => $name,
                            ),
                            "[$validatorIdentifier][$name]"
                        );
                }
            }
        }

        return $validationErrors;
    }

    /**
     * @param Value $value
     *
     * @return string
     */
    protected function getSortInfo(BaseValue $value)
    {
        return $value->videoId;
    }
}
