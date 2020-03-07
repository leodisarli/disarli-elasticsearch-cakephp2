<?php
App::uses('AppHelper', 'View/Helper');

class ElasticPaginatorHelper extends AppHelper
{
    protected $pageLimit = 399;

    public $helpers = ['Html'];

    public function link($title, $url = [], $options = [])
    {
        $field = $this->request->params['named']['sort'] ?? null;
        $order = $this->request->params['named']['direction'] ?? null;

        $options += ['model' => null, 'escape' => true];
        $model = $options['model'];
        unset($options['model']);

        if (!empty($this->options)) {
            $options += $this->options;
        }
        if (isset($options['url'])) {
            $url = array_merge((array) $options['url'], (array) $url);
            unset($options['url']);
        }
        unset($options['convertKeys']);

        $url = $this->url($url, true, $model);

        if (!empty($field) && !empty($order)) {
            $url = $url .'/sort:'.$field.'/direction:'.$order;
        }

        $obj = isset($options['update']) ? $this->_ajaxHelperClass : 'Html';
        return $this->{$obj}->link($title, $url, $options);
    }

    public function first($first = '<< first', $options = [], $page = 1, $pageCount = 1)
    {
        $options = (array) $options + [
            'tag' => 'span',
            'after' => null,
            'separator' => ' | ',
            'ellipsis' => '...',
            'class' => null,
        ];

        if ($pageCount <= 1) {
            return '';
        }
        extract($options);
        unset($options['tag'], $options['after'], $options['separator'], $options['ellipsis'], $options['class']);

        $out = '';

        if ((is_int($first) || ctype_digit($first)) && $page >= $first) {
            if ($after === null) {
                $after = $ellipsis;
            }
            for ($i = 1; $i <= $first; $i++) {
                $out .= $this->Html->tag($tag, $this->link($i, ['page' => $i], $options), compact('class'));
                if ($i != $first) {
                    $out .= $separator;
                }
            }
            $out .= $after;
        } elseif ($page > 1 && is_string($first)) {
            $options += ['rel' => 'first'];
            $out = $this->Html->tag($tag, $this->link($first, ['page' => 1], $options), compact('class')) . $after;
        }
        return $out;
    }

    public function last($last = 'last >>', $options = [], $page = 1, $pageCount = 1)
    {
        $options = (array) $options + [
            'tag' => 'span',
            'before' => null,
            'separator' => ' | ',
            'ellipsis' => '...',
            'class' => null
        ];

        if ($pageCount <= 1) {
            return '';
        }

        extract($options);
        unset($options['tag'], $options['before'], $options['separator'], $options['ellipsis'], $options['class']);

        $out = '';
        $lower = $pageCount - $last + 1;

        if ((is_int($last) || ctype_digit($last)) && $page <= $lower) {
            if ($before === null) {
                $before = $ellipsis;
            }
            for ($i = $lower; $i <= $pageCount; $i++) {
                $out .= $this->Html->tag($tag, $this->link($i, ['page' => $i], $options), compact('class'));
                if ($i != $pageCount) {
                    $out .= $separator;
                }
            }
            $out = $before . $out;
        } elseif ($page < $pageCount && is_string($last)) {
            $options += ['rel' => 'last'];
            $out = $before . $this->Html->tag(
                $tag,
                $this->link($last, ['page' => $pageCount], $options),
                compact('class')
            );
        }
        return $out;
    }

