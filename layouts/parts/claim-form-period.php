<div>
    <div class="claim-form-dates">
        <?php \MapasCulturais\i::_e("Dada de abertura e fechamento do recurso"); ?> <br>
        <strong class="js-editable" data-type="date" data-yearrange="2000:+25" <?php echo $opportunity->claimFrom ? "data-value='" . $opportunity->claimFrom->format('Y-m-d') . "'" : '' ?> data-viewformat="dd/mm/yyyy" data-edit="claimFrom" data-showbuttons="false" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Data inicial"); ?>"><?php echo $opportunity->claimFrom ? $opportunity->claimFrom->format('d/m/Y') : \MapasCulturais\i::__("Data inicial"); ?></strong>
        <?php \MapasCulturais\i::_e("a"); ?>
        <strong class="js-editable" data-type="date" data-yearrange="2000:+25" <?php echo $opportunity->claimTo ? "data-value='" . $opportunity->claimTo->format('Y-m-d') . "'" : '' ?> data-viewformat="dd/mm/yyyy" data-edit="claimTo" data-timepicker="#claimTo_time" data-showbuttons="false" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Data final"); ?>"><?php echo $opportunity->claimTo ? $opportunity->claimTo->format('d/m/Y') : \MapasCulturais\i::__("Data final"); ?></strong>
        <?php \MapasCulturais\i::_e("às"); ?>
        <strong class="js-editable" id="claimTo_time" data-datetime-value="<?php echo $opportunity->claimTo ? $opportunity->claimTo->format('Y-m-d H:i') : ''; ?>" data-placeholder="<?php \MapasCulturais\i::esc_attr_e("Hora final"); ?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Hora final"); ?>"><?php echo $opportunity->claimTo ? $opportunity->claimTo->format('H:i') : ''; ?></strong>
    </div>
</div>