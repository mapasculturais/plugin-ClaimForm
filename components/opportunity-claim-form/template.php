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
                <h4><?php i::_e('Envie um modelo de documento que deve ser anexado pelos solicitantes') ?></h4>
                <div>
                    <input type="file" name="formClaimUploadSample" @change="setFile()" ref="file">
                </div>
                <div v-if="filesUpload.url">
                    <ul>
                        <li>
                            <a :href="filesUpload.url" target="_blank" download >{{filesUpload.name}}</a>
                        </li>
                    </ul>
                </div>
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
    <div v-if="filesSample.url">
        <ul>
            <li>
                <a :href="filesSample.url" target="_blank" download ><?php i::_e('Baixar arquivo de exemplo') ?></a>
            </li>
        </ul>
    </div>
</div>