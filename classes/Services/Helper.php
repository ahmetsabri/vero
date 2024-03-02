<?php

trait Helper
{
    /**
     * Calculates the duration between two dates based on the given duration unit.
     *
     * @param  string  $durationUnit  The unit of duration (HOURS, DAYS, or WEEKS).
     * @param  string  $startDate  The start date.
     * @param  string|null  $endDate  The end date (optional).
     * @return float|null Returns the calculated duration in the specified unit, or null if the end date is not provided.
     */
    public function calculateDuration(string $durationUnit, string $startDate, ?string $endDate = null): ?float
    {
        if (is_null($endDate)) {
            return null;
        }

        $startDate = new DateTime($startDate);
        $endDate = new DateTime($endDate);
        if ($endDate <= $startDate) {
            $this->sendErrorResponse('The end date must be after start date');
        }
        $diff = $startDate->diff($endDate);
        $diffInHours = $diff->h + ($diff->days * 24);

        $duration = match ($durationUnit) {
            'HOURS' => $diffInHours,
            'DAYS' => $diffInHours / 24,
            'WEEKS' => $diffInHours / (24 * 7),
        };

        return round($duration, 2);
    }

    /**
     * Converts a string from camelCase to snake_case.
     *
     * @param  string  $input  The input string in camelCase.
     * @return string Returns the converted string in snake_case.
     */
    public function camelCaseToSnakeCase(string $input): string
    {
        $pattern = '/(?<=\w)(?=[A-Z][a-z\d])/';
        $snakeCase = preg_replace($pattern, '_', $input);

        return strtolower($snakeCase);
    }

    /**
     * Checks if any of the specified keys exist in the given array of changes.
     *
     * @param  array  $changes  The array of changes.
     * @param  mixed  ...$keys  The keys to check for existence in the changes array.
     * @return bool Returns true if any of the keys exist in the changes array, otherwise false.
     */
    public function hasChanges(array $changes, ...$keys)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $changes)) {
                return true;
                break;
            }
        }

        return false;
    }

    /**
     * Sends an error response with the provided message.
     *
     * @param  string  $message  The error message.
     * @param  int  $code  The error code.
     */
    public function sendErrorResponse(string $message, int $status=400): void
    {
        http_response_code($status);
        $errorResponse = [
            'message' => $message,
        ];
        echo json_encode($errorResponse);
        exit();
    }

    /**
     * Sends an success response with the provided message.
     *
     * @param  string  $message  The success message.
     * @param  int  $code  The status code.
     */
    public function sendSuccessResponse(string $message='', int $status=200): void
    {
        http_response_code($status);
        $errorResponse = [
            'message' => $message,
        ];
        echo json_encode($errorResponse);
        exit();
    }
}
