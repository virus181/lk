<?php
use app\models\Call;
use app\models\helper\Phone;

/** @var Call[] $calls */

if($calls): ?>
<div class="block calls-block row">
    <div class="col-sm-12 delivery-info">
        <h2>История звонков</h2>
        <div class="">
            <?php foreach ($calls as $call): ?>
                <p>
                    <span class="call-string">
                        <?=date('d.m.Y, H:i', strtotime($call->ring_time));?>&nbsp;
                        <i class="<?= $call->direction;?>"></i>
                    </span>
                    <span class="pull-right"><?=$call->getTag();?></span>
                </p>
                <p class="phone">
                    <span class="pull-right"><?=$call->getDownloadUrl();?></span>
                    <strong><?= (new Phone($call->client_phone))->getHumanView();?></strong>
                </p>
                <hr />
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>
<script type="text/javascript">
    $('[data-toggle="tooltip"]').tooltip();
</script>
