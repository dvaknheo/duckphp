<?php
use Project\Base\Helper\ViewHelper as V;

$flag_resent=session('resent');

V::startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><?= V::HL('Verify Your Email Address'); ?></div>

                <div class="card-body">
                    <?php if($flag_resent): ?>
                        <div class="alert alert-success" role="alert">
                            <?= V::HL('A fresh verification link has been sent to your email address.'); ?>

                        </div>
                    <?php endif; ?>

                    <?= V::HL('Before proceeding, please check your email for a verification link.'); ?>

                    <?= V::HL('If you did not receive the email'); ?>,
                    <form class="d-inline" method="POST" action="<?= V::H($url_email_resend)?>">
                        <?= $csrf_field;?>
                        <button type="submit" class="btn btn-link p-0 m-0 align-baseline"><?= V::HL('click here to request another'); ?></button>.
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php V::stopSection(); ?>
<?php V::ShowBlock('layouts/app');?>
