<?php

namespace App\Services\v1;

use Illuminate\Http\Request;

class ScheduleQuery {
    protected $safeParms = [
        'courtId' => ['eq'],
        'availability' => ['eq'],
        'date' => ['eq', 'lt', 'lte', 'gt', 'gte'],
        'timeStart' => ['eq', 'lt', 'lte', 'gt', 'gte'],
        'timeFinish' => ['eq', 'lt', 'lte', 'gt', 'gte']
    ];

    protected $columnMap = [
        'courtId' => 'schedules.court_id',
        'availability' => 'schedules.availability',
        'date' => 'schedules.date',
        'timeStart' => 'schedules.time_start',
        'timeFinish' => 'schedules.time_finish'
    ];

    protected $operatorMap = [
        'eq' => '=',
        'lt' => '<',
        'lte' => '<=',
        'gt' => '>',
        'gte' => '>=',
        'like' => 'like',
        'in' => 'in'
    ];

    public function transform(Request $request) {
        $eloQuery = [];

        foreach ($this->safeParms as $parm => $operators) {
            $query = $request->query($parm);

            if (!isset($query)) {
                continue;
            }

            $column = $this->columnMap[$parm] ?? $parm;

            foreach ($operators as $operator) {
                if (isset($query[$operator])) {
                    if ($operator == 'like') {
                        $eloQuery[] = [$column, $this->operatorMap[$operator], '%' . $query[$operator] . '%'];
                    } else {
                        $eloQuery[] = [$column, $this->operatorMap[$operator], $query[$operator]];
                    }
                }
            }
        }

        return $eloQuery;
    }
}