<?php


class CreateConstructionStageValidator extends Validator
{
    public function rules(): array
    {
        return [
            'name' => ['required','string','maxLength:255'],
            'externalId' => ['nullable','string','maxLength:255'],
            'startDate'=>['dateTime'],
            'endDate' => ['nullable','dateTime','after:startDate'],
            'color' => ['nullable','color'],
            'status' => ['required','in:NEW,PLANNED,DELETED'],
            'durationUnit' => ['in:HOURS,DAYS,WEEKS']
        ];
    }

    public function defaults(): array
    {
        return [
            'durationUnit' => 'DAYS'
        ];
    }
}
