<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{InventoryCategory, InventoryItem};

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $detergent = InventoryCategory::create(['name'=>'Detergent','color'=>'Blue']);
        $fabcon    = InventoryCategory::create(['name'=>'Fabric Conditioner','color'=>'Pink']);
        $bleach    = InventoryCategory::create(['name'=>'Bleach','color'=>'Teal']);
        $packaging = InventoryCategory::create(['name'=>'Packaging','color'=>'Amber']);
        $fuel      = InventoryCategory::create(['name'=>'Fuel','color'=>'Red']);
        $cleaning  = InventoryCategory::create(['name'=>'Cleaning Supplies','color'=>'Green']);

        $defaultItems = [
            ['catId'=>$detergent->id,'name'=>'Ariel Liquid','brand'=>'Ariel','supply_type'=>'bulk','purchase_unit'=>'Dozen','units_per_purchase'=>12,'unit_label'=>'bottle','default_cost'=>120,'selling_price'=>12,'reorder_point'=>24,'max_level'=>240],
            ['catId'=>$detergent->id,'name'=>'Ariel Powder','brand'=>'Ariel','supply_type'=>'bulk','purchase_unit'=>'Dozen','units_per_purchase'=>12,'unit_label'=>'sachet','default_cost'=>95,'selling_price'=>10,'reorder_point'=>24,'max_level'=>240],
            ['catId'=>$detergent->id,'name'=>'Breeze Powder','brand'=>'Breeze','supply_type'=>'bulk','purchase_unit'=>'Dozen','units_per_purchase'=>12,'unit_label'=>'sachet','default_cost'=>85,'selling_price'=>9,'reorder_point'=>24,'max_level'=>240],
            ['catId'=>$fabcon->id,'name'=>'Downy Regular','brand'=>'Downy','supply_type'=>'bulk','purchase_unit'=>'Box','units_per_purchase'=>100,'unit_label'=>'sachet','default_cost'=>350,'selling_price'=>5,'reorder_point'=>100,'max_level'=>1000],
            ['catId'=>$fabcon->id,'name'=>'Downy Sensitive','brand'=>'Downy','supply_type'=>'bulk','purchase_unit'=>'Box','units_per_purchase'=>100,'unit_label'=>'sachet','default_cost'=>380,'selling_price'=>6,'reorder_point'=>100,'max_level'=>1000],
            ['catId'=>$fabcon->id,'name'=>'Comfort','brand'=>'Comfort','supply_type'=>'bulk','purchase_unit'=>'Box','units_per_purchase'=>100,'unit_label'=>'sachet','default_cost'=>300,'selling_price'=>4,'reorder_point'=>100,'max_level'=>1000],
            ['catId'=>$bleach->id,'name'=>'Zonrox Regular','brand'=>'Zonrox','supply_type'=>'bulk','purchase_unit'=>'Piece','units_per_purchase'=>1,'unit_label'=>'bottle','default_cost'=>28,'selling_price'=>30,'reorder_point'=>10,'max_level'=>50],
            ['catId'=>$bleach->id,'name'=>'Zonrox Color Safe','brand'=>'Zonrox','supply_type'=>'bulk','purchase_unit'=>'Piece','units_per_purchase'=>1,'unit_label'=>'bottle','default_cost'=>32,'selling_price'=>35,'reorder_point'=>10,'max_level'=>50],
            ['catId'=>$packaging->id,'name'=>'Plastic Bags (Small)','brand'=>'Generic','supply_type'=>'direct','purchase_unit'=>'Pack','units_per_purchase'=>100,'unit_label'=>'piece','default_cost'=>50,'selling_price'=>1,'reorder_point'=>100,'max_level'=>1000],
            ['catId'=>$packaging->id,'name'=>'Scotch Tape','brand'=>'Generic','supply_type'=>'direct','purchase_unit'=>'Box','units_per_purchase'=>12,'unit_label'=>'roll','default_cost'=>120,'selling_price'=>15,'reorder_point'=>12,'max_level'=>60],
            ['catId'=>$fuel->id,'name'=>'LPG','brand'=>'Gasul','supply_type'=>'direct','purchase_unit'=>'Tank','units_per_purchase'=>1,'unit_label'=>'tank','default_cost'=>6200,'selling_price'=>0,'reorder_point'=>1,'max_level'=>5],
        ];

        foreach ($defaultItems as $row) {
            InventoryItem::create([
                'category_id'           => $row['catId'],
                'name'                  => $row['name'],
                'brand'                 => $row['brand'],
                'supply_type'           => $row['supply_type'],
                'purchase_unit'         => $row['purchase_unit'],
                'units_per_purchase'    => $row['units_per_purchase'],
                'unit_label'            => $row['unit_label'],
                'default_cost'          => $row['default_cost'],
                'selling_price'         => $row['selling_price'],
                'reorder_point'         => $row['reorder_point'],
                'max_level'             => $row['max_level'],
                'is_active'             => true,
            ]);
        }
    }
}
