<?php

use MapasCulturais\i;

$file = $opportunity->getFiles('formClaimUploadSample');

$template = '
<div id="file-{{id}}" class="objeto">
    <a href="{{url}}" rel="noopener noreferrer">{{name}}</a> 
    <a data-href="{{deleteUrl}}" data-target="#file-{{id}}" data-configm-message="Remover este arquivo?" class="deleteRight delete hltip js-remove-item" data-hltip-classes="hltip-ajuda" title="Excluir arquivo"><span class="configdelete">Excluir</span></a>
</div>';

?>
<span class="label"><?php i::_e("Arquivo de exemplo para o recurso"); ?>: </span><br>
<a class="add btn btn-default js-open-editbox hltip" data-target="#editbox-formClaimUploadSample-file" href="#"> <?= i::_e('Vincular arquivo') ?></a>

<div id="editbox-formClaimUploadSample-file" class="js-editbox mc-left" title="<?= i::_e('Arquivo de exemplo') ?>" data-submit-label="Enviar">
    <?php $this->ajaxUploader($opportunity, 'formClaimUploadSample', 'append', '.js-formClaimUploadSample', $template, '', false, false, false) ?>
</div>

<div class="js-formClaimUploadSample">
    <?php if ($file) : ?>
        <div id="file-<?php echo $file->id ?>" class="objeto <?php if ($this->isEditable()) echo i::_e(' is-editable'); ?>">
            <a href="<?php echo $file->url . '?id=' . $file->id; ?>" download><?php echo $file->description ? $file->description :  mb_substr(pathinfo($file->name, PATHINFO_FILENAME), 0, 20) . '.' . pathinfo($file->name, PATHINFO_EXTENSION); ?></a>
            <a data-href="<?php echo $file->deleteUrl ?>" data-target="#file-<?php echo $file->id ?>" data-configm-message="Remover este arquivo?" class="deleteRight delete hltip js-remove-item" data-hltip-classes="hltip-ajuda" title="Excluir arquivo."><span class="configdelete"><?php i::_e("Excluir"); ?></span></a>
        </div>
    <?php endif ?>
</div>