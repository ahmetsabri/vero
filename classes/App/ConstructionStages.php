<?php


class ConstructionStages
{
    use Helper;

    private $db;

    public function __construct()
    {
        $this->db = Api::getDb();
    }

    public function getAll()
    {
        $stmt = $this->db->prepare("
			SELECT
				ID as id,
				name,
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as startDate,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as endDate,
				duration,
				durationUnit,
				color,
				externalId,
				status
			FROM construction_stages limit 100
		");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSingle(int $id)
    {
        $stmt = $this->db->prepare("
			SELECT
				ID as id,
				name,
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as startDate,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as endDate,
				duration,
				durationUnit,
				color,
				externalId,
				status
			FROM construction_stages
			WHERE ID = :id
		");
        $stmt->execute(['id' => $id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Handles the creation of a new construction stage.
     *
     * @param  ConstructionStagesCreate  $data  The data object containing information about the construction stage to be created.
     * @return array Returns an array containing information about the newly created construction stage, or an array of validation errors if validation fails.
     */
    public function post(ConstructionStagesCreate $data): array
    {
        $validator = (new CreateConstructionStageValidator())->validate($data);

        if (! $validator->passed()) {
            http_response_code(422);

            return $validator->getErrors();
        }

        $stmt = $this->db->prepare('
			INSERT INTO construction_stages
			    (name, start_date, end_date, duration, durationUnit, color, externalId, status)
			    VALUES (:name, :start_date, :end_date, :duration, :durationUnit, :color, :externalId, :status)
			');
        $stmt->execute([
            'name' => $data->name,
            'start_date' => $data->startDate,
            'end_date' => $data->endDate,
            'duration' => $this->calculateDuration($data->durationUnit, $data->startDate, $data->endDate),
            'durationUnit' => $data->durationUnit,
            'color' => $data->color,
            'externalId' => $data->externalId,
            'status' => $data->status,
        ]);

        return $this->getSingle($this->db->lastInsertId());
    }

    /**
     * Deletes a construction stage by its ID.
     *
     * @param  mixed  $id  The ID of the construction stage to be deleted.
     * @return void indicating a successful deletion operation.
     */
    public function delete(int $id)
    {
        $stmt = $this->db->prepare('
		UPDATE construction_stages
        SET status = :status
        where ID=:id');

        $stmt->execute(['id'=>$id,'status' => 'DELETED']);

        return $this->getSingle($id);
    }

    /**
     * Updates a construction stage with the provided data.
     *
     * @param  ConstructionStagesUpdate  $data  The data object containing information to update the construction stage.
     * @param  int  $id  The ID of the construction stage to be updated.
     * @return array|null Returns an array containing information about the updated construction stage, or null if the record doesn't exist
     */
    public function patch(ConstructionStagesUpdate $data, int $id): ?array
    {
        $validator = (new UpdateConstructionStageValidator())->validate($data);

        if (! $validator->passed()) {
            http_response_code(422);

            return $validator->getErrors();
        }
        $data = $data->toArray();
        $originalStage = $this->getSingle($id);

        if (count($originalStage) == 0) {
            return null;
        }
        $stage = $originalStage[0];

        unset($stage['id'],$stage['duration']);

        $changes = array_diff_assoc($data, $stage);

        if (empty($changes)) {
            return $originalStage;
        }

        if ($this->hasChanges($changes, 'startDate', 'endDate', 'durationUnit')) {
            $durationUnit = array_key_exists('durationUnit', $data) ? $data['durationUnit'] : $stage['durationUnit'];
            $startDate = array_key_exists('startDate', $data) ? $data['startDate'] : $stage['startDate'];
            $endDate = array_key_exists('endDate', $data) ? $data['endDate'] : $stage['endDate'];
            $changes['duration'] = $this->calculateDuration($durationUnit, $startDate, $endDate);
        }

        $chngedColumnsStmt = array_map(function ($column) {
            if ($column == 'startDate' || $column == 'endDate') {
                return $this->camelCaseToSnakeCase($column)."=:$column";
            }

            return "$column=:$column";
        }, array_keys($changes));

        $stmt = $this->db->prepare('UPDATE construction_stages SET '.implode(',', $chngedColumnsStmt).' WHERE ID =:id ');

        $changedColumnsValueBinding = [];

        foreach ($changes as $column => $value) {
            $changedColumnsValueBinding[$column] = $changes[$column];
        }
        $changedColumnsValueBinding['id'] = $id;

        $stmt->execute($changedColumnsValueBinding);

        return $this->getSingle($id);
    }
}
