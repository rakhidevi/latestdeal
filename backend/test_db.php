<?php
try {
    \App\Models\Deal::updateOrCreate(
        ['url'=>'https://test.com'],
        [
            'category_id'=>1,
            'merchant_id'=>1,
            'title'=>'test',
            'original_price'=>100,
            'discounted_price'=>50,
            'image_path'=>'deals/test.jpg',
            'status'=>'active'
        ]
    );
    echo "Success!";
} catch (\Exception $e) {
    echo $e->getMessage();
}
