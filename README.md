# cakephp2-elasticsearch

**"INSTALL":**

1 - Install via composer the package elasticsearch/elasticsearch and include in your bootstrap.php file

2 - Include to your database.php config file the public var $$elasticsearch and set the addres(es) / port(s) to your elastic database

3 - Copy files /Controler/Component/ElasticSearchComponent.php, /View/Helper/ElasticPaginatorHelper.php and /Model/Datasource/ElasticSearchṗhp to your
    /Controler/Component, /View/Helper folders and /Model/Datasource/ 
    
**USING IN YOUR CONTROLLER**

1 - Include the component:
```
public $components = [
    'ElasticSearch',
];
```

2 - Create the query base:
```
$query = [
    'index' => 'your-index-here',
    'type' => 'your-type-here',
    'scroll' => '1m',
];
```

3 - Use $query and params to create your query as follow in any order / combination and then pass the query to Elasticsearch\ClientBuilder with $elasticSearch->search($query);

**PAGINATE**
```
$query = $this->ElasticSearch->paginate($query, $page);
```
query : your current query (array),

page : actual page number (int).

**SORT**
```
$query = $this->ElasticSearch->sort($query, $field, $order, $scoreFirst);
```
query : your current query (array),

field : field to order by (str),

order : direction: 'asc' or 'desc' (str),

scoreFirst: if true the score order will be the first one, the field order the second one: true or false (bool). 


**MATCH**
```
$query = $this->ElasticSearch->match($query, $conditions);
```
query : your current query (array),

conditions: array with conditions where key = field name and value = filter to match (array)
```
    $condition = [
        'name' => 'John',
        'id' => '23'
    ];
```

**NOT MATCH**
```
$query = $this->ElasticSearch->notMatch($query, $conditionsNot);
```
query : your current query (array),

conditionsNot: array with conditions to deny where key = field name and value = filter to match (array)
```
    $condition = [
        'gender' => 'm',
        'type' => '2'
    ];
```

**MATCH OR**
```
$query = $this->ElasticSearch->matchOr($query, $conditionsOr);
```
query : your current query (array),

conditionsOr: array with conditions where key = field name and value = filter to match (array)
```
    $condition = [
        'country' => 'US',
        'country' => 'BR'
    ];
```

**SEARCH WITH WILDCARDS**
```
$query = $this->ElasticSearch->wildcard($query, $search);
```
query : your current query (array),

search: array with conditions where key = field name and value with wildcards = filter to find (array)
```
    $condition = [
        'ip' => '*192.*',
        'mac' => '??:*:36:FF'
    ];
```

**RANGE**
```    
$query = $this->ElasticSearch->range($query, $range);
```
query : your current query (array),

search: array with conditions where key = field name as array and values 'start' and 'end' with ranges
```
    $range = [
        'date' => [
            'start' = '2016-03-02 00:00:00',
            'end' = '2016-03-03 00:00:00',
        ]
    ];
```

**SUM**
```
$query = $this->ElasticSearch->sum($query, $sums);
```
query : your current query (array),

search: array with names and fields to create sums where key = name sum and value  = field (array)
```
    $sums = [
        'total' => 'value',
        'sum_upload' => 'upload'
    ];
```
    
**EXAMPLE IN CONTROLLER**
```
<?php

public $components = [
    'Paginator',
    'ElasticSearch',
];

function action()
{

    $page = $this->request->params['named']['page'] ?? 1;
    $field = $this->request->params['named']['sort'] ?? 'date';
    $order = $this->request->params['named']['direction'] ?? 'desc';

    $query = [
        'index' => 'your-index-here',
        'type' => 'your-type-here',
        'scroll' => '1m',
    ];
    $conditions = [
        'name' => 'John',
        'client_id' => '13',
    ];
    $conditionsOr = [
        'country' => 'us',
        'type' => '13',
    ];
    $conditionsNot = [
        'gender' => 'f',
    ];
    $search = [
        'ip' => '*192.168*',
    ];
    $range = [
        'date' => [
            'start' = '2016-03-02 00:00:00',
            'end' = '2016-03-03 00:00:00',
        ]
    ];
    $sums = [
        'total' => 'value',
        'sum_upload' => 'upload',
    ];
    
    $query = $this->ElasticSearch->paginate($query, $page);
    $query = $this->ElasticSearch->sort($query, $field, $order, false);
    $query = $this->ElasticSearch->match($query, $conditions);
    $query = $this->ElasticSearch->matchOr($query, $conditionsOr);
    $query = $this->ElasticSearch->notMatch($query, $conditionsNot);
    $query = $this->ElasticSearch->wildcard($query, $search);
    $query = $this->ElasticSearch->range($query, $ranges);
    $query = $this->ElasticSearch->sum($query, $sums);
    
    $dataSource = ConnectionManager::getDataSource('elasticsearch');
    $hosts = $dataSource->config['hosts'];
    $client = Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
    $return = $client->search($query);
    
    $result = $this->ElasticSearch->dealResponse('Person', $return, $page);
    
    
}
?>
```

**USING PAGINATION IN YOUR VIEW**

1 - Include the helper to your controller
```
public $helpers = [
    'ElasticPaginator',
];
```

2 - Call the paginator, where $person[params] are the result generated in controller with dealResponse
```
<?php
    echo $this->ElasticPaginator->numbers([
        'total'=> $person['total'],
        'page'=> $person['page'],
        'pageCount'=> $person['pages'],
        'modulus' => 7,
        'first' => '«',
        'last' => '»',
        'tag' => 'li',
        'separator' => '',
        'currentClass' => 'current',
        'currentTag' => 'a',
        'escape' => false,
    ]);
?>
```
