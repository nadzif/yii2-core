<?php
/**
 * Created by PhpStorm.
 * User: Nadzif Glovory
 * Date: 11/14/2019
 * Time: 3:56 AM
 */

namespace nadzif\core\web\components;


class ClosureHelper
{
    public $closureMarker = 'SuperClosure';

    /** @var SerializerInterface */
    protected $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param $object
     *
     * @return string
     */
    public function serialize($object)
    {
        $this->transformClosures($object);
        $serialized = serialize($object);
        $this->restoreClosures($object);
        return $serialized;
    }

    /**
     * @param $object
     *
     * @return mixed
     */
    public function transformClosures(&$object)
    {
        if (is_object($object) || is_array($object)) {
            foreach ($object as $key => &$value) {
                if ($value instanceof Closure) {
                    $value = $this->serializer->serialize($value);
                } elseif (is_object($value) || is_array($value)) {
                    $value = $this->transformClosures($value);
                }
            }
        }
        return $object;
    }

    /**
     * @param $object
     *
     * @return mixed
     */
    public function restoreClosures(&$object)
    {
        if (is_object($object) || is_array($object)) {
            foreach ($object as $key => &$value) {
                if (is_string($value) && strpos($value, $this->closureMarker)) {
                    $value = $this->serializer->unserialize($value);
                } elseif (is_object($value) || is_array($value)) {
                    $value = $this->restoreClosures($value);
                }
            }
        }
        return $object;
    }

    /**
     * @param  string  $data
     *
     * @return mixed
     */
    public function unserialize($data)
    {
        $object = unserialize($data);
        $this->restoreClosures($object);
        return $object;
    }
}