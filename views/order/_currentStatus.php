<div class="current-status">
    <title><?php echo $title;?></title>
    <h1><?php echo $title;?></h1>
    <div class="row">
        <div class="col-xs-6">
            <h4><?php echo Yii::t('app', 'Delivery information');?></h4>
            <p><?php echo Yii::t('app', 'Status');?>: <?php echo $status['name'];?></p>
            <p><?php echo Yii::t('app', 'Description');?>: <?php echo $status['description'];?></p>
            <p><?php echo Yii::t('app', 'Date');?>: <?php echo $status['date'];?></p>
        </div>
        <div class="col-xs-6">
            <h4><?php echo Yii::t('app', 'Provider information');?></h4>
            <p><?php echo Yii::t('app', 'Status');?>: <?php echo $provider['name'];?></p>
            <p><?php echo Yii::t('app', 'Description');?>: <?php echo $provider['description'];?></p>
            <p><?php echo Yii::t('app', 'Date');?>: <?php echo $provider['date'];?></p>
        </div>

    </div>
</div>