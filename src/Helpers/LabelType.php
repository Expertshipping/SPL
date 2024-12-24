<?php

namespace ExpertShipping\Spl\Helpers;

class LabelType
{
    public static function labelType($request)
    {
        $labelType = 'all';
        if($request->to['country'] === $request->from['country'] && $request->from['country'] === (auth()->user()->company->country??auth()->user()->country)){
            $labelType = 'domestic';
        }

        if($request->to['country'] !== $request->from['country'] && $request->from['country'] === (auth()->user()->company->country??auth()->user()->country)){
            $labelType = 'export';
        }

        if($request->to['country'] !== $request->from['country'] && $request->to['country'] === (auth()->user()->company->country??auth()->user()->country)){
            $labelType = 'import';
        }

        return $labelType;
    }
}
