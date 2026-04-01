<?php

/**

 * Sistema de validación centralizado

 * Valida entradas de usuario para prevenir errores y ataques

 */



/**

 * Excepción personalizada para errores de validación

 */

class ValidationException extends Exception {

    private $field;

    private $errors;

    

    public function __construct($message, $errors = []) {

        parent::__construct($message);

        

        // Si $errors es un string, convertir a array con campo único

        if (is_string($errors)) {

            $this->field = $errors;

            $this->errors = [$errors => $message];

        } else {

            $this->errors = $errors;

            $this->field = !empty($errors) ? array_key_first($errors) : null;

        }

    }

    

    public function getField() {

        return $this->field;

    }

    

    public function getErrors() {

        return $this->errors;

    }

}



/**

 * Clase Validator - Funciones de validación reutilizables

 */

class Validator {

    

    /**

     * Valida que los campos requeridos existan y no estén vacíos

     * 

     * @param array $data Datos a validar

     * @param array $fields Campos requeridos

     * @throws ValidationException Si falta algún campo

     */

    public static function required($data, $fields) {

        $missing = [];

        

        foreach ($fields as $field) {

            if (!isset($data[$field]) || trim($data[$field]) === '') {

                $missing[] = $field;

            }

        }

        

        if (!empty($missing)) {

            throw new ValidationException('Campos requeridos faltantes: ' . implode(', ', $missing));

        }

        

        return true;

    }

    

    /**

     * Valida que un valor sea numérico y esté en el rango permitido

     * 

     * @param mixed $value Valor a validar

     * @param float|null $min Valor mínimo permitido

     * @param float|null $max Valor máximo permitido

     * @param string $fieldName Nombre del campo (para mensajes de error)

     * @return float Valor convertido a float

     * @throws ValidationException Si no es válido

     */

    public static function numeric($value, $min = null, $max = null, $fieldName = 'valor') {

        if (!is_numeric($value)) {

            throw new ValidationException("$fieldName debe ser numérico", $fieldName);

        }

        

        $value = floatval($value);

        

        if ($min !== null && $value < $min) {

            throw new ValidationException("$fieldName debe ser mayor o igual a $min", $fieldName);

        }

        

        if ($max !== null && $value > $max) {

            throw new ValidationException("$fieldName debe ser menor o igual a $max", $fieldName);

        }

        

        return $value;

    }

    

    /**

     * Valida que un valor sea entero positivo

     * 

     * @param mixed $value Valor a validar

     * @param string $fieldName Nombre del campo

     * @return int Valor convertido a int

     * @throws ValidationException Si no es válido

     */

    public static function positiveInt($value, $fieldName = 'valor') {

        if (!is_numeric($value) || intval($value) <= 0) {

            throw new ValidationException("$fieldName debe ser un número entero positivo", $fieldName);

        }

        

        return intval($value);

    }

    

    /**

     * Valida formato de fecha

     * 

     * @param string $date Fecha a validar

     * @param string $format Formato esperado (default: Y-m-d)

     * @return string Fecha validada

     * @throws ValidationException Si no es válida

     */

    public static function date($date, $format = 'Y-m-d') {

        $d = DateTime::createFromFormat($format, $date);

        

        if (!$d || $d->format($format) !== $date) {

            throw new ValidationException("Fecha inválida. Formato esperado: $format", 'fecha');

        }

        

        return $date;

    }

    

    /**

     * Valida formato de email

     * 

     * @param string $email Email a validar

     * @return string Email validado

     * @throws ValidationException Si no es válido

     */

    public static function email($email) {

        $email = trim($email);

        

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            throw new ValidationException('Email inválido', 'email');

        }

        

