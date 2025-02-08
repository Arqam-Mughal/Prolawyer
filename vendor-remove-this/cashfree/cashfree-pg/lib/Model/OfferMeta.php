<?php
/**
 * OfferMeta
 *
 * PHP version 7.4
 *
 * @category Class
 * @package  Cashfree
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 */

/**
 * Cashfree Payment Gateway APIs
 *
 * Cashfree's Payment Gateway APIs provide developers with a streamlined pathway to integrate advanced payment processing capabilities into their applications, platforms and websites.
 *
 * The version of the OpenAPI document: 2023-08-01
 * Contact: developers@cashfree.com
 * Generated by: https://openapi-generator.tech
 * OpenAPI Generator version: 7.0.0
 */

/**
 * NOTE: This class is auto generated by OpenAPI Generator (https://openapi-generator.tech).
 * https://openapi-generator.tech
 * Do not edit the class manually.
 */

namespace Cashfree\Model;

use \ArrayAccess;
use \Cashfree\ObjectSerializer;

/**
 * OfferMeta Class Doc Comment
 *
 * @category Class
 * @description Offer meta details object
 * @package  Cashfree
 * @author   OpenAPI Generator team
 * @link     https://openapi-generator.tech
 * @implements \ArrayAccess<string, mixed>
 */
class OfferMeta implements ModelInterface, ArrayAccess, \JsonSerializable
{
    public const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $openAPIModelName = 'OfferMeta';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $openAPITypes = [
        'offer_title' => 'string',
        'offer_description' => 'string',
        'offer_code' => 'string',
        'offer_start_time' => 'string',
        'offer_end_time' => 'string'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      * @phpstan-var array<string, string|null>
      * @psalm-var array<string, string|null>
      */
    protected static $openAPIFormats = [
        'offer_title' => null,
        'offer_description' => null,
        'offer_code' => null,
        'offer_start_time' => null,
        'offer_end_time' => null
    ];

    /**
      * Array of nullable properties. Used for (de)serialization
      *
      * @var boolean[]
      */
    protected static $openAPINullables = [
        'offer_title' => false,
		'offer_description' => false,
		'offer_code' => false,
		'offer_start_time' => false,
		'offer_end_time' => false
    ];

    /**
      * If a nullable field gets set to null, insert it here
      *
      * @var boolean[]
      */
    protected $openAPINullablesSetToNull = [];

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPITypes()
    {
        return self::$openAPITypes;
    }

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function openAPIFormats()
    {
        return self::$openAPIFormats;
    }

    /**
     * Array of nullable properties
     *
     * @return array
     */
    protected static function openAPINullables(): array
    {
        return self::$openAPINullables;
    }

    /**
     * Array of nullable field names deliberately set to null
     *
     * @return boolean[]
     */
    private function getOpenAPINullablesSetToNull(): array
    {
        return $this->openAPINullablesSetToNull;
    }

    /**
     * Setter - Array of nullable field names deliberately set to null
     *
     * @param boolean[] $openAPINullablesSetToNull
     */
    private function setOpenAPINullablesSetToNull(array $openAPINullablesSetToNull): void
    {
        $this->openAPINullablesSetToNull = $openAPINullablesSetToNull;
    }

    /**
     * Checks if a property is nullable
     *
     * @param string $property
     * @return bool
     */
    public static function isNullable(string $property): bool
    {
        return self::openAPINullables()[$property] ?? false;
    }

    /**
     * Checks if a nullable property is set to null.
     *
     * @param string $property
     * @return bool
     */
    public function isNullableSetToNull(string $property): bool
    {
        return in_array($property, $this->getOpenAPINullablesSetToNull(), true);
    }

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'offer_title' => 'offer_title',
        'offer_description' => 'offer_description',
        'offer_code' => 'offer_code',
        'offer_start_time' => 'offer_start_time',
        'offer_end_time' => 'offer_end_time'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'offer_title' => 'setOfferTitle',
        'offer_description' => 'setOfferDescription',
        'offer_code' => 'setOfferCode',
        'offer_start_time' => 'setOfferStartTime',
        'offer_end_time' => 'setOfferEndTime'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'offer_title' => 'getOfferTitle',
        'offer_description' => 'getOfferDescription',
        'offer_code' => 'getOfferCode',
        'offer_start_time' => 'getOfferStartTime',
        'offer_end_time' => 'getOfferEndTime'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @return array
     */
    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @return array
     */
    public static function setters()
    {
        return self::$setters;
    }

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @return array
     */
    public static function getters()
    {
        return self::$getters;
    }

    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName()
    {
        return self::$openAPIModelName;
    }


    /**
     * Associative array for storing property values
     *
     * @var mixed[]
     */
    protected $container = [];

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values
     *                      initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->setIfExists('offer_title', $data ?? [], null);
        $this->setIfExists('offer_description', $data ?? [], null);
        $this->setIfExists('offer_code', $data ?? [], null);
        $this->setIfExists('offer_start_time', $data ?? [], null);
        $this->setIfExists('offer_end_time', $data ?? [], null);
    }

