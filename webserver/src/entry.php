<?php

    $data = array(
        "forms"=> array("بردو" ,"برضو", "برضه", "برده"),
        "meanings"=> array("أيضًا", "كذلك"),
        "origin"=> "التركية",
        "original"=> "Bir de",
        "categories"=> array("العامية"),
        "examples"=> array("أنا مش هقدر أخلص الشغل بسرعة، بس أنت برضه حاول تساعدني", "الكتب دي جديدة، وبرضه عندي كتب قديمة إذا عايز"),
        "references"=> array("معجم الكلمات الدخيلة في لغتنا الدارجة لمحمد ابن ناصر العبودي")

    );


?>

<article>
    <h2>الكلمة: <?php echo implode(', ', $data['forms']); ?></h2>
    
    <p><strong>المعنى المراد:</strong> <?php echo implode(', ', $data['meanings']); ?></p>
    <p><strong>دخيلة من:</strong> <?php echo $data['origin']; ?></p>
    <p><strong>أصلها:</strong> <?php echo $data['original']; ?></p>

    <h3>سياقات:</h3>
    <ul>
        <?php foreach($data['examples'] as $example): ?>
            <li><?php echo $example; ?></li>
        <?php endforeach; ?>
    </ul>

    <h3>التصنيف:</h3>
    <ul>
        <?php foreach($data['categories'] as $category): ?>
            <li><?php echo $category; ?></li>
        <?php endforeach; ?>
    </ul>

    <h3>المراجع:</h3>
    <ul>
        <?php foreach($data['references'] as $reference): ?>
            <li><?php echo $reference; ?></li>
        <?php endforeach; ?>
    </ul>
</article>