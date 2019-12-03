<?php
use Project\Base\Helper\ViewHelper as V;

V::startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    <?php if($session_status): ?>
                        <div class="alert alert-success" role="alert">
                            <?= V::H($session_status); ?>

                        </div>
                    <?php endif; ?>

                    You are logged in!
                </div>
            </div>
        </div>
    </div>
</div>
<?php V::stopSection(); ?>
<?php V::ShowBlock('layouts/app');?>