<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

/**
 * Class BatchFilterBag
 * Magento API filter container
 *
 * @package OroCRM:MagentoBundle
 */
class BatchFilterBag
{
    const FILTER_TYPE_SIMPLE  = 'filter';
    const FILTER_TYPE_COMPLEX = 'complex_filter';

    /** @var array applied filters */
    protected $filters;

    public function __construct(array $filters = [], array $complexFilters = [])
    {
        $this->reset();

        if (!empty($filters)) {
            foreach ($filters as $filterName => $filterValue) {
                $this->addFilter(
                    $filterName,
                    [
                        'key'   => $filterName,
                        'value' => $filterValue
                    ]
                );
            }
        }

        if (!empty($complexFilters)) {
            foreach ($complexFilters as $filterName => $filterValue) {
                $this->addComplexFilter(
                    $filterName,
                    [
                        'key'   => $filterName,
                        'value' => $filterValue
                    ]
                );
            }
        }
    }

    /**
     * Return applied configured fields
     *
     * @return array
     */
    public function getAppliedFilters()
    {
        $filters = [];

        if (!empty($this->filters[self::FILTER_TYPE_SIMPLE])) {
            $filters[self::FILTER_TYPE_SIMPLE] = array_values($this->filters[self::FILTER_TYPE_SIMPLE]);
        }
        if (!empty($this->filters[self::FILTER_TYPE_COMPLEX])) {
            $filters[self::FILTER_TYPE_COMPLEX] = array_values($this->filters[self::FILTER_TYPE_COMPLEX]);
        }

        return ['filters' => $this->fixFilters($filters)];
    }

    /**
     * Fixes issues with magento 1.7 where only one filter from these using the same key are used
     * @link https://magecore.atlassian.net/browse/CRM-4690
     *
     * @param array $filters
     */
    private function fixFilters(array $filters)
    {
        $fixedFilters = [];
        $usedKeys = [];

        foreach ($filters as $key => $filter) {
            if (is_array($filter)) {
                foreach ($filter as $subKey => $subFilter) {
                    if (isset($subFilter['key'])) {
                        $subFilter['key'] = $this->createUniqueKey($usedKeys, $subFilter['key']);
                        $usedKeys[] = $subFilter['key'];
                    }

                    $fixedFilters[$key][$subKey] = $subFilter;
                }
            } else {
                $fixedFilters[$key] = $filter;
            }
        }

        return $fixedFilters;
    }

    /**
     * @param array $usedKeys
     * @param string $key
     */
    private function createUniqueKey(array $usedKeys, $key)
    {
        $keyLength = strlen($key);
        $i = 0;
        while (in_array($key, $usedKeys) && $i < $keyLength) {
            $key[$i] = strtoupper($key[$i]);
            $i++;
        }

        return $key;
    }

    /**
     * @param int|\stdClass $lastId
     * @param string        $idFieldName
     *
     * @return $this
     */
    public function addLastIdFilter($lastId, $idFieldName = 'entity_id')
    {
        $this->addComplexFilter(
            'lastid',
            [
                'key'   => $idFieldName,
                'value' => [
                    'key'   => 'gt',
                    'value' => $lastId,
                ],
            ]
        );

        return $this;
    }

    /**
     * @param string    $dateField
     * @param string    $dateKey
     * @param \DateTime $date
     * @param string    $format
     *
     * @return $this
     */
    public function addDateFilter($dateField, $dateKey, \DateTime $date, $format = 'Y-m-d H:i:s')
    {
        $this->addComplexFilter(
            $dateField . '-' . $dateKey,
            [
                'key'   => $dateField,
                'value' => [
                    'key'   => $dateKey,
                    'value' => $date->format($format),
                ],
            ]
        );

        return $this;
    }

    /**
     * @param array $websiteIds
     *
     * @return $this
     */
    public function addWebsiteFilter(array $websiteIds)
    {
        $this->addComplexFilter(
            'website_id',
            [
                'key'   => 'website_id',
                'value' => [
                    'key'   => 'in',
                    'value' => implode(',', $websiteIds)
                ]
            ]
        );

        return $this;
    }

    /**
     * @param array $storeIds
     *
     * @return $this
     */
    public function addStoreFilter(array $storeIds)
    {
        $this->addComplexFilter(
            'store_id',
            [
                'key'   => 'store_id',
                'value' => [
                    'key'   => 'in',
                    'value' => implode(',', $storeIds)
                ]
            ]
        );

        return $this;
    }

    /**
     * @param string $name
     * @param array  $definition
     *
     * @return $this
     */
    public function addComplexFilter($name, $definition)
    {
        $this->addFilter($name, $definition, self::FILTER_TYPE_COMPLEX);

        return $this;
    }

    /**
     * @param string $name
     * @param array  $definition
     * @param string $filterType
     *
     * @return $this
     */
    public function addFilter($name, array $definition, $filterType = self::FILTER_TYPE_SIMPLE)
    {
        $filterType = in_array(
            $filterType,
            [self::FILTER_TYPE_SIMPLE, self::FILTER_TYPE_COMPLEX]
        ) ? $filterType : self::FILTER_TYPE_SIMPLE;

        $this->filters[$filterType][$name] = $definition;

        return $this;
    }

    /**
     * @param string $filterType
     * @param string $filterName
     *
     * @return $this
     */
    public function reset($filterType = null, $filterName = null)
    {
        if (is_null($filterType) && is_null($filterName)) {
            $this->filters = [
                self::FILTER_TYPE_COMPLEX => [],
                self::FILTER_TYPE_SIMPLE  => [],
            ];
        }

        if (isset($this->filters[$filterType]) && is_null($filterName)) {
            $this->filters[$filterType] = [];
        }

        if (isset($this->filters[$filterType][$filterName])) {
            unset($this->filters[$filterType][$filterName]);
        }

        return $this;
    }

    /**
     * Merge one instance of filter bag into current
     *
     * @param BatchFilterBag $bag
     */
    public function merge(BatchFilterBag $bag)
    {
        $appliedFilters = $bag->getAppliedFilters();
        $appliedFilters = array_pop($appliedFilters);
        if (!empty($appliedFilters[self::FILTER_TYPE_COMPLEX])) {
            foreach ($appliedFilters[self::FILTER_TYPE_COMPLEX] as $filterData) {
                $filterName = $filterData['key'];
                $this->addComplexFilter($filterName, $filterData);
            }
        }
        if (!empty($appliedFilters[self::FILTER_TYPE_SIMPLE])) {
            foreach ($appliedFilters[self::FILTER_TYPE_SIMPLE] as $filterData) {
                $filterName = $filterData['key'];
                $this->addFilter($filterName, $filterData);
            }
        }
    }
}
