<?php

abstract class Validator implements ValidatorInterface
{
    private array $errors = [];

    private array $data = [];

    /**
     * Validates input data according to defined rules.
     *
     * @param  object|array  $data  The data input to be validated.
     * @return static Returns the current instance of the Validator.
     */
    public function validate(object|array $data): static
    {
        $this->data = (array) $data;

        $validatedFields = [];
        foreach ($this->rules() as $fieldName => $value) {
            if (in_array('required', $value) || array_key_exists($fieldName, $this->rules())) {
                $validatedFields[$fieldName] = $value;
            }
        }

        foreach ($validatedFields as $field => $rules) {
            $value = $this->data[$field] ?? $this->getDefaultValue($field);

            if ($this->isNullable($rules, $value) || $this->isSometimes($rules, $field)) {
                continue;
            }

            foreach ($rules as $rule) {
                if ($rule == 'nullable' || $rule == 'sometimes') {
                    continue;
                }
                $method = $rule;
                $params = null;
                if (str_contains($rule, ':')) {
                    $paramDelimater = explode(':', $rule);
                    $params = end($paramDelimater);
                    $method = $paramDelimater[0];
                }
                $this->{$method}($field, $value, $params);
            }
        }

        return $this;
    }

    /**
     * Retrieves the validation errors.
     *
     * @return array Returns an array containing validation errors.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Validates if the value is a string.
     *
     * @param  string  $field  The name of the input field.
     * @param  mixed  $value  The value of the input.
     */
    public function string(string $field, mixed $value): void
    {
        if (! is_string($value) || (is_string($value) && strlen($value) == 0)) {
            $this->errors[$field][] = "The $field must be a valid string";
        }
    }

    /**
     * Validates the maximum length of a string.
     *
     * @param  string  $field  The name of the input field.
     * @param  mixed  $value  The value of the input.
     * @param  int  $length  The maximum allowed length.
     */
    public function maxLength(string $field, mixed $value, int $length): void
    {
        if (strlen($value ?? '') > $length) {
            $this->errors[$field][] = "The $field length must be less than or equal $length";
        }
    }

    /**
     * Validates a date and time string.
     *
     * @param  string  $field  The name of the input field.
     * @param  string  $value  The value of the input.
     */
    public function dateTime(string $field, mixed $value): void
    {
        if (! $value) {
            $this->errors[$field][] = "The provided $field is invalid";

            return;
        }
        if (! DateTime::createFromFormat('Y-m-d\TH:i:sO', $value)) {
            $this->errors[$field][] = "The provided $field is invalid";
        }
    }

    /**
     * Validates if the value exists in a list of allowed values.
     *
     * @param  string  $field  The name of the input field.
     * @param  string  $value  The value of the input.
     * @param  mixed  $params  A comma-separated list of allowed values.
     */
    public function in(string $field, mixed $value, mixed $params): void
    {
        if (! in_array($value, explode(',', $params))) {
            $this->errors[$field][] = "The provided $field is invalid allowed values : $params";
        }
    }

    /**
     * Validates if the value is a valid color code.
     *
     * @param  string  $field  The name of the input field.
     * @param  string  $value  The value of the input.
     */
    public function color(string $field, mixed $value): void
    {
        $pattern = '/^#[a-fA-F0-9]{6}$/';

        if (! preg_match($pattern, $value)) {
            $this->errors[$field][] = "The provided $field is invalid color";
        }
    }

    /**
     * Validates if the field is required.
     *
     * @param  string  $field  The name of the input field.
     * @param  string  $value  The value of the input.
     */
    public function required(string $field, mixed $value): void
    {
        if (! array_key_exists($field, $this->data) || is_null($value)) {
            $this->errors[$field][] = "The $field field is required";
        }
    }

    /**
     * Checks if a field is nullable.
     *
     * @param  array  $rules  The validation rules for the field.
     * @param  mixed  $value  The value of the input.
     * @return bool Returns true if the field is nullable, false otherwise.
     */
    public function isNullable(array $rules, mixed $value): bool
    {
        return in_array('nullable', $rules) && is_null($value);
    }

    /**
     * Checks if a field is only validated sometimes.
     *
     * @param  array  $rules  The validation rules for the field.
     * @param  string  $field  The name of the input field.
     * @return bool Returns true if the field is sometimes validated, false otherwise.
     */
    public function isSometimes(array $rules, string $field): bool
    {
        return in_array('sometimes', $rules) && ! array_key_exists($field, $this->data);
    }

    /**
     * Validates if the date is after a specified date.
     *
     * @param  string  $field  The name of the input field.
     * @param  string  $value  The value of the input.
     * @param  mixed  $param  The name of the field to compare with.
     */
    public function after(string $field, mixed $value, mixed $param): void
    {
        $afterDate = $this->data[$param] ?? null;

        if (is_null($afterDate)) {
            $this->errors[$field][] = "The provided $field is invalid";

            return;
        }

        $fieldValue = new DateTime($value);
        $afterDate = new DateTime($this->data[$param]);
        if ($fieldValue <= $afterDate) {
            $this->errors[$field][] = "The $field must be after $param";
        }
    }

    /**
     * Retrieves the default value for a field.
     *
     * @param  string  $field  The name of the input field.
     * @return mixed Returns the default value for
     */
    public function getDefaultValue(string $field): mixed
    {
        return $this->defaults()[$field] ?? null;
    }

    /**
     * Checks if validation has passed without any errors.
     *
     * @return bool Returns true if validation passed, false otherwise.
     */
    public function passed(): bool
    {
        return count($this->getErrors()) === 0;
    }

    /**
     * Returns an array of validation rules.
     *
     * @return array Returns an array of validation rules.
     */
    abstract public function rules(): array;

    /**
     * Returns an array of default values for fields.
     *
     * @return array Returns an array of default values for fields.
     */
    abstract public function defaults(): array;
}
