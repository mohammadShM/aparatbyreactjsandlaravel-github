<?php

use App\Tag;
use Illuminate\Database\Seeder;

class TagsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Tag::count()) {
            // remove seeder and create seeder fake data
            Tag::truncate();
        }
        $tags = ['عمومی', 'خبری', 'علم و تکنولوژی', 'ورزشی', 'بانوان', 'بازی', 'طنز', 'آموزشی', 'تفریحی',
            'فیلم', 'مذهبی', 'موسیقی', 'سیاسی', 'حوادث', 'گردشگری', 'حیوانات', 'متفرقه', 'تبلیغات', 'هنری'
            , 'کارتون', 'سلامت',];
        foreach ($tags as $tagName) {
            Tag::create(['title' => $tagName]);
        }
        $this->command->info('add this tags' . implode(' , ', $tags));
    }
}
