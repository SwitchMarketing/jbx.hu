<?php namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\BaseBuilder as Builder;

class BaseModel extends Model
{

    protected $DBGroup = 'shop';
        
    /**
     * displayFields
     *
     * @var array
     */
    protected $displayFields    = [];
    
    /**
     * extraFields
     *
     * @var array
     */
    protected $extraFields = [];

        
    /**
     * filters
     *
     * @var array
     */
    protected $filters  = [];
        
    /**
     * sorters
     *
     * @var array
     */
    protected $sorters  = [];
        
    
    /**
     * keys
     *
     * @var array
     */
    protected $keys = [];

    /**
     * 
     * ezekben a oszlopokban szűr a szabadszavas kereső
     * 
     * @var array
     */
    protected $qstringColumns = [];
        

    public function initialize() {
        $this->_setDefaultFields();
    }

    /**
     * @param array $args
     * 
     * @return object
     */
    public function findAll(?int $limit = null, int $offset = 0):object
    {

        $builder = $this->builder();

        $this->_setSelect($builder);        
        $this->_applyFilters($this->filters, $builder);
        $this->_applySorters($this->sorters, $builder);

        $queryResult = parent::findAll($limit, $offset);

        // a teljes lekérdezésből kinyerjük a where részt
        // hogy a teljes találati számot is meg tudjuk adni
        $lastQuery = $this->db->getLastQuery()->getQuery();

        //echo $lastQuery;

        $where = '';
        // Word boundaries prevent matching SQL keyword substrings inside values
        // (e.g. 'backorder' would truncate at 'back' without \b).
        if (preg_match('/\bWHERE\b(.*?)(?:\bORDER\b|\bLIMIT\b|$)/is', $lastQuery, $m)) {
            $where = $m[1];
        }

        $result = (object) [
            'total' => ($where) ? $builder->where($where)->countAllResults() : $builder->countAllResults(),
            'data'  => $queryResult
        ];

        return $result;
    }

    /**
     * setFields
     *
     * @param  mixed $data
     * @return array
     */
    public function setFields(array $data):array 
    {
        
        if(!(count($data)) > 0)
            return $this->_setDefaultFields();

        $this->keys = $data;

        $this->displayFields = array_filter($this->_setDefaultFields(), function($k) {
            return in_array($k, $this->keys);
        }, ARRAY_FILTER_USE_KEY);
        
        return $this->displayFields;
    }

    /**
     * setExtraFields
     *
     * @param  array $data
     * @return array
     */
    public function setExtraFields(array $data):array 
    {        
        $this->extraFields = $data;
        return $this->extraFields;
    }


    /**
     * setFilters
     *
     * @param  mixed $filters
     * @return void
     */
    public function setFilters(array $filters = []) {

        if(is_array($filters) && count($filters)) 
            $this->filters = $filters;

        return $this->filters;
    }

        
    /**
     * clearFilters
     *
     * @return void
     */
    public function clearFilters()
    {
        $this->filters = [];
        return $this->filters;
    }

    /**
     * setSorters
     *
     * @param  mixed $sorters
     * @return void
     */
    public function setSorters(array $sorters = []) {

        if(count($sorters)) 
            $this->sorters = $sorters;

        return $this->sorters;
    }    
    
    /**
     * setQstringColumns
     *
     * @param  mixed $columns
     * @return array
     */
    public function setQstringColumns(array $columns = []) { 
        $this->qstringColumns = $columns;
        return $this->qstringColumns;
    }        

    /**
     * _setSelect
     *
     * @param  mixed $builder
     * @return Builder
     */
    protected function _setSelect(Builder $builder):Builder
    {
         //columns
         $columns = $this->displayFields ?? $this->table.'.*';

         $columns = implode(',', array_map(
             function ($v, $k) {
                 return $v.' AS '.$k;
             },
             $columns,
             array_keys($columns)
         ));         
         
         $builder->select($columns, false);
         
         return $builder;
    }

    /**
     * _setDefaultFields
     * 
     * set default display fields
     *
     * @return array
     */
    protected function _setDefaultFields():array {

        $table = $this->db->DBPrefix . $this->table;

        $fields = $this->db->getFieldNames($table);

        foreach ($fields as $field_name)
            $this->displayFields[$field_name] = $table.'.'.$field_name;
        
        if(count($this->extraFields))
            $this->displayFields = array_merge($this->displayFields, $this->extraFields);        

        return $this->displayFields;
    }

     /**
     * _applyFilters
     *
     * @param  array $filter
     * @param  Builder $qb
     * @return Builder
     */
    protected function _applyFilters(array $filter = [], Builder $qb = null):Builder {

        if(count($filter)) 
        {
        
            $where = [];

            foreach($filter as $f)
            {

                if (method_exists($this, '_applyCustomFilter') && $this->_applyCustomFilter($qb, $f)) {
                    continue;
                }

                if(isset($f->expression) && strlen($f->expression) > 0)
                {
                    $qb->where($f->expression, null, false);
                }
                else if(isset($f->property) && $f->value !== '')
                {
                    $f->value = trim($f->value);
                    $f->value = mb_strtolower($f->value,'UTF-8');

                   if(isset($f->operator))
                    {
                        switch ($f->operator) {
                            case 'eq':
                                $where[$f->property.' ='] = $f->value;
                                break;
                            case 'neq':
                                $where[$f->property.' !='] = $f->value;
                                break;
                            case 'lt':
                                $where[$f->property.' <='] = $f->value;
                                break;
                            case 'gt':
                                $where[$f->property.' >='] = $f->value;
                                break;
                            case 'in':
                                $qb->whereIn($f->property, explode(',',$f->value));
                                break;
                            default :
                                $where[$f->property] = $f->value;
                        }
                    }
                    else
                    {
                        if($f->value == 'null')
                        {
                            $qb->where(''.$f->property.' IS NULL');
                        }
                        else
                        {
                            $qstring = explode(' ',$f->value);
                            if($f->property == 'qstring' && count($this->qstringColumns)) {
                                foreach($qstring as $qs) {
                                    $qb->groupStart();
                                    foreach($this->qstringColumns as $c) {
                                        $qb->orLike($c, $qs);
                                    }
                                    $qb->groupEnd();
                                }
                            }
                            else
                                foreach($qstring as $qs)
                                    $qb->like($f->property, $qs);
                        }
                    }       
                }
                
            }

            if(count($where))
               $qb->where($where);
        }

        return $qb;
    }

    /**
     * _applySorters
     *
     * @param  mixed $sorter
     * @param  Builder $qb
     * @return Builder
     */
    protected function _applySorters(array $sorter = [], Builder $qb = null):Builder {

        if(count($sorter)) 
        {
            foreach($sorter as $s) {
                $qb->orderBy($s->property, $s->direction); 
            }
        }

        return $qb;
    }
}