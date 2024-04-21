<?php

namespace ExpertShipping\Spl\Utilities;

class FilterBuilder
{
    protected $query;
    protected $request;
    protected $filters = [];
    protected $namespace = 'ExpertShipping\\Spl\\Utilities\\Filters\\';
    protected $relationship;

    public function __construct($query, $request, $relationship = null)
    {
        $this->query = $query;
        $this->request = $request;
        $this->relationship = $relationship;
    }

    public static function for($query, $request, $relationship = null)
    {
        return (new static($query, $request, $relationship))->apply();
    }

    public function apply()
    {
        $query = $this->query;
        if($this->relationship){
            if(!is_array($this->relationship) || (isset($this->relationship['type']) && $this->relationship['type'] == 'relation')) {
                $query->whereHas($this->relationship, function($query){
                    return $this->appFiltersForQuery($query);
                });
            }else{
                if(isset($this->relationship['type']) && $this->relationship['type'] == 'morph' && isset($this->relationship['relationship']) && isset($this->relationship['types'])) {
                    $relationships = explode('.', $this->relationship['relationship']);
                    if(count($relationships) != 2){
                        $query->whereHasMorph($this->relationship['relationship'], $this->relationship['types'], function($query){
                            return $this->appFiltersForQuery($query);
                        });
                    }else{
                        $firstRelationship = $relationships[0];
                        $secondRelationship = $relationships[1];

                        if(str_contains($firstRelationship, 'able')){
                            $query->whereHasMorph($firstRelationship, $this->relationship['types'], function($query) use ($secondRelationship){
                                $query->whereHas($secondRelationship, function($query){
                                    return $this->appFiltersForQuery($query);
                                });
                            });
                        }

                        if(str_contains($secondRelationship, 'able')){
                            $query->whereHas($firstRelationship, function($query) use ($secondRelationship){
                                $query->whereHasMorph($secondRelationship, $this->relationship['types'], function($query){
                                    return $this->appFiltersForQuery($query);
                                });
                            });
                        }
                    }

                }

                if(isset($this->relationship['type']) && $this->relationship['type'] == 'relation' && isset($this->relationship['relationship'])) {
                    $query->whereHas($this->relationship['relationship'], function($query){
                        return $this->appFiltersForQuery($query);
                    });
                }
            }
        }else{
            $query = $this->appFiltersForQuery($query);
        }

        return $query;
    }

    public function filters()
    {
        if(is_array($this->request)){
            return $this->request;
        }

        return $this->request->all();
    }

    private function appFiltersForQuery($query)
    {
        foreach ($this->filters() as $filter => $value) {
            if(is_null($value) || empty($value)){
                continue;
            }

            $class = $this->namespace . str($filter)->studly();

            if (class_exists($class)) {
                $query = $class::apply($query, $value);
            }
        }

        return $query;
    }

    private function getFunctionNameFromRelationship($relationship)
    {
        $isMorph = str_contains($relationship, 'able');

        return $isMorph ? 'whereHasMorph' : 'whereHas';
    }
}
