<?php
$images = [
    'cat_men.png' => 'C:\Users\Ravi-TS360\.gemini\antigravity\brain\09932b64-c901-4f2d-84af-4abe247e751d\category_men_1777716234159.png',
    'cat_women.png' => 'C:\Users\Ravi-TS360\.gemini\antigravity\brain\09932b64-c901-4f2d-84af-4abe247e751d\category_women_1777716249516.png',
    'cat_boy.png' => 'C:\Users\Ravi-TS360\.gemini\antigravity\brain\09932b64-c901-4f2d-84af-4abe247e751d\category_boy_1777716265718.png',
    'cat_girl.png' => 'C:\Users\Ravi-TS360\.gemini\antigravity\brain\09932b64-c901-4f2d-84af-4abe247e751d\category_girl_1777716280746.png',
];

$destDir = __DIR__ . '/public/images';
if (!is_dir($destDir)) {
    mkdir($destDir, 0777, true);
}

foreach ($images as $name => $source) {
    if (file_exists($source)) {
        if (copy($source, $destDir . '/' . $name)) {
            echo "Successfully copied $name <br>";
        } else {
            echo "Failed to copy $name <br>";
        }
    } else {
        echo "Source file not found for $name: $source <br>";
    }
}
?>
