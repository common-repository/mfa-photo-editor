<?php

$plugin = \SOSIDEE_MFA_PHOTOEDITOR\SosPlugin::instance();

$plugin::addMediaLibrary();

?>

<h2>
    <?php $plugin::te('page.gallery.title'); ?>
    &nbsp;
    <i class="material-icons" style="cursor: pointer; color: #2271b1; vertical-align: middle;" onclick="jsSosShowHideBlock('sos-gal-info');" title="<?php $plugin::te('info.show.hide'); ?>">help</i>
</h2>

<div id="sos-gal-info" class="wrap sos-gal-info" style="display: none;">
    <p><?php $plugin::te( ['info.add.images', ['{icon}' => '<span class="button button-secondary"><i class="material-icons" style="vertical-align: middle;">add</i></span>'] ]); ?></p>
    <p><?php $plugin::te( ['info.save.gallery', ['{button}' => '<span class="button button-primary">{label}</span>', '{label}' => 'button.text.save.gallery'] ]); ?></p>
    <p><?php $plugin::te( ['info.copy.url', ['{icon}' => '<i class="material-icons" style="vertical-align: bottom;">content_copy</i>'] ]); ?></p>
    <p><?php $plugin::te( ['info.remove.image', ['{icon}' => '<i class="material-icons" style="vertical-align: bottom; color: red;">delete_forever</i>'] ]); ?></p>
    <p><?php $plugin::te('info.sort.images'); ?></p>
</div>

    <div class="wrap">
        <?php $plugin::msgHtml(); ?>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php $plugin::te('label.gallery.url'); ?></th>
                <td class="sos-gal-url">
                    <input id="sos-gal-txt-url" type="text" value="<?php echo $plugin->jsonUrl; ?>" readonly="readonly">
                    <i title="<?php $plugin::te('text.copy.url'); ?>" onclick="jsSosCopy2CB('sos-gal-txt-url', sosgal_local.copy2cb);" class="material-icons">content_copy</i>
                </td>
            </tr>
            <tr>
                <th scope="row">&nbsp;</th>
                <td>
                    <button id="sos-gal-btn-save" class="button button-primary" disabled="disabled"><?php $plugin::te('button.text.save.gallery'); ?></button>
                    <img id="sos-gal-loader" src="<?php echo $plugin->getLoaderSrc(); ?>" alt="waiting..." style="margin-left: 1em; display: none;">
                </td>
            </tr>
        </table>

        <br><br>

        <div id="sos-gal-box-overlay" class="sos-gal-box">
            <div class="sos-gal-box-inner">
                <div class="sos-gal-title">OVERLAY</div>
                <div class="sos-gal-cmd">
                    <button id="sos-gal-btn-add-overlay" class="button button-secondary" disabled="disabled"><i class="material-icons" style="vertical-align: middle;">add</i></button>
                </div>
                <ul id="sos-gal-list-overlay">
                </ul>
                <br clear="both">
            </div>
        </div>
        <div id="sos-gal-box-scenario" class="sos-gal-box">
            <div class="sos-gal-box-inner">
                <div class="sos-gal-title">SCENARIO</div>
                <div class="sos-gal-cmd">
                    <button id="sos-gal-btn-add-scenario" class="button button-secondary" disabled="disabled"><i class="material-icons" style="vertical-align: middle;">add</i></button>
                </div>
                <ul id="sos-gal-list-scenario">
                </ul>
                <br clear="both">
            </div>
        </div>

    </div>
