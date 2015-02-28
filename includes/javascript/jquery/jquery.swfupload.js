var PhotosResult = "";
var Count = 0;
var UploadedFiles = 0;
var defVal;
var loadedObj = false;
var swfu;

$.getScript("includes/javascript/swfupload/swfupload.js", function(){loadedObj=true;});
KR_AJAX.include.style('includes/javascript/swfupload/swfupload.css');

function kr_fileDialogComplete(numFilesSelected, numFilesQueued) {
    try {
        if(numFilesQueued > 0) {
            PhotosResult = numFilesQueued == '1' ? ' image' : ' images';
            PhotosResult = numFilesQueued + PhotosResult + " attached";
            Count = parseInt(numFilesQueued);
            defVal = $('#AddPhotos').val();
            $('#AddPhotos').val(window.js_lang.loading);
            $('#submitStatus').attr('disabled', 'disabled').addClass('disabled');
            this.startUpload();
        }
    } catch (ex) {
    }
}

function kr_uploadProgress(file, bytesLoaded) {
    try {
        var pw = 115;
        var w = Math.ceil(pw * (UploadedFiles / Count + (bytesLoaded / (file.size * Count))));
        $('#Progress').stop().animate({ width: w });
    } catch (ex) {
    }
}
function kr_uploadSuccess(file, serverData) {
    try {UploadedFiles++;} catch (ex) {}
}

function kr_uploadComplete(file) {
    try {
        if (this.getStats().files_queued > 0) {
            this.startUpload();
        } else {
            $("#Progress").fadeTo('slow', 0.0);
            setTimeout(function(){
                $("#Progress").css("width", '0');
                $("#AddPhotos").val(defVal);
            }, 700);
            $("#Progress").fadeTo('slow', 1.0);
            update_upload($("#uploaddir").val());
        }
    } catch (ex) {
    }
}

function kr_fileQueueError(file, errorCode, message) {
    try {
        switch (errorCode) {
            case SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED: alert('Too many images'); break;
            case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE: alert('Cannot upload Zero Byte files.'); break;
            case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT: alert('File is too big.'); break;
            case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE: alert('Invalid File Type.'); break;
            default: alert('Unhandled Error'); break;
        }
    } catch (ex) {
    }

}

function swfuploadLoaded() {
    $('#Buttons object').hover(function() {$(this).next().addClass('hover');}, function() {$(this).next().removeClass('hover');});
}

(function($_){$_.extend($_, {
    swfupload:function(settings){
        $(document).ready(function(){
            if(isObject(window.swfu)){                
                $('#SWFUpload_0').remove();
                window.swfu.destroy();
                window.swfu = '';
            }
            if(loadedObj==false){
                setTimeout(function(){KR_AJAX.swfupload(settings)}, 100);
                return false;
            }
            settings = $.extend({
                flash_url : "includes/javascript/swfupload/swfupload.swf",
                flash9_url : "includes/javascript/swfupload/swfupload_fp9.swf",
                upload_url: "upload.php",
                post_params: {"PHPSESSID" : 'NONE'},
                file_size_limit : "2 MB",
                file_types : "*.*",
                file_types_description : "All Files",
                file_upload_limit : 100,
                file_queue_limit : 0,
                custom_settings : {progressTarget : "fsUploadProgress", cancelButtonId : "btnCancel"},
                debug: false,

                button_image_url : "includes/images/pixel.gif",
                button_width: 115,
                button_height: 32,
                button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
                button_cursor: SWFUpload.CURSOR.HAND,
                button_placeholder_id: "fAddPhotos",
                debug : false,

                file_dialog_complete_handler: kr_fileDialogComplete,
                upload_progress_handler: kr_uploadProgress,
                upload_success_handler: kr_uploadSuccess,
                upload_complete_handler: kr_uploadComplete,
                swfupload_loaded_handler: swfuploadLoaded,
                file_queue_error_handler: kr_fileQueueError
            }, settings);
            var_inited('initSPost',function(){
               initSPost();
               settings.post_params.secID = jsSecretID;
               window.swfu = new SWFUpload(settings);
            })
        });
    }
})
})(KR_AJAX)