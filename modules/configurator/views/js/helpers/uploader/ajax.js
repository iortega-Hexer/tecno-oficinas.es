/**
 * 2007-2019 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author DMConcept <support@dmconcept.fr>
 *  @copyright 2015 DMConcept
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *
 * Don't forget to prefix your containers with your own identifier
 * to avoid any conflicts with others containers.
 */

function humanizeSize(bytes)
{
    if (typeof bytes !== 'number') {
        return '';
    }

    if (bytes >= 1000000000) {
        return (bytes / 1000000000).toFixed(2) + ' GB';
    }

    if (bytes >= 1000000) {
        return (bytes / 1000000).toFixed(2) + ' MB';
    }

    return (bytes / 1000).toFixed(2) + ' KB';
}

function ajaxConfiguratorUploader(conf_upload) {
    $('#' + conf_upload.id + '-upload-button').find('.spinner').hide();
    $('#' + conf_upload.id + '-upload-button').find('.spinner-in-progress').hide();

    var max_files = conf_upload.max_files - conf_upload.files_counter;

    if (conf_upload.is_files) {
        $('#' + conf_upload.id + '-images-thumbnails').parent().show();
    }

    var upload_button = Ladda.create(document.querySelector('#' + conf_upload.id + '-upload-button'));
    var total_files = 0;

    var config = {};
    config.dataType = 'json';
    config.async = true; // need to be true to run onprogress event
    if (conf_upload.url) {
        config.url = conf_upload.url;
    }
    config.autoUpload = false;
    config.singleFileUploads = true;
    if (conf_upload.maxFileSize) {
        config.maxFileSize = conf_upload.maxFileSize;
    }
    if (conf_upload.dropZone) {
        config.dropZone = $(conf_upload.dropZone);
    }
    config.start = function (e) {
        upload_button.start();
        $('#' + conf_upload.id + '-upload-button').unbind('click'); //Important as we bind it for every elements in add function
        $('#' + conf_upload.id + '-upload-button').find('.spinner').show();
        $('#' + conf_upload.id + '-upload-button').find('.ladda-label').hide();
        $('#' + conf_upload.id + '-upload-button').find('.spinner-in-progress').show();
        $('#' + conf_upload.id + '-errors').html('').parent().hide();
    };
    config.fail = function (e, data) {
        total_files--;
        $('#' + conf_upload.id + '-files-list').html('');
        $('#' + conf_upload.id + '-errors').html(data.errorThrown.message).parent().show();
        $('#' + conf_upload.id + '-upload-button').find('.spinner').hide();
        $('#' + conf_upload.id + '-upload-button').find('.ladda-label').show();
        $('#' + conf_upload.id + '-upload-button').find('.spinner-in-progress').hide();
    };
    config.done = function (e, data) {
        if (data.result) {
            if (typeof data.result[conf_upload.name] !== 'undefined') {
                for (var i = 0; i < data.result[conf_upload.name].length; i++) {
                    if (data.result[conf_upload.name][i] !== null) {
                        if (typeof data.result[conf_upload.name][i].error !== 'undefined' && data.result[conf_upload.name][i].error != '') {
                            $('#' + conf_upload.id + '-errors').html('<strong>' + data.result[conf_upload.name][i].name + '</strong> : ' + data.result[conf_upload.name][i].error).parent().show();
                            total_files--;
                            $('#' + conf_upload.id + '-files-list').html('');
                        } else {
                            $(data.context).appendTo($('#' + conf_upload.id + '-success'));
                            $('#' + conf_upload.id + '-success').parent().show();

                            // Refresh the progress circle
                            Front.notify();

                            if (typeof data.result[conf_upload.name][i].link !== 'undefined') {
                                template = '<a class="btn btn-link" href="' + data.result[conf_upload.name][i].link + '"><i class="icon-download"></i> ' + data.result[conf_upload.name][i].name + '</a>';

                                if (typeof data.result[conf_upload.name][i].delete_url !== 'undefined') {
                                    template += '<a class="btn btn-default btn-sm configurator-delete-upload" href="' + data.result[conf_upload.name][i].delete_url + '"><i class="icon-trash"></i> ' + conf_upload.labels.delete + '</a>';
                                }
                                
                                template = '<div class="configurator-upload-row">' + template + '</div>';

                                $('#' + conf_upload.id + '-images-thumbnails').html($('#' + conf_upload.id + '-images-thumbnails').html() + template);
                                $('#' + conf_upload.id + '-images-thumbnails').parent().show();
                            }
                        }
                    }
                }
            }

            $(data.context).find('button').remove();
            $('#' + conf_upload.id + '-upload-button').find('.spinner').hide();
            $('#' + conf_upload.id + '-upload-button').find('.ladda-label').show();
            $('#' + conf_upload.id + '-upload-button').find('.spinner-in-progress').hide();
        }
    };
    
    config.progress = function (e, data) {
        if (conf_upload.display_progress) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            data.context.find('.progress-bar').width(progress + '%');
            if (data.loaded === data.total) {
                data.context.find('.progress').addClass('configurator-upload-finished');
                data.context.find('.progress').removeClass('configurator-uploading');
            }
        }
    }

    $('#' + conf_upload.id).fileupload(
        config
    ).on('fileuploadalways', function (e, data) {
        total_files--;
        max_files--;

        if (total_files == 0) {
            upload_button.stop();
            $('#' + conf_upload.id + '-upload-button').unbind('click');
            $('#' + conf_upload.id + '-files-list').parent().hide();
        }
    }).on('fileuploadadd', function (e, data) {
        if (total_files >= max_files) {
            e.preventDefault();
            alert(conf_upload.labels.fileuploadadd_alert);
            return;
        }

        data.context = $('<div/>').addClass('form-group').appendTo($('#' + conf_upload.id + '-files-list'));
        $('#' + conf_upload.id + '-upload-button').attr('disabled', false);
        $('#' + conf_upload.id + '-success').html('').parent().hide();

        var show_image = parseInt(conf_upload.show_upload_image);

        if (show_image) {
            var src = window.URL.createObjectURL(data.files[0]);
            var img = '<img src="' + src + '" class="uploader-thumb">';
        } else {
            var img = '';
        }
        var file_name = $('<span/>').append('<strong>' + data.files[0].name + '</strong> (' + humanizeSize(data.files[0].size) + ')' + img).appendTo(data.context);

        var button = $('<button/>')
            .addClass('btn btn-default pull-right btn-sm')
            .prop('type', 'button')
            .html('<i class="icon-trash"></i> ' + conf_upload.labels.remove_file)
            .appendTo(data.context)
            .on('click', function () {
                total_files--;
                data.files = null;

                var total_elements = $(this).parent().siblings('div.form-group').length;
                $(this).parent().remove();

                if (total_elements == 0) {
                    $('#' + conf_upload.id + '-files-list').html('').parent().hide();
                }
            });
            
        if (conf_upload.display_progress) {
            var progressbar = $('<div class="progress configurator-progress configurator-uploading">'
                    + '<div class="progress-bar" role="progressbar" style="width: 0%;"></div>'
                    + '</div>')
                .appendTo(data.context);
        }

        $('#' + conf_upload.id + '-files-list').parent().show();
        $('#' + conf_upload.id + '-upload-button').show().bind('click', function () {
            if (data.files != null)
                data.submit();
        });

        total_files++;
    }).on('fileuploadprocessalways', function (e, data) {
        var index = data.index, file = data.files[index];

        if (file.error) {
            $('#' + conf_upload.id + '-errors').append('<div class="form-group"><strong>' + file.name + '</strong> (' + humanizeSize(file.size) + ') : ' + file.error + '</div>').parent().show();
            $(data.context).find('button').trigger('click');
            $('#' + conf_upload.id + '-files-list').html('').parent().hide();
        }
    });

    $('#' + conf_upload.id + '-files-list').parent().hide();
    total_files = 0;

    $('#' + conf_upload.id + '-add-button').on('click', function () {
        $('#' + conf_upload.id + '-success').html('').parent().hide();
        $('#' + conf_upload.id + '-errors').html('').parent().hide();
        $('#' + conf_upload.id).trigger('click');
    });
    
    $('#' + conf_upload.id + '-images-thumbnails').on('click', '.configurator-delete-upload', function(e){
        e.preventDefault();
        var btn = $(this);
        $.ajax({
            method: "GET",
            url: btn.attr('href')
        }).done(function(res) {
            total_files--;
            btn.closest('.configurator-upload-row').remove();
        });
    });

}

