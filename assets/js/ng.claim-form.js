(function (angular) {
    "use strict";
    var module = angular.module('ng.claim-form', ['ngSanitize', 'checklist-model']);

    module.controller('ClaimFormController', ['$scope', '$timeout', 'ClaimFormService', function ($scope, $timeout, ClaimFormService) {
        $scope.maxUploadSizeFormatted = MapasCulturais.maxUploadSizeFormatted;

        $scope.open = function (editbox, id, event) {
            MapasCulturais.AjaxUploader.init()
            editbox.open(id, event)
        }

        $scope.sendClaim = function () {
            $('.carregando-arquivo').show();
            $('.submit-attach-opportunity').hide();

            var $form = $('#send-clain-form');

            

            $form.submit();
            if (!$form.data('onSuccess')) {
                $form.data('onSuccess', true);
                $form.on('ajaxForm.success', function (evt, response) {
                    var template = MapasCulturais.TemplateManager.getTemplate('claim-form-response');
                    var data = response['formClaimUpload'][0];
                    var html = Mustache.render(template,data);
                    $(".edit-box").hide()
                    $(".js-formClaimUpload").append(html)
                    $(".progress").addClass('inactive')
                    $('.carregando-arquivo').hide();
                    $('.submit-attach-opportunity').show();
                    MapasCulturais.Messages.success("Recurso enviado com sucesso");
                });
            }
        }
    }]);

    module.factory('ClaimFormService', ['$http', '$rootScope', function ($http, $rootScope) {

        return {};
    }]);
})(angular);
