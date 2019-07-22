<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file = new SplFileObject('database/seeds/productsSeed.csv');
        $file->setFlags(
            \SplFileObject::READ_CSV |
            \SplFileObject::READ_AHEAD |
            \SplFileObject::SKIP_EMPTY |
            \SplFileObject::DROP_NEW_LINE
        );
        $list = [];
        $now = Carbon::now();
        foreach($file as $line) {
            $list[] = [
                "image" => $line[0],
                "name" => $line[1],
                "desc" => $line[2],
                "value" => $line[3],
                "created_at" => $now,
                "updated_at" => $now,
            ];
        }

        DB::table("products")->insert($list);
    }
}
