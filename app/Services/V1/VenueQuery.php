<?php

namespace App\Services\V1;

use Illuminate\Http\Request;

class VenueQuery {
    protected $safeParms = [
        'price' => ['eq', 'gte', 'lte'],
        'courtSize' => ['eq', 'gte', 'lte'],
        'courtType' => ['eq'],
        'floorType' => ['eq']
    ];

    protected $columnMap = [
        'price' => 'courts.price',
        'courtSize' => 'courts.size',
        'courtType' => 'court_types.type',
        'floorType' => 'courts.floor_type'
    ];

    protected $operatorMap = [
        'eq' => '=',
        'lt' => '<',
        'gt' => '>',
        'lte' => '<=',
        'gte' => '>='
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
                    $eloQuery[] = [$column, $this->operatorMap[$operator], $query[$operator]];
                }
            }
        }

        return $eloQuery;
    }
}