    /**
    * Sets $this->container[$variableName] to the given data or to the given default Value; if $variableName
    * is nullable and its value is set to null in the $fields array, then mark it as "set to null" in the
    * $this->openAPINullablesSetToNull array
    *
    * @param string $variableName
    * @param array  $fields
    * @param mixed  $defaultValue
    */
    private function setIfExists(string $variableName, array $fields, $defaultValue): void
    {
        if (self::isNullable($variableName) && array_key_exists($variableName, $fields) && is_null($fields[$variableName])) {
            $this->openAPINullablesSetToNull[] = $variableName;
        }

        $this->container[$variableName] = $fields[$variableName] ?? $defaultValue;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        if ($this->container['offer_title'] === null) {
            $invalidProperties[] = "'offer_title' can't be null";
        }
        if ((mb_strlen($this->container['offer_title']) > 50)) {
            $invalidProperties[] = "invalid value for 'offer_title', the character length must be smaller than or equal to 50.";
        }

        if ((mb_strlen($this->container['offer_title']) < 3)) {
            $invalidProperties[] = "invalid value for 'offer_title', the character length must be bigger than or equal to 3.";
        }

        if ($this->container['offer_description'] === null) {
            $invalidProperties[] = "'offer_description' can't be null";
        }
        if ((mb_strlen($this->container['offer_description']) > 100)) {
            $invalidProperties[] = "invalid value for 'offer_description', the character length must be smaller than or equal to 100.";
        }

        if ((mb_strlen($this->container['offer_description']) < 3)) {
            $invalidProperties[] = "invalid value for 'offer_description', the character length must be bigger than or equal to 3.";
        }

        if ($this->container['offer_code'] === null) {
            $invalidProperties[] = "'offer_code' can't be null";
        }
        if ((mb_strlen($this->container['offer_code']) > 45)) {
            $invalidProperties[] = "invalid value for 'offer_code', the character length must be smaller than or equal to 45.";
        }

        if ((mb_strlen($this->container['offer_code']) < 1)) {
            $invalidProperties[] = "invalid value for 'offer_code', the character length must be bigger than or equal to 1.";
        }

        if ($this->container['offer_start_time'] === null) {
            $invalidProperties[] = "'offer_start_time' can't be null";
        }
        if ((mb_strlen($this->container['offer_start_time']) > 20)) {
            $invalidProperties[] = "invalid value for 'offer_start_time', the character length must be smaller than or equal to 20.";
        }

        if ((mb_strlen($this->container['offer_start_time']) < 3)) {
            $invalidProperties[] = "invalid value for 'offer_start_time', the character length must be bigger than or equal to 3.";
        }

        if ($this->container['offer_end_time'] === null) {
            $invalidProperties[] = "'offer_end_time' can't be null";
        }
        return $invalidProperties;
    }

    /**
     * Validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return count($this->listInvalidProperties()) === 0;
    }


    /**
     * Gets offer_title
     *
     * @return string
     */
    public function getOfferTitle()
    {
        return $this->container['offer_title'];
    }

    /**
     * Sets offer_title
     *
     * @param string $offer_title Title for the Offer.
     *
     * @return self
     */
    public function setOfferTitle($offer_title)
    {
        if (is_null($offer_title)) {
            throw new \InvalidArgumentException('non-nullable offer_title cannot be null');
        }
        if ((mb_strlen($offer_title) > 50)) {
            throw new \InvalidArgumentException('invalid length for $offer_title when calling OfferMeta., must be smaller than or equal to 50.');
        }
        if ((mb_strlen($offer_title) < 3)) {
            throw new \InvalidArgumentException('invalid length for $offer_title when calling OfferMeta., must be bigger than or equal to 3.');
        }

        $this->container['offer_title'] = $offer_title;

        return $this;
    }

