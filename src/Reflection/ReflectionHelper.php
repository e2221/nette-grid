<?php declare(strict_types=1);


namespace e2221\NetteGrid\Reflection;


use Nette\SmartObject;
use Nette\Utils\ArrayHash;
use Nette\Utils\Callback;
use Nette\Utils\Reflection;
use ReflectionException;
use ReflectionProperty;

/**
 * Class ReflectionHelper
 * @package e2221\NetteGrid\Reflection
 */
class ReflectionHelper
{
    use SmartObject;

    private const
        ARRAY               = 'array',
        ARRAY_HASH          = 'Nette\Utils\ArrayHash',
        TO_ARRAY_METHODS    = [
            'toArray',
        ];

    /**
     * Get callback parameter type
     * @param callable $callback
     * @param int $offset
     * @return string|null
     * @throws ReflectionException
     */
    public static function getCallbackParameterType(callable $callback, int $offset = 0): ?string
    {
        $reflection = Callback::toReflection($callback);
        $parameters = $reflection->getParameters();
        if(!isset($parameters[$offset])){
            return null;
        }
        return Reflection::getParameterType($parameters[$offset]);
    }

    /**
     * Get count of callback parameters
     * @param callable $callback
     * @return int
     * @throws ReflectionException
     */
    public static function getCallbackParametersCount(callable $callback): int
    {
        $reflection = Callback::toReflection($callback);
        $parameters = $reflection->getParameters();

        return count($parameters);
    }

    /**
     * Prepare callback closure in given object - form
     * @param ArrayHash $data
     * @param string|null $type
     * @return ArrayHash|mixed|mixed[]
     * @throws ReflectionException
     */
    public static function getFormCallbackClosure(ArrayHash $data, ?string $type)
    {
        if ($type == self::ARRAY_HASH || is_null($type)) {
            return $data;
        } elseif ($type == self::ARRAY) {
            return self::dataToArray($data);
        } else {
            return self::toCustomMapper($data, $type);
        }
    }


    /**
     * @param mixed $data
     * @param string|null $returnType
     * @return array|mixed|ArrayHash
     * @throws ReflectionException
     */
    public static function getRowCallbackClosure($data, ?string $returnType)
    {
        if (is_null($returnType)) {
            return $data;
        }
        $dataType = self::getDataType($data);
        if ($dataType == $returnType) {
            return $data;
        }

        switch ($returnType) {
            case self::ARRAY:
                return self::dataToArray($data);
            case self::ARRAY_HASH:
                return self::toArrayHash($data);
            default:
                return self::toCustomMapper($data, $returnType);
        }
    }

    /**
     * @param mixed $data
     * @return string
     */
    private static function getDataType($data): string
    {
        $type = gettype($data);
        return $type == 'object' ? get_class($data) : $type;
    }

    /**
     * @param mixed $data
     * @return mixed[]
     */
    private static function dataToArray($data): array
    {
        if (is_array($data)) {
            return (array)$data;
        }
        foreach(self::TO_ARRAY_METHODS as $method){
            if(method_exists($data, $method)){
                return $data->$method();
            }
        }
        return (array)$data;
    }

    /**
     * @param mixed $data
     * @return ArrayHash
     */
    private static function toArrayHash($data): ArrayHash
    {
        return ArrayHash::from(self::dataToArray($data));
    }

    /**
     * @param mixed $object
     * @param string $returnType
     * @return mixed
     * @throws ReflectionException
     */
    private static function toCustomMapper($object, string $returnType)
    {
        $obj = new $returnType();
        $objectFields = self::dataToArray($object);
        foreach ($objectFields as $key => $value) {
            if (property_exists($obj, $key)) {
                $prop = new ReflectionProperty($obj, $key);
                $propType = Reflection::getPropertyType($prop);
                if (empty($value) && $prop->getType()->allowsNull()) {
                    $value = null;
                } elseif (is_scalar($value)) {
                    settype($value, $propType);
                } elseif (is_object($value) && (get_class($value) != $propType) && isset($value->id)){
                    $value = $value->id;
                }
                $obj->$key = $value;
            }
        }
        return $obj;
    }

}
