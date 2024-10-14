<?php

namespace App\Services\V1;

use Illuminate\Http\Request;

class TransactionQuery {
    protected $safeParms = [
        'transactionStatus' => ['eq'],
        'userId' => ['eq'],
        'ownerId' => ['eq'],
        'courtId' => ['eq'],
    ];

    protected $columnMap = [
        'transactionStatus' => 'ts.status',
        'userId' => 'u1.id',
        'ownerId' => 'u2.id',
        'courtId' => 'c.id'
        // 'courtId' => 'courts.id'
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