<?php

class ConstructionStagesUpdate
{
    
    use ToArray;

    public ?string $name;
    public ?string $startDate;
    public ?string $endDate;
    public ?string $durationUnit;
    public ?string $color;
    public ?string $externalId;
    public ?string $status;

    public function __construct(object $data)
    {
        if (is_object($data)) {
            $vars = get_object_vars($data);

            foreach ($vars as $name => $value) {
                if (!property_exists($this, $name)) {
                    continue;
                }
                $this->$name = $value;
            }
        }
    }
}
