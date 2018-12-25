<?php
namespace app\widgets;

use Yii;
use yii\jui\JuiAsset;

class Modal extends \yii\bootstrap\Modal
{
    public $draggable = false;

    public function run()
    {
        parent::run();

        Yii::$app->view->registerJs(
        <<<JS
            var id = '#$this->id';
           $(id).on('show.bs.modal', function() {
               var obj = $(this);

               obj.find('.modal-body').html('<div class="text-center"><i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i></div>');
               obj.find('.modal-header').find('.modal-title').remove();
           });
            $(id).on('shown.bs.modal', function(e) {
                e.stopPropagation();
                
                if (e.relatedTarget === undefined) {
                    return false;
                }
                var link = $(e.relatedTarget),
                    obj = $(this);
                
                $.ajax({
                    url: link.data('href'),
                    method: 'get',
                    dataType: 'html',
                    success: function(data) {
                        var page = $(data),
                            header = page.find('h1').text(),
                            title = page.find('title').text();
                        
                        page.find('h1').remove();
                        obj.find('.modal-header').append('<h3 class="modal-title">'+header+'</h5>');
                        obj.find('.modal-body').html(page);
                        
                        if (page.find('title').length > 0) {
                            $('head').find('title').html(title);
                            page.find('title').remove();
                        }
                    },
                    error: function(data) {
                        $('.modal-body').html('<h3 class="text-center">'+data.responseText+'</h2><br/>');
                    }
                })
            });
JS
        );

        if ($this->draggable) {
            JuiAsset::register($this->getView());
            Yii::$app->view->registerJs(
                <<<JS
                $(id+' .modal-content').draggable({              
                   handle: '.modal-header'
                });
JS
            );
        }
    }
}