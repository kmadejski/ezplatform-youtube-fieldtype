<?php

namespace EzSystems\YouTubeFieldType\Core\FieldType;

use eZ\Publish\Core\FieldType\Value as BaseValue;

class Value extends BaseValue
{
    /**
     * YouTube video ID.
     *
     * URL: https://www.youtube.com/watch?v=DwnL82UK7Lw
     * Video ID: DwnL82UK7Lw
     *
     * @var string
     */
    public $videoId;

    /**
     * Embed title.
     *
     * @var string
     */
    public $title;

    /**
     * Autoplay embed option.
     *
     * @var bool
     */
    public $autoplay;

    /**
     * Embed window width.
     *
     * @var int
     */
    public $width;

    /**
     * Embed window height.
     *
     * @var int
     */
    public $height;

    /**
     * Value constructor.
     * @param array|null $values
     */
    public function __construct(array $values = null)
    {
        foreach ((array) $values as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Returns a string representation of the field value.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->videoId;
    }
}
