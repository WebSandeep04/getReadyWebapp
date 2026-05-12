<?php
$images = [
    'cat_women.png' => 'C:\Users\Ravi-TS360\.gemini\antigravity\brain\09932b64-c901-4f2d-84af-4abe247e751d\category_women_v2_closeup_1777719451040.png',
    'cat_girl.png' => 'C:\Users\Ravi-TS360\.gemini\antigravity\brain\09932b64-c901-4f2d-84af-4abe247e751d\category_girl_v2_closeup_1777719467804.png',
];

$destDir = __DIR__ . '/images';
if (!is_dir($destDir)) {
    mkdir($destDir, 0777, true);
}

foreach ($images as $name => $source) {
    if (file_exists($source)) {
        if (copy($source, $destDir . '/' . $name)) {
            echo "Successfully updated $name <br>";
        } else {
            echo "Failed to update $name <br>";
        }
    } else {
        echo "Source file not found for $name: $source <br>";
    }
}
?>
