<?php

interface ValidatorInterface
{
    /**
     * Validates input data according to defined rules.
     *
     * @param array $data The data input to be validated.
     * @return static Returns the current instance of the Validator.
     */
    public function validate(array $data): static;

    /**
     * Retrieves the validation errors.
     *
     * @return array Returns an array containing validation errors.
     */
    public function getErrors(): array;
}
