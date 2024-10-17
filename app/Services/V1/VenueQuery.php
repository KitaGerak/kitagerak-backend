<?php

namespace App\Services\V1;

use Illuminate\Http\Request;

class VenueQuery {
    protected $safeParms = [
        'status' => ['eq'],
        'price' => ['eq', 'gte', 'lte', 'gt', 'lt', 'in'],
        'courtSize' => ['eq', 'gte', 'lte', 'gt', 'lt'],
        'courtType' => ['eq'],
        'floorType' => ['eq'],
        'addressId' => ['eq'],
        'city' => ['eq'],
        'province' => ['eq'],
        'postalCode' => ['eq'],
        'longitude' => ['eq'],
        'latitude' => ['eq']
    ];

    protected $columnMap = [
        'status' => 'venues.status',
        'price' => 'courts.regular_price',
        'courtSize' => 'courts.size',
        'courtType' => 'court_types.type',
        'floorType' => 'courts.floor_type',
        'addressId' => 'venues.address_id',
        'city' => 'addresses.city',
        'province' => 'addresses.province',
        'postalCode' => 'addresses.postal_code',
        'longitude' => 'addresses.longitude',
        'latitude' => 'addresses.latitude'
    ];

    protected $operatorMap = [
        'eq' => '=',
        'lt' => '<',
        'gt' => '>',
        'lte' => '<=',
        'gte' => '>=',
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
                    $eloQuery[] = [$column, $this->operatorMap[$operator], $query[$operator]];
                }
            }
        }

        return $eloQuery;
    }
}