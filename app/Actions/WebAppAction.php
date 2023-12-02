<?php

namespace App\Actions;

class WebAppAction
{
    public function getNestedItems(&$category)
    {
        $allItems = [];
        foreach ($category->items as &$item){
            $allItems[] = $item;
        }
        foreach ($category->children as $child){
            foreach ($child->items as &$item){
                $allItems[] = $item;
            }
            foreach ($child->children as $subChild){
                foreach ($subChild->items as &$item){
                    $allItems[] = $item;
                }
            }
        }
        return $allItems;
    }
}
