<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'About';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php echo Html::img('https://i0.wp.com/9to5mac.com/wp-content/uploads/sites/6/2025/09/sora-app-icon.jpg?w=1500&quality=82&strip=all&ssl=1',['alt'=> 'hopa']);?>

    <p>
        This is the About page. You may modify the following file to customize its content:
    </p>

    <code><?= __FILE__ ?></code>
</div>
