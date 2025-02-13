<?php

namespace App\Services\V1;

use Illuminate\Http\Request;

class CourtQuery {
    protected $safeParms = [
        'name' => ['eq', 'like'],
        'description' => ['eq', 'like'],
        'venueId' => ['eq'],
        'floorType' => ['eq', 'like'],
        'courtTypeId' => ['eq'],
        'regularPrice' => ['eq', 'gte', 'gt', 'lte', 'lt', 'in'],
        'size' => ['eq', 'gte', 'gt', 'lte', 'lt', 'in'],
        'rating' => ['eq', 'gte', 'gt', 'lte', 'lt', 'in'],
    ];

    protected $columnMap = [
        'name' => 'courts.name',
        'description' => 'courts.description',
        'venueId' => 'courts.venue_id',
        'floorType' => 'courts.floor_type',
        'courtTypeId' => 'courts.court_type_id',
        'regularPrice' => 'courts.regular_price',
        'memberPrice' => 'courts.member_price',
        'size' => 'courts.size',
        'rating' => 'courts.rating',
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