        return $email;

    }

    

    /**

     * Valida que el valor sea uno de los permitidos (enum)

     * 

     * @param mixed $value Valor a validar

     * @param array $allowedValues Valores permitidos

     * @param string $fieldName Nombre del campo

     * @return mixed Valor validado

     * @throws ValidationException Si no es válido

     */

    public static function enum($value, $allowedValues, $fieldName = 'valor') {

        if (!in_array($value, $allowedValues, true)) {

            throw new ValidationException(

                "$fieldName debe ser uno de: " . implode(', ', $allowedValues),

                $fieldName

            );

        }

        

        return $value;

    }

    

    /**

     * Valida longitud de string

     * 

     * @param string $value String a validar

     * @param int|null $min Longitud mínima

     * @param int|null $max Longitud máxima

     * @param string $fieldName Nombre del campo

     * @return string String validado

     * @throws ValidationException Si no cumple la longitud

     */

    public static function length($value, $min = null, $max = null, $fieldName = 'campo') {

        $len = mb_strlen($value);

        

        if ($min !== null && $len < $min) {

            throw new ValidationException("$fieldName debe tener al menos $min caracteres", $fieldName);

        }

        

        if ($max !== null && $len > $max) {

            throw new ValidationException("$fieldName debe tener máximo $max caracteres", $fieldName);

        }

        

        return $value;

    }

    

    /**

     * Valida que un valor sea booleano

     * 

     * @param mixed $value Valor a validar

     * @param string $fieldName Nombre del campo

     * @return bool Valor convertido a boolean

     */

    public static function boolean($value, $fieldName = 'valor') {

        if ($value === true || $value === 1 || $value === '1' || $value === 'true') {

            return true;

        }

        

        if ($value === false || $value === 0 || $value === '0' || $value === 'false') {

            return false;

        }

        

        throw new ValidationException("$fieldName debe ser booleano (true/false)", $fieldName);

    }

    

    /**

     * Valida porcentaje (0-100)

     * 

     * @param mixed $value Valor a validar

     * @param string $fieldName Nombre del campo

     * @return float Porcentaje validado

     * @throws ValidationException Si no es válido

     */

    public static function percentage($value, $fieldName = 'porcentaje') {

        return self::numeric($value, 0, 100, $fieldName);

    }

    

    /**

     * Valida moneda (MXN, USD, USDT)

     * 

     * @param string $value Moneda a validar

     * @return string Moneda validada

     * @throws ValidationException Si no es válida

     */

    public static function currency($value) {

        return self::enum($value, ['MXN', 'USD', 'USDT'], 'moneda');

    }

    

    /**

     * Valida que un array no esté vacío

     * 

     * @param array $value Array a validar

     * @param string $fieldName Nombre del campo

     * @return array Array validado

     * @throws ValidationException Si está vacío

     */

    public static function notEmptyArray($value, $fieldName = 'array') {

        if (!is_array($value) || empty($value)) {

            throw new ValidationException("$fieldName no puede estar vacío", $fieldName);

        }

        

        return $value;

    }

    

    /**

     * Sanitiza un string removiendo HTML y espacios extra

     * 

     * @param string $value String a sanitizar

     * @return string String sanitizado

     */

    public static function sanitize($value) {

        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');

    }

    

    /**

     * Valida múltiples campos con sus reglas

     * 

     * @param array $data Datos a validar

     * @param array $rules Reglas de validación

     * @return array Datos validados

     * @throws ValidationException Si alguna validación falla

     * 

     * Ejemplo:

     * Validator::validateAll($_POST, [

     *     'nombre' => ['required', 'length:3,100'],

     *     'email' => ['required', 'email'],

     *     'edad' => ['numeric:18,100']

     * ]);

     */

    public static function validateAll($data, $rules) {

        $validated = [];

        

        foreach ($rules as $field => $fieldRules) {

            $value = $data[$field] ?? null;

            

            foreach ($fieldRules as $rule) {

                if ($rule === 'required') {

                    self::required($data, [$field]);

                } elseif ($rule === 'email') {

                    $validated[$field] = self::email($value);

                } elseif (strpos($rule, 'numeric') === 0) {

                    $params = explode(':', $rule);

                    $range = isset($params[1]) ? explode(',', $params[1]) : [null, null];

                    $validated[$field] = self::numeric($value, $range[0] ?? null, $range[1] ?? null, $field);

                } elseif (strpos($rule, 'length') === 0) {

                    $params = explode(':', $rule);

                    $range = isset($params[1]) ? explode(',', $params[1]) : [null, null];

                    $validated[$field] = self::length($value, $range[0] ?? null, $range[1] ?? null, $field);

                }

            }

        }

        

        return $validated;

    }

}

