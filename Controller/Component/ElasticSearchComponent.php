<?php
class ElasticSearchComponent extends Component
{
    public $limit = 25;

    private function calculateFrom($page)
    {
        if ($page == 1) {
            $from = 0;
        } else {
            $from = $page * $this->limit - 1;
        }

        return $from;
    }

    private function calculatePages($total)
    {
        if (empty($total)) {
            $pages = 0;
        } else {
            $pages = round($total/$this->limit);
        }

        return $pages;
    }

    public function paginate($query, $page)
    {
        $query['from'] = $this->calculateFrom($page);
        $query['size'] = $this->limit;

        return $query;
    }

    public function match($query, $conditions)
    {
        foreach ($conditions as $field => $condition) {
            $query['body']['query']['bool']['must'][]['match'] = [$field => $condition];
        }

        return $query;
    }

    public function matchOr($query, $conditions)
    {
        foreach ($conditions as $field => $condition) {
            $query['body']['query']['bool']['should'][]['match'] = [$field => $condition];
            $query['body']['query']['bool']['minimum_should_match'] = 1;
        }

        return $query;
    }

    public function notMatch($query, $conditionsNot = [])
    {
        foreach ($conditionsNot as $field => $conditions) {
            $query['body']['query']['bool']['must_not'][]['match'] = [$field => $condition];
        }

        return $query;
    }

    public function range($query, $ranges)
    {
        foreach ($ranges as $field => $range) {
            $query['body']['query']['bool']['must'][]['range'] = [$field =>
                [
                    'gte' => $range['start'],
                    'lte' => $range['end'],
                ]
            ];
        }

        return $query;
    }

    public function wildcard($query, $conditions)
    {
        foreach ($conditions as $field => $condition) {
            $query['body']['query']['bool']['must'][]['wildcard'] = [$field => $condition];
        }

        return $query;
    }

    public function sort($query, $field, $order, $scoreFirst = false)
    {
        if ($scoreFirst) {
            $query['body']['sort'] = [
                "_score" => ['order' => 'desc'],
                $field => [ 'order' => $order ],
            ];
        } else {
            $query['body']['sort'] = [
                $field => [ 'order' => $order ],
            ];
        }

        return $query;
    }

    public function sum($query, $sums)
    {
        foreach ($sums as $name => $field) {
            $query['body']['aggs'][$name] = ['sum' => ['field' => $field]];
        }

        return $query;
    }

    public function dealResponse($model, $data, $currentPage = 1)
    {
        $result = [];
        $result['total'] = $data['hits']['total'] ?? 0;
        $result['page'] = $currentPage;
        $result['pages'] = $this->calculatePages($result['total']);

        if (isset($data['hits']['hits']) && !empty($data['hits']['hits'])) {
            foreach ($data['hits']['hits'] as $dataResult) {
                $dataResult['_source']['id'] = $dataResult['_id'];
                $result['data'][][$model] = $dataResult['_source'];
            }
        } else {
            $result['data'] = [];
        }

        if (isset($data['aggregations']) && !empty($data['aggregations'])) {
            foreach ($data['aggregations'] as $name => $agg) {
                $result['sums'][$name] = $agg['value'];
            }
        }

        return $result;
    }
}
