<?php

class ElasticSearch extends DataSource
{

    /**
     * Description for this DataSource
     * @var string
     *
     */
    public $description = 'ElasticSearch';

    /**
     * The ElasticClient instance
     * @var object
     *
     */
    public $client = null;

    /**
     * The current connection status
     * @var boolean
     *
     */
    public $connected = false;

    /**
     * The default configuration
     * @var array
     *
     */
    public $baseConfig = [
        'hosts' => [],
    ];
}
