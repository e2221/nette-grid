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

    /**
     * Get callback parameter type
     * @param callable $callback
     * @param int $offset
     * @return string|null
     * @throws ReflectionException
     */
    public static function getCallbackParameterType(callable $callback, int $offset=0): ?string
    {
        $reflection = Callback::toReflection($callback);
        return Reflection::getParameterType(
            $reflection->getParameters()[$offset]
        );
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
        if ($type == 'Nette\Utils\ArrayHash' || is_null($type)) {
            return $data;
        } elseif ($type == 'array') {
            return (array)$data;
        } else {
            $result = new $type();
            foreach ($data as $key => $value) {
                if (property_exists($result, $key)) {
                    $prop = new ReflectionProperty($result, $key);
                    $type = Reflection::getPropertyType($prop);
                    if(empty($value) && $prop->getType()->allowsNull()){
                        $value = null;
                    }else{
                        settype($value, $type);
                    }
                    $result->$key = $value;
                }
            }

            return $result;
        }
    }

    /**
     * @param mixed $data
     * @param string|null $type
     * @return array|mixed|ArrayHash
     * @throws ReflectionException
     */
    public static function getRowCallbackClosure($data, ?string $type)
    {
        if(is_null($type)) {
            return $data;
        }

        $dataType = gettype($data);

        if($dataType == $type) {
            return $data;
        }

        if($type == 'Nette\Utils\ArrayHash' && $dataType == 'array') {
            return ArrayHash::from((array)$data);
        } elseif ($dataType == 'object') {
            $dataType = get_class($data);

            if($dataType == 'Nette\Utils\ArrayHash' && $type == 'array') {
                return (array) $data;

            }else{
                $result = new $type();
                foreach ($data as $key => $value) {
                    if (property_exists($result, $key)) {
                        $prop = new ReflectionProperty($result, $key);
                        $type = Reflection::getPropertyType($prop);
                        if(empty($value) && $prop->getType()->allowsNull()){
                            $value = null;
                        }else{
                            settype($value, $type);
                        }
                        $result->$key = $value;
                    }
                }
                return $result;
            }
        }else{
            return $data;
        }
    }
}