    /**
     * Gets offer_description
     *
     * @return string
     */
    public function getOfferDescription()
    {
        return $this->container['offer_description'];
    }

    /**
     * Sets offer_description
     *
     * @param string $offer_description Description for the Offer.
     *
     * @return self
     */
    public function setOfferDescription($offer_description)
    {
        if (is_null($offer_description)) {
            throw new \InvalidArgumentException('non-nullable offer_description cannot be null');
        }
        if ((mb_strlen($offer_description) > 100)) {
            throw new \InvalidArgumentException('invalid length for $offer_description when calling OfferMeta., must be smaller than or equal to 100.');
        }
        if ((mb_strlen($offer_description) < 3)) {
            throw new \InvalidArgumentException('invalid length for $offer_description when calling OfferMeta., must be bigger than or equal to 3.');
        }

        $this->container['offer_description'] = $offer_description;

        return $this;
    }

    /**
     * Gets offer_code
     *
     * @return string
     */
    public function getOfferCode()
    {
        return $this->container['offer_code'];
    }

    /**
     * Sets offer_code
     *
     * @param string $offer_code Unique identifier for the Offer.
     *
     * @return self
     */
    public function setOfferCode($offer_code)
    {
        if (is_null($offer_code)) {
            throw new \InvalidArgumentException('non-nullable offer_code cannot be null');
        }
        if ((mb_strlen($offer_code) > 45)) {
            throw new \InvalidArgumentException('invalid length for $offer_code when calling OfferMeta., must be smaller than or equal to 45.');
        }
        if ((mb_strlen($offer_code) < 1)) {
            throw new \InvalidArgumentException('invalid length for $offer_code when calling OfferMeta., must be bigger than or equal to 1.');
        }

        $this->container['offer_code'] = $offer_code;

        return $this;
    }

    /**
     * Gets offer_start_time
     *
     * @return string
     */
    public function getOfferStartTime()
    {
        return $this->container['offer_start_time'];
    }

    /**
     * Sets offer_start_time
     *
     * @param string $offer_start_time Start Time for the Offer
     *
     * @return self
     */
    public function setOfferStartTime($offer_start_time)
    {
        if (is_null($offer_start_time)) {
            throw new \InvalidArgumentException('non-nullable offer_start_time cannot be null');
        }
        if ((mb_strlen($offer_start_time) > 20)) {
            throw new \InvalidArgumentException('invalid length for $offer_start_time when calling OfferMeta., must be smaller than or equal to 20.');
        }
        if ((mb_strlen($offer_start_time) < 3)) {
            throw new \InvalidArgumentException('invalid length for $offer_start_time when calling OfferMeta., must be bigger than or equal to 3.');
        }

        $this->container['offer_start_time'] = $offer_start_time;

        return $this;
    }

    /**
     * Gets offer_end_time
     *
     * @return string
     */
    public function getOfferEndTime()
    {
        return $this->container['offer_end_time'];
    }

    /**
     * Sets offer_end_time
     *
     * @param string $offer_end_time Expiry Time for the Offer
     *
     * @return self
     */
    public function setOfferEndTime($offer_end_time)
    {
        if (is_null($offer_end_time)) {
            throw new \InvalidArgumentException('non-nullable offer_end_time cannot be null');
        }
        $this->container['offer_end_time'] = $offer_end_time;

        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param integer $offset Offset
     *
     * @return boolean
     */
    public function offsetExists($offset): bool
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param integer $offset Offset
     *
     * @return mixed|null
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->container[$offset] ?? null;
    }

    /**
     * Sets value based on offset.
     *
     * @param int|null $offset Offset
     * @param mixed    $value  Value to be set
     *
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     *
     * @param integer $offset Offset
     *
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->container[$offset]);
    }

    /**
     * Serializes the object to a value that can be serialized natively by json_encode().
     * @link https://www.php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed Returns data which can be serialized by json_encode(), which is a value
     * of any type other than a resource.
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
       return ObjectSerializer::sanitizeForSerialization($this);
    }

    /**
     * Gets the string presentation of the object
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode(
            ObjectSerializer::sanitizeForSerialization($this),
            JSON_PRETTY_PRINT
        );
    }

    /**
     * Gets a header-safe presentation of the object
     *
     * @return string
     */
    public function toHeaderValue()
    {
        return json_encode(ObjectSerializer::sanitizeForSerialization($this));
    }
}


