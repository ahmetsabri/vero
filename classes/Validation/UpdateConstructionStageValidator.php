<?php


class UpdateConstructionStageValidator extends Validator
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes','string','maxLength:255'],
            'externalId' => ['sometimes','nullable','string','maxLength:255'],
            'startDate'=>['sometimes','dateTime'],
            'endDate' => ['sometimes','nullable','dateTime'],
            'color' => ['sometimes','nullable','color'],
            'status' => ['required','in:NEW,PLANNED,DELETED'],
            'durationUnit' => ['sometimes','in:HOURS,DAYS,WEEKS']
        ];
    }

    public function defaults(): array
    {
        return [
            'durationUnit' => 'DAYS'
        ];
    }
}
