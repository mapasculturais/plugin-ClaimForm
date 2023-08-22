<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-container
    mc-modal
    mc-icon
');
?>
<div v-if="isActive()">
    <mc-modal :title="modalTitle" classes="opportunity-claim-form" button-classes="opportunity-claim-form__buttonlabel">
        <template #default>
            <div>
                <label class="button button--primary-outline button--icon" for="formClaimUploadSample">
                    <mc-icon name="upload"></mc-icon> Enviar
                </label>
                <input type="file" id="formClaimUploadSample" name="formClaimUploadSample" @change="setFile" ref="file">
                <a href="#" download="filename"><?php i::_e("Baixe o arquivo modelo para o anexo")?></a>
            </div>
            <div class="opportunity-claim-form__content">
                <h5 class="semibold opportunity-claim-form__label"><?php i::_e('Descreva abaixo os motivos do recurso') ?></h5>
                <textarea v-model="claim.message" id="message" class="opportunity-claim-form__textarea"></textarea>
            </div>
        </template>
        <template #actions="modal">
            <button class="button button--text delete-registration " @click="close(modal)"><?php i::_e('Cancelar') ?></button>
            <button class="button button--primary" @click="sendClain(modal)"><?php i::_e('Solicitar') ?></button>
        </template>
        <template #button="modal">
            <h5 class="opportunity-claim-form__resource bold"><?php i::_e('Discorda do resultado?') ?></h5>
            <div>
                <?php i::_e('Período do recurso de recurso');?> {{claimFromDate}} a {{claimclaimTo}} 
            </div>
            <button class="button button--primary-outline" @click="open(modal)"><?php i::_e('Solicitar Recurso') ?></button>
        </template>
    </mc-modal>
</div>