    public function numbers($options = [])
    {
        if ($options === true) {
            $options = [
                'before' => ' | ',
                'after' => ' | ',
                'first' => 'first',
                'last' => 'last',
            ];
        }

        $defaults = [
            'tag' => 'span',
            'before' => null,
            'after' => null,
            'class' => null,
            'modulus' => '8',
            'separator' => ' | ',
            'first' => null,
            'last' => null,
            'ellipsis' => '...',
            'currentClass' => 'current',
            'currentTag' => null,
            'useLimit' => false,
        ];
        $options += $defaults;

        $params = [];

        $params['page'] = $options['page'];
        $params['pageCount'] = $options['pageCount'];

        if (empty($params['pageCount']) || $params['pageCount'] <= 1) {
            return '';
        }

        extract($options);
        unset(
            $options['tag'],
            $options['before'],
            $options['after'],
            $options['model'],
            $options['modulus'],
            $options['separator'],
            $options['first'],
            $options['last'],
            $options['ellipsis'],
            $options['class'],
            $options['currentClass'],
            $options['currentTag']
        );
        $out = '';

        if ($useLimit && $params['pageCount']>$this->pageLimit) {
            $params['pageCount'] = $this->pageLimit;
        }

        if ($modulus && $params['pageCount'] > $modulus) {
            $half = (int) ($modulus / 2);
            $end = $params['page'] + $half;

            if ($end > $params['pageCount']) {
                $end = $params['pageCount'];
            }
            $start = $params['page'] - ($modulus - ($end - $params['page']));
            if ($start <= 1) {
                $start = 1;
                $end = $params['page'] + ($modulus - $params['page']) + 1;
            }

            $firstPage = is_int($first) ? $first : 0;
            if ($first && $start > 1) {
                $offset = ($start <= $firstPage) ? $start - 1 : $first;
                if ($firstPage < $start - 1) {
                    $out .= $this->first($offset, compact('tag', 'separator', 'ellipsis', 'class'), $params['page'], $params['pageCount']);
                } else {
                    $out .= $this->first(
                        $offset,
                        compact('tag', 'separator', 'class', 'ellipsis') + ['after' => $separator],
                        $params['page'],
                        $params['pageCount']
                    );
                }
            }

            $out .= $before;

            for ($i = $start; $i < $params['page']; $i++) {
                $out .= $this->Html->tag($tag, $this->link($i, ['page' => $i], $options), compact('class')) . $separator;
            }

            if ($class) {
                $currentClass .= ' ' . $class;
            }
            if ($currentTag) {
                $out .= $this->Html->tag($tag, $this->Html->tag($currentTag, $params['page']), ['class' => $currentClass]);
            } else {
                $out .= $this->Html->tag($tag, $params['page'], ['class' => $currentClass]);
            }
            if ($i != $params['pageCount']) {
                $out .= $separator;
            }

            $start = $params['page'] + 1;
            for ($i = $start; $i < $end; $i++) {
                $out .= $this->Html->tag($tag, $this->link($i, ['page' => $i], $options), compact('class')) . $separator;
            }

            if ($end != $params['page']) {
                $out .= $this->Html->tag($tag, $this->link($i, ['page' => $end], $options), compact('class'));
            }

            $out .= $after;

            if ($last && $end < $params['pageCount']) {
                $lastPage = is_int($last) ? $last : 0;
                $offset = ($params['pageCount'] < $end + $lastPage) ? $params['pageCount'] - $end : $last;
                if ($offset <= $lastPage && $params['pageCount'] - $end > $lastPage) {
                    $out .= $this->last($offset, compact('tag', 'separator', 'ellipsis', 'class'), $params['page'], $params['pageCount']);
                } else {
                    $out .= $this->last(
                        $offset,
                        compact('tag', 'separator', 'class', 'ellipsis') + ['before' => $separator],
                        $params['page'],
                        $params['pageCount']
                    );
                }
            }
        } else {
            $out .= $before;

            for ($i = 1; $i <= $params['pageCount']; $i++) {
                if ($i == $params['page']) {
                    if ($class) {
                        $currentClass .= ' ' . $class;
                    }
                    if ($currentTag) {
                        $out .= $this->Html->tag($tag, $this->Html->tag($currentTag, $i), ['class' => $currentClass]);
                    } else {
                        $out .= $this->Html->tag($tag, $i, ['class' => $currentClass]);
                    }
                } else {
                    $out .= $this->Html->tag($tag, $this->link($i, ['page' => $i], $options), compact('class'));
                }
                if ($i != $params['pageCount']) {
                    $out .= $separator;
                }
            }

            $out .= $after;
        }

        return $out;
    }
}