/* WEBCAM MANAGER */

var webcamManager = {

    conf_upload: null,
    id: '#',
    counter: 0,
    active: false,
    id_configurator_container: '#configurator_block',
    class_webcam_active_btn: '.uploader-webcam-active-btn',
    class_webcam_picture_btn: '.uploader-webcam-picture-btn',
    class_webcam_block: '.uploader-webcam-block',
    class_webcam_video: '.uploader-webcam-video',
    class_webcam_cancel_btn: '.uploader-webcam-cancel-btn',
    class_webcam_accept_btn: '.uploader-webcam-accept-btn',

    current_picture: null,

    init: function (conf_upload) {
        var self = this;
        self.conf_upload = conf_upload;
        self.id = '#' + conf_upload.id;

        $(self.id_configurator_container).on('click', self.class_webcam_active_btn, function () {
            self.activeWebcam();
        });
        $(self.id_configurator_container).on('click', self.class_webcam_picture_btn, function () {
            self.takePicture();
        });
        $(self.id_configurator_container).on('click', self.class_webcam_accept_btn, function () {
            self.acceptPicture();
        });
        $(self.id_configurator_container).on('click', self.class_webcam_cancel_btn, function () {
            self.cancelPicture();
        });
    },

    activeWebcam: function () {
        var self = this;
        if (!self.active) {
            var step = $(self.id)
            var video = step.find(self.class_webcam_video).get(0);

            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({video: true}).then(function (stream) {
                    video.src = window.URL.createObjectURL(stream);
                    video.play();
                });
            }
        }
        self.active = true;
    },

    takePicture: function () {
        var self = this;
        var step = $(self.id)
        var video = step.find(self.class_webcam_video).get(0);
        self.counter++;

        var canvas = document.createElement("canvas");
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

        self.current_picture = canvas.toDataURL();

        step.find(self.class_webcam_block).removeClass('preview-off');
        step.find(self.class_webcam_block).addClass('preview-on');
        step.find(self.class_webcam_block).append('<img class="webcam-preview" src="' + self.current_picture + '">');
    },

    acceptPicture: function () {
        var self = this;
        var step = $(self.id);

        var ImageURL = self.current_picture;
        var block = ImageURL.split(";");
        var contentType = block[0].split(":")[1];
        var realData = block[1].split(",")[1];

        var blob = self.b64toBlob(realData, contentType);
        blob.name = self.conf_upload.labels.webcam_picture + self.counter + '.png';
        var id = self.conf_upload.id;
        $('#' + id).fileupload('add', {files: blob});

        step.find(self.class_webcam_block).addClass('preview-off');
        step.find(self.class_webcam_block).removeClass('preview-on');
        step.find(self.class_webcam_block + ' img').remove();
    },

    cancelPicture: function () {
        var self = this;
        var step = $(self.id);

        self.current_picture = null;

        step.find(self.class_webcam_block).addClass('preview-off');
        step.find(self.class_webcam_block).removeClass('preview-on');
        step.find(self.class_webcam_block + ' img').remove();
    },

    b64toBlob: function (b64Data, contentType, sliceSize) {
        contentType = contentType || '';
        sliceSize = sliceSize || 512;

        var byteCharacters = atob(b64Data);
        var byteArrays = [];

        for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {
            var slice = byteCharacters.slice(offset, offset + sliceSize);

            var byteNumbers = new Array(slice.length);
            for (var i = 0; i < slice.length; i++) {
                byteNumbers[i] = slice.charCodeAt(i);
            }

            var byteArray = new Uint8Array(byteNumbers);

            byteArrays.push(byteArray);
        }
        return new Blob(byteArrays, {type: contentType});
    }
};

/* INIT */

$(document).ready(function () {
    if (typeof configurator_uploader !== 'undefined') {
        for (var i in configurator_uploader) {
            var conf_upload = configurator_uploader[i];
            ajaxConfiguratorUploader(conf_upload);
            if (conf_upload.use_upload_camera) {
                webcamManager.init(conf_upload);
            }
        }
    }
});
