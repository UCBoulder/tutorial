(function ($) {
  $(document).ready(function () {
    function updateStepField() {
      $( ".file-widget-multiple__table-wrapper table tbody tr" ).each(function ( index, element ) {
        var id = $(this).find("span.file--image").attr('data-drupal-selector');
        var hiddenInputId = $(this).find(".image-widget input[type=hidden]").attr('data-drupal-selector');
        // Theme switched to using the wieght of the draggable item over the fid
        // passing this along to correctly select the title input.
        var stepWeight = thenum = hiddenInputId.match(/\d+/)[0];
        id = id.match(/\d+/g).map(Number);
        $(this).find('.tabledrag-hide').prev().prev().prev().prev().find('.form-item-field-tut-body-image-current-' + id[1] + '-meta-title').find('label').text("Step Text");
        $(this).find('.tabledrag-hide').prev().prev().prev().prev().find('.form-item-field-tut-body-image-current-' + id[1] + '-meta-title').find('.description').text("This text will be added to your clipboard for the text in the step.");
        if ( !$(this).find('.tabledrag-hide').prev().find('button.copy-tutorial-fid').length ) {
          $(this).find('.tabledrag-hide').prev().append('<button type="button" class="copy-tutorial-fid" onclick="copyFid(' + id[1] + ", " + stepWeight + ')">Copy Step</button><br/><br/><strong>FID:</strong> ' + id[1]);
        }
      });
    }
    updateStepField();
    Drupal.behaviors.tutorial = {
      attach: function (context, settings) {
        updateStepField();
      }
    }
  });
})(jQuery);

// Copies FID to clipboard
function copyFid(fid, step) {
  var el = document.createElement('textarea');
  var text = document.getElementById('edit-field-tut-body-image-' + step + '-title').value;
  if (text === "") {
    text = 'Add Step Text Here';
  }
  el.value = '[step fid="' + fid + '"]' + text + '[/step]';
  el.setAttribute('readonly', '');
  el.style = {position: 'absolute', left: '-9999px'};
  document.body.appendChild(el);
  el.select();
  document.execCommand('copy');
  document.body.removeChild(el);
}
