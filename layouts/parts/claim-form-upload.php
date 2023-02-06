<?php

use MapasCulturais\i;

$app = MapasCulturais\App::i();
$files = $entity->getFiles('formClaimUpload');
$filesSample = $entity->opportunity->getFiles('formClaimUploadSample');
$url = $app->createUrl("registration", "upload/{$entity->id}")
?>

<div class="widget claim-form" ng-controller="ClaimFormController">
    <h3> <?php i::_e("Solicitar recurso") ?> </h3>
    <div class="confiButtongeneral">
        <a class="add btn btn-default" ng-click="open(editbox, 'form-claim', $event)" rel="noopener noreferrer"><?= i::_e('Formulário de recurso') ?></a>
        <?php if ($filesSample) : ?>
            <a class="add btn btn-default download" download href="<?= $filesSample->url ?>"><?= i::_e('Baixar arquivo de exemplo') ?></a>
        <?php endif ?>

    </div>

    <edit-box id="form-claim" position="bottom" cancel-label="<?php i::esc_attr_e("Cancelar"); ?>" submit-label="<?php i::esc_attr_e("Enviar recurso"); ?>" loading-label="<?php i::esc_attr_e("Carregando ..."); ?>" on-submit="sendClaim" close-on-cancel='true' index="{{$index}}" spinner-condition="data.uploadSpinner">
        <form id="send-clain-form" class="js-ajax-upload" method="post" action="<?= $url ?>" data-group="formClaimUpload" enctype="multipart/form-data">
            <div>
                <?php i::_e("Mensagem"); ?>:<br />
                <textarea type="text" rows="5" cols="30" name="description[formClaimUpload]"></textarea>
            </div>
            <div>
                <?php i::_e("Anexar arquivo"); ?>:<br />
                <?php if ($filesSample) : ?>
                    <small><?php i::_e("Baixe o exemplo do arquivo no botão <b><i>Baixar arquivo de exemplo</i></b>"); ?></small>
                <?php endif ?>
                <div class="alert danger hidden"></div>
                <p class="form-help"><?php i::_e("Tamanho máximo do arquivo:"); ?> {{maxUploadSizeFormatted}}</p>
                <input type="file" name="formClaimUpload" />

                <div class="js-ajax-upload-progress">
                    <div class="progress">
                        <div class="bar"></div>
                        <div class="percent">0%</div>
                    </div>
                </div>
            </div>
        </form>
    </edit-box>

    <div class="scrolling">
        <ul class="js-formClaimUpload">
            <?php if (is_array($files)) : ?>
                <?php foreach ($files as $file) : ?>
                    <li id="file-<?php echo $file->id ?>" class="objeto <?php if ($this->isEditable()) echo i::_e(' is-editable'); ?>">
                        <a href="<?php echo $file->url . '?id=' . $file->id; ?>" download><?= $file->name ?></a>
                        <a data-href="<?php echo $file->deleteUrl ?>" data-target="#file-<?php echo $file->id ?>" data-configm-message="Remover este arquivo?" class="deleteRight delete hltip js-remove-item" data-hltip-classes="hltip-ajuda" title="Excluir arquivo."><span class="configdelete"><?php i::_e("Excluir"); ?></span></a>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>
</div>

<script id='claim-form-response' class='js-mustache-template' type="html/template">
    <li id="file-{{id}}" class="objeto">
        <a href="{{url}}" rel="noopener noreferrer">{{name}}</a> 
        <a data-href="{{deleteUrl}}" data-target="#file-{{id}}" data-configm-message="Remover este arquivo?" class="deleteRight delete hltip js-remove-item" data-hltip-classes="hltip-ajuda" title="Excluir arquivo"><span class="configdelete">Excluir</span></a>
    </li';
</script>