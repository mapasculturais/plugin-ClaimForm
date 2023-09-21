<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-file
    mc-icon
    mc-link
    mc-modal
');
?>

<div class="opportunity-enable-claim">
    <div class="opportunity-enable-claim__header">
        <h4 class="bold opportunity-enable-claim__title"><?= i::__("Configuração de recurso nesta fase")?></h4>
        <div class="field opportunity-enable-claim__input">
            <label for="resource">
                <input type="checkbox" id="resource" v-model="isActiveClaim" @click="autoSave()" />
                <?= i::__("Habilitar solicitação de recurso") ?>
            </label>
        </div>
        
  
    </div>
    <div class="opportunity-enable-claim__content" v-if="isActiveClaim">
            <div class="grid-12">
                <entity-field :entity="entity" classes="col-3 sm:col-12" prop="claimFrom"></entity-field>
                <entity-field :entity="entity" classes="col-3 sm:col-12" prop="claimTo"></entity-field>
            </div>
            <div class="opportunity-enable-claim__email">
                <label class="opportunity-enable-claim__label" for="input">
                    <h5 class="semibold opportunity-enable-claim__subtitle"><?= i::__("Insira o email que receberá as solicitações") ?></h5>
                </label>
                <div class="opportunity-enable-claim__save">
                    <input type="text" v-model="entity.claimEmail" @change="autoSave()" />
                </div>
            </div>
            <div>
                <label class="opportunity-enable-claim__send"><?php i::_e('Envie um modelo de documento que deve ser anexado pelos solicitantes') ?></label>
                <entity-file :entity="entity" groupName="formClaimUploadSample" title="" editable titleModal="<?= i::__("Configurar arquivo de exemplo") ?>">
                    <template #modal-actions>
                        <button class="col-6 button button--text" type="reset" @click="modal.close()"> <?php i::_e("Cancelar") ?> </button>
                        <button class="col-6 button button--primary" type="submit" @click="upload(modal); $event.preventDefault();"> <?php i::_e("Enviar") ?> </button>
                    </template>
                </entity-file>
            </div>
    </div>